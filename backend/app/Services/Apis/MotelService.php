<?php

namespace App\Services\Apis;

use App\Models\Motel;
use Illuminate\Http\Request;

class MotelService
{
    public function getFeaturedMotels()
    {
        $motels = Motel::select('id', 'slug', 'name', 'description', 'address', 'status', 'district_id')
            ->with('district')
            ->with('mainImage')
            ->withCount('amenities')
            ->withCount(['rooms as available_rooms_count' => function ($query) {
                $query->where('status', 'Trống');
            }])
            ->with(['rooms' => function ($query) {
                $query->select('motel_id', 'price')
                      ->whereNotNull('price')
                      ->orderBy('price', 'asc');
            }])
            ->orderBy('amenities_count', 'desc')
            ->orderBy('available_rooms_count', 'desc')
            ->take(6)
            ->get()
            ->map(function ($motel) {
                $minPrice = $motel->rooms->min('price') ?? 0;

                return [
                    'id' => $motel->id,
                    'slug' => $motel->slug,
                    'name' => $motel->name,
                    'description' => $motel->description,
                    'address' => $motel->address,
                    'status' => $motel->status,
                    'district_name' => $motel->district->name,
                    'image' => $motel->mainImage->image_url,
                    'amenity_count' => $motel->amenities_count,
                    'room_count' => $motel->available_rooms_count,
                    'price' => $minPrice,
                ];
            });

        return $motels;
    }

    public function searchMotels(Request $request)
    {
        $query = Motel::query()->with(['district', 'rooms', 'mainImage'])
            ->withCount('amenities')
            ->withCount(['rooms as available_rooms_count' => function ($query) {
                $query->where('status', 'Trống');
            }]);

        // Lọc theo từ khóa
        if ($keyword = $request->input('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('address', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Lọc theo khu vực
        if ($area = $request->input('area')) {
            $query->whereHas('district', function ($q) use ($area) {
                $q->where('name', $area);
            });
        }

        // Lọc theo khoảng giá
        if ($priceRange = $request->input('priceRange')) {
            $query->whereHas('rooms', function ($q) use ($priceRange) {
                if ($priceRange == 'under_1m') {
                    $q->where('price', '<', 1000000);
                } elseif ($priceRange == '1m_2m') {
                    $q->whereBetween('price', [1000000, 2000000]);
                } elseif ($priceRange == '2m_3m') {
                    $q->whereBetween('price', [2000000, 3000000]);
                } elseif ($priceRange == '3m_5m') {
                    $q->whereBetween('price', [3000000, 5000000]);
                } elseif ($priceRange == 'over_5m') {
                    $q->where('price', '>', 5000000);
                }
            });
        }

        // Lọc theo diện tích
        if ($areaRange = $request->input('areaRange')) {
            $query->whereHas('rooms', function ($q) use ($areaRange) {
                if ($areaRange == 'under_20') {
                    $q->where('area', '<', 20);
                } elseif ($areaRange == '20_30') {
                    $q->whereBetween('area', [20, 30]);
                } elseif ($areaRange == '30_50') {
                    $q->whereBetween('area', [30, 50]);
                } elseif ($areaRange == 'over_50') {
                    $q->where('area', '>', 50);
                }
            });
        }

        // Lọc theo tiện ích
        $amenities = $request->input('amenities', []);
        if (is_string($amenities)) {
            $amenities = array_filter(explode('+', $amenities));
        }
        if (!empty($amenities)) {
            $query->whereHas('amenities', function ($q) use ($amenities) {
                $q->whereIn('amenities.name', $amenities);
            }, '=', count($amenities));
        }

        // Sắp xếp
        $sort = $request->input('sort', 'Sắp xếp mặc định');
        if ($sort == 'Nổi bật nhất') {
            $query->orderBy('amenities_count', 'desc')
                  ->orderBy('available_rooms_count', 'desc');
        } elseif ($sort == 'Mới nhất') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort == 'Cũ nhất') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Phân trang
        $perPage = $request->input('per_page', 6);
        $page = $request->input('page', 1);
        $motels = $query->paginate($perPage, ['*'], 'page', $page);

        // Transform kết quả
        $motels->getCollection()->transform(function ($motel) {
            $minPrice = $motel->rooms->min('price') ?? 0;
            return [
                'id' => $motel->id,
                'slug' => $motel->slug,
                'name' => $motel->name,
                'description' => $motel->description,
                'address' => $motel->address,
                'status' => $motel->status,
                'district_name' => $motel->district->name,
                'image' => $motel->mainImage->image_url,
                'room_count' => $motel->available_rooms_count,
                'price' => $minPrice,
            ];
        });

        return [
            'data' => $motels->items(),
            'current_page' => $motels->currentPage(),
            'total_pages' => $motels->lastPage(),
            'total' => $motels->total(),
        ];
    }

    public function getMotelDetail($slug)
    {
        $motel = Motel::with([
            'district',
            'images',
            'rooms' => function ($query) {
                $query->select('id', 'motel_id', 'name', 'price', 'area', 'status')
                    ->where('status', 'Trống')
                    ->with(['amenities' => function ($query) {
                        $query->select('amenities.id', 'amenities.name');
                    }])
                    ->with('mainImage');
            },
            'amenities' => function ($query) {
                $query->select('amenities.id', 'amenities.name');
            },
        ])->where('slug', $slug)->firstOrFail();

        // Lấy fees từ DB với đơn vị
        $fees = [
            ['name' => 'Tiền điện', 'price' => $motel->electricity_fee, 'unit' => 'kWh'],
            ['name' => 'Tiền nước', 'price' => $motel->water_fee, 'unit' => 'm³'],
            ['name' => 'Tiền giữ xe', 'price' => $motel->parking_fee, 'unit' => 'tháng'],
            ['name' => 'Tiền rác', 'price' => $motel->junk_fee, 'unit' => 'tháng'],
            ['name' => 'Tiền internet', 'price' => $motel->internet_fee, 'unit' => 'tháng'],
            ['name' => 'Phí dịch vụ', 'price' => $motel->service_fee, 'unit' => 'tháng'],
        ];

        // Lấy danh sách ảnh từ motel_images
        $images = $motel->images->map(function ($image) {
            return ['src' => $image->image_url];
        })->values()->all();

        return [
            'id' => $motel->id,
            'name' => $motel->name,
            'address' => $motel->address,
            'district_name' => $motel->district->name,
            'description' => $motel->description,
            'images' => $images,
            'amenities' => $motel->amenities->pluck('name')->toArray(),
            'rooms' => $motel->rooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'price' => $room->price,
                    'area' => $room->area,
                    'status' => $room->status,
                    'amenities' => $room->amenities->pluck('name')->toArray(),
                    'image' => $room->mainImage->image_url
                ];
            })->values(),
            'fees' => $fees,
            'map_url' => $motel->map_embed_url,
        ];
    }
}
