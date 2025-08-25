<?php

namespace App\Services\Apis;

use App\Models\Motel;
use App\Models\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Dịch vụ xử lý logic nghiệp vụ liên quan đến nhà trọ.
 */
class MotelService
{
    // Hằng số định nghĩa các tiêu chí sắp xếp
    private const SORT_DEFAULT = 'Sắp xếp mặc định'; // Sắp xếp mặc định
    private const SORT_FEATURED = 'Nổi bật nhất'; // Sắp xếp theo độ nổi bật
    private const SORT_NEWEST = 'Mới nhất'; // Sắp xếp theo thời gian tạo mới nhất
    private const SORT_OLDEST = 'Cũ nhất'; // Sắp xếp theo thời gian tạo cũ nhất

    /**
     * Lấy danh sách nhà trọ nổi bật.
     *
     * @return array Danh sách nhà trọ nổi bật
     */
    public function getFeaturedMotels()
    {
        // Truy vấn lấy 6 nhà trọ nổi bật dựa trên số lượng tiện ích và phòng trống
        $motels = Motel::query()
            ->select('id', 'slug', 'name', 'description', 'address', 'status', 'district_id')
            ->with(['district:id,name']) // Lấy thông tin quận/huyện
            ->withCount('amenities') // Đếm số lượng tiện ích
            ->withCount(['rooms as available_rooms_count' => fn (Builder $query) =>
                $query->where('status', 'Trống') // Đếm số phòng trống
            ])
            ->with(['rooms' => fn ($query) =>
                $query->select('motel_id', 'price')
                    ->whereNotNull('price')
                    ->orderBy('price', 'asc')
                    ->take(1) // Lấy phòng có giá thấp nhất
            ])
            ->orderBy('amenities_count', 'desc') // Sắp xếp theo số lượng tiện ích
            ->orderBy('available_rooms_count', 'desc') // Sắp xếp theo số phòng trống
            ->take(6) // Giới hạn 6 nhà trọ
            ->get();

        // Biến đổi danh sách nhà trọ thành định dạng trả về
        return $this->transformMotelList($motels);
    }

