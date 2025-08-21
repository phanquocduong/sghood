<?php

namespace App\Services\Apis;

use App\Models\Motel;
use App\Models\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MotelService
{
    // Hằng số cho các tiêu chí sắp xếp
    private const SORT_DEFAULT = 'Sắp xếp mặc định';
    private const SORT_FEATURED = 'Nổi bật nhất';
    private const SORT_NEWEST = 'Mới nhất';
    private const SORT_OLDEST = 'Cũ nhất';

    /**
     * Lấy danh sách nhà trọ nổi bật.
     *
     * @return array
     */
    public function getFeaturedMotels()
    {
        $motels = Motel::query()
            ->select('id', 'slug', 'name', 'description', 'address', 'status', 'district_id')
            ->with(['district:id,name'])
            ->withCount('amenities')
            ->withCount(['rooms as available_rooms_count' => fn (Builder $query) =>
                $query->where('status', 'Trống')
            ])
            ->with(['rooms' => fn ($query) =>
                $query->select('motel_id', 'price')
                    ->whereNotNull('price')
                    ->orderBy('price', 'asc')
                    ->take(1)
            ])
            ->orderBy('amenities_count', 'desc')
            ->orderBy('available_rooms_count', 'desc')
            ->take(6)
            ->get();

        return $this->transformMotelList($motels);
    }

    /**
     * Tìm kiếm nhà trọ theo tiêu chí.
     *
     * @param Request $request
     * @return array
     */
    public function searchMotels(Request $request)
    {
        $query = Motel::query()
            ->with(['district:id,name', 'rooms:id,motel_id,price'])
            ->withCount('amenities')
            ->withCount(['rooms as available_rooms_count' => fn (Builder $query) =>
                $query->where('status', 'Trống')
            ]);

        // Lọc theo từ khóa
        if ($keyword = $request->input('keyword')) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhereHas('amenities', function (Builder $amenityQuery) use ($keyword) {
                        $amenityQuery->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        // Lọc theo khu vực
        if ($district = $request->input('district')) {
            $query->whereHas('district', fn (Builder $q) =>
                $q->where('name', $district)
            );
        }

        // Lọc theo khoảng giá
        if ($priceRange = $request->input('priceRange')) {
            $priceRangesJson = Config::getValue('price_filter_options', '[]');
            $priceRanges = json_decode($priceRangesJson, true) ?: [];
            $query->whereHas('rooms', function (Builder $q) use ($priceRange, $priceRanges) {
                $range = collect($priceRanges)->firstWhere('key', $priceRange);
                if ($range) {
                    // Chuyển đổi min/max từ chuỗi sang số
                    $min = isset($range['min']) ? (float)$range['min'] : null;
                    $max = isset($range['max']) ? (float)$range['max'] : null;
                    if ($min !== null && $max !== null) {
                        $q->whereBetween('price', [$min, $max]);
                    } elseif ($min !== null) {
                        $q->where('price', '>=', $min);
                    } elseif ($max !== null) {
                        $q->where('price', '<=', $max);
                    }
                }
            });
        }

        // Lọc theo diện tích
        if ($areaRange = $request->input('areaRange')) {
            $areaRangesJson = Config::getValue('area_filter_options', '[]');
            $areaRanges = json_decode($areaRangesJson, true) ?: [];
            $query->whereHas('rooms', function (Builder $q) use ($areaRange, $areaRanges) {
                $range = collect($areaRanges)->firstWhere('key', $areaRange);
                if ($range) {
                    // Chuyển đổi min/max từ chuỗi sang số
                    $min = isset($range['min']) ? (float)$range['min'] : null;
                    $max = isset($range['max']) ? (float)$range['max'] : null;
                    if ($min !== null && $max !== null) {
                        $q->whereBetween('area', [$min, $max]);
                    } elseif ($min !== null) {
                        $q->where('area', '>=', $min);
                    } elseif ($max !== null) {
                        $q->where('area', '<=', $max);
                    }
                }
            });
        }

        // Lọc theo tiện ích
        if ($amenities = $request->input('amenities', [])) {
            $query->whereHas('amenities', fn (Builder $q) =>
                $q->whereIn('amenities.name', $amenities), '>=', count($amenities)
            );
        }

        // Sắp xếp
        $sort = $request->input('sort', self::SORT_DEFAULT);
        match ($sort) {
            self::SORT_FEATURED => $query->orderBy('amenities_count', 'desc')
                ->orderBy('available_rooms_count', 'desc'),
            self::SORT_NEWEST => $query->orderBy('created_at', 'desc'),
            self::SORT_OLDEST => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        // Phân trang
        $perPage = $request->input('per_page', 6);
        /** @var LengthAwarePaginator $motels */
        $motels = $query->paginate($perPage);

        return [
            'data' => $this->transformMotelList($motels->getCollection()),
            'current_page' => $motels->currentPage(),
            'total_pages' => $motels->lastPage(),
            'total' => $motels->total(),
        ];
    }

    /**
     * Lấy chi tiết nhà trọ theo slug.
     *
     * @param string $slug
     * @return array
     */
    public function getMotelDetail($slug)
    {
        $motel = Motel::query()
            ->with([
                'district:id,name',
                'images:id,motel_id,image_url',
                'rooms' => fn ($query) =>
                    $query->select('id', 'motel_id', 'name', 'price', 'area', 'max_occupants', 'status', 'description')
                        ->where('status', '!=', 'Ẩn')
                        ->with([
                            'amenities:id,name',
                            'images:id,room_id,image_url,is_main'
                        ]),
                'amenities:id,name',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        // Lấy fees từ DB với đơn vị
        $fees = [
            ['name' => 'Tiền điện', 'price' => $motel->electricity_fee, 'unit' => 'kWh'],
            ['name' => 'Tiền nước', 'price' => $motel->water_fee, 'unit' => 'm³'],
            ['name' => 'Tiền giữ xe', 'price' => $motel->parking_fee, 'unit' => 'người/tháng'],
            ['name' => 'Tiền rác', 'price' => $motel->junk_fee, 'unit' => 'tháng'],
            ['name' => 'Tiền internet', 'price' => $motel->internet_fee, 'unit' => 'tháng'],
            ['name' => 'Phí dịch vụ', 'price' => $motel->service_fee, 'unit' => 'tháng'],
        ];

        // Lấy danh sách ảnh từ motel_images
        $images = $motel->images->map(fn ($image) => ['src' => $image->image_url])->values()->all();

        $rooms = $motel->rooms->map(fn ($room) => [
            'id' => $room->id,
            'name' => $room->name,
            'price' => $room->price,
            'area' => $room->area,
            'max_occupants' => $room->max_occupants,
            'description' => $room->description,
            'status' => $room->status,
            'amenities' => $room->amenities->pluck('name')->toArray(),
            'main_image' => $room->main_image->image_url,
            'images' => $room->images->map(fn ($image) => ['src' => $image->image_url])->values()->all()
        ])->values()->all();

        return [
            'id' => $motel->id,
            'name' => $motel->name,
            'address' => $motel->address,
            'district_name' => $motel->district->name,
            'description' => $motel->description,
            'images' => $images,
            'amenities' => $motel->amenities->pluck('name')->toArray(),
            'rooms' => $rooms,
            'fees' => $fees,
            'map_url' => $motel->map_embed_url,
        ];
    }

    /**
     * Biến đổi danh sách nhà trọ thành định dạng trả về.
     *
     * @param \Illuminate\Support\Collection $motels
     * @return array
     */
    private function transformMotelList($motels): array
    {
        return $motels->map(function ($motel) {
            return [
                'id' => $motel->id,
                'slug' => $motel->slug,
                'name' => $motel->name,
                'address' => $motel->address,
                'status' => $motel->status,
                'district_name' => $motel->district->name,
                'main_image' => $motel->main_image->image_url,
                'room_count' => $motel->available_rooms_count,
                'min_price' => $motel->rooms->min('price'),
            ];
        })->toArray();
    }
}

?>