    /**
     * Tìm kiếm nhà trọ theo các tiêu chí lọc.
     *
     * @param Request $request Yêu cầu chứa các tham số tìm kiếm
     * @return array Danh sách nhà trọ phù hợp và thông tin phân trang
     */
    public function searchMotels(Request $request)
    {
        // Xây dựng truy vấn tìm kiếm nhà trọ
        $query = Motel::query()
            ->with(['district:id,name', 'rooms:id,motel_id,price']) // Lấy thông tin quận và phòng
            ->withCount('amenities') // Đếm số lượng tiện ích
            ->withCount(['rooms as available_rooms_count' => fn (Builder $query) =>
                $query->where('status', 'Trống') // Đếm số phòng trống
            ]);

        // Lọc theo từ khóa
        if ($keyword = $request->input('keyword')) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%") // Tìm theo tên nhà trọ
                    ->orWhere('address', 'like', "%{$keyword}%") // Tìm theo địa chỉ
                    ->orWhere('description', 'like', "%{$keyword}%") // Tìm theo mô tả
                    ->orWhereHas('amenities', function (Builder $amenityQuery) use ($keyword) {
                        $amenityQuery->where('name', 'like', "%{$keyword}%"); // Tìm theo tiện ích
                    });
            });
        }

        // Lọc theo quận/huyện
        if ($district = $request->input('district')) {
            $query->whereHas('district', fn (Builder $q) =>
                $q->where('name', $district) // Lọc theo tên quận
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
                        $q->whereBetween('price', [$min, $max]); // Lọc giá phòng trong khoảng
                    } elseif ($min !== null) {
                        $q->where('price', '>=', $min); // Lọc giá phòng tối thiểu
                    } elseif ($max !== null) {
                        $q->where('price', '<=', $max); // Lọc giá phòng tối đa
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
                        $q->whereBetween('area', [$min, $max]); // Lọc diện tích phòng trong khoảng
                    } elseif ($min !== null) {
                        $q->where('area', '>=', $min); // Lọc diện tích phòng tối thiểu
                    } elseif ($max !== null) {
                        $q->where('area', '<=', $max); // Lọc diện tích phòng tối đa
                    }
                }
            });
        }

        // Lọc theo tiện ích
        if ($amenities = $request->input('amenities', [])) {
            $query->whereHas('amenities', fn (Builder $q) =>
                $q->whereIn('amenities.name', $amenities), '>=', count($amenities) // Lọc nhà trọ có đủ các tiện ích
            );
        }

        // Áp dụng sắp xếp
        $sort = $request->input('sort', self::SORT_DEFAULT);
        match ($sort) {
            self::SORT_FEATURED => $query->orderBy('amenities_count', 'desc')
                ->orderBy('available_rooms_count', 'desc'), // Sắp xếp theo tiện ích và phòng trống
            self::SORT_NEWEST => $query->orderBy('created_at', 'desc'), // Sắp xếp theo mới nhất
            self::SORT_OLDEST => $query->orderBy('created_at', 'asc'), // Sắp xếp theo cũ nhất
            default => $query->orderBy('created_at', 'desc'), // Sắp xếp mặc định
        };

        // Phân trang kết quả
        $perPage = $request->input('per_page', 6);
        /** @var LengthAwarePaginator $motels */
        $motels = $query->paginate($perPage);

        // Trả về danh sách nhà trọ và thông tin phân trang
        return [
            'data' => $this->transformMotelList($motels->getCollection()),
            'current_page' => $motels->currentPage(),
            'total_pages' => $motels->lastPage(),
            'total' => $motels->total(),
        ];
    }

    /**
     * Lấy chi tiết một nhà trọ theo slug.
     *
     * @param string $slug Slug của nhà trọ
     * @return array Chi tiết nhà trọ
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Nếu nhà trọ không tồn tại
     */
    public function getMotelDetail($slug)
    {
        // Truy vấn chi tiết nhà trọ theo slug
        $motel = Motel::query()
            ->with([
                'district:id,name', // Lấy thông tin quận/huyện
                'images:id,motel_id,image_url', // Lấy danh sách ảnh nhà trọ
                'rooms' => fn ($query) =>
                    $query->select('id', 'motel_id', 'name', 'price', 'area', 'max_occupants', 'status', 'description')
                        ->where('status', '!=', 'Ẩn') // Lọc phòng không bị ẩn
                        ->with([
                            'amenities:id,name', // Lấy tiện ích của phòng
                            'images:id,room_id,image_url,is_main' // Lấy ảnh của phòng
                        ]),
                'amenities:id,name', // Lấy tiện ích của nhà trọ
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        // Định dạng danh sách phí dịch vụ
        $fees = [
            ['name' => 'Tiền điện', 'price' => $motel->electricity_fee, 'unit' => 'kWh'],
            ['name' => 'Tiền nước', 'price' => $motel->water_fee, 'unit' => 'm³'],
            ['name' => 'Tiền giữ xe', 'price' => $motel->parking_fee, 'unit' => 'người/tháng'],
            ['name' => 'Tiền rác', 'price' => $motel->junk_fee, 'unit' => 'tháng'],
            ['name' => 'Tiền internet', 'price' => $motel->internet_fee, 'unit' => 'tháng'],
            ['name' => 'Phí dịch vụ', 'price' => $motel->service_fee, 'unit' => 'tháng'],
        ];

        // Định dạng danh sách ảnh nhà trọ
        $images = $motel->images->map(fn ($image) => ['src' => $image->image_url])->values()->all();

        // Định dạng danh sách phòng
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

        // Trả về chi tiết nhà trọ
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
     * @param \Illuminate\Support\Collection $motels Bộ sưu tập nhà trọ
     * @return array Danh sách nhà trọ đã được định dạng
     */
    private function transformMotelList($motels): array
    {
        // Biến đổi từng nhà trọ trong danh sách
        return $motels->map(function ($motel) {
            return [
                'id' => $motel->id,
                'slug' => $motel->slug,
                'name' => $motel->name,
                'address' => $motel->address,
                'status' => $motel->status,
                'district_name' => $motel->district->name,
                'main_image' => $motel->main_image->image_url, // Ảnh chính của nhà trọ
                'room_count' => $motel->available_rooms_count, // Số phòng trống
                'min_price' => $motel->rooms->min('price'), // Giá phòng thấp nhất
            ];
        })->toArray();
    }
}
