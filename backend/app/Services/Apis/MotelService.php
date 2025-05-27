<?php

namespace App\Services\Apis;

use App\Models\Motel;

class MotelService
{
    public function getFeaturedMotels()
    {
        $motels = Motel::select('id', 'name', 'description', 'address', 'status', 'district_id')
            ->with('district') // Lấy thông tin quận huyện
            ->with('mainImage') // Lấy ảnh chính
            ->withCount('amenities') // Đếm số lượng tiện ích
            ->withCount(['rooms as available_rooms_count' => function ($query) {
                $query->where('status', 'Trống'); // Chỉ đếm phòng có status = 'Trống'
            }]) // Đếm số phòng trống
            ->with(['rooms' => function ($query) {
                $query->select('motel_id', 'price')
                      ->whereNotNull('price')
                      ->orderBy('price', 'asc'); // Lấy giá nhỏ nhất
            }]) // Lấy tất cả phòng để tính giá nhỏ nhất
            ->orderBy('amenities_count', 'desc') // Sắp xếp theo số lượng tiện ích giảm dần
            ->orderBy('available_rooms_count', 'desc') // Sắp xếp theo số phòng trống giảm dần
            ->take(6) // Lấy 6 nhà trọ nổi bật nhất
            ->get()
            ->map(function ($motel) {
                // Lấy giá nhỏ nhất từ danh sách phòng
                $minPrice = $motel->rooms->min('price') ?? 0;

                return [
                    'id' => $motel->id,
                    'name' => $motel->name,
                    'description' => $motel->description,
                    'address' => $motel->address,
                    'status' => $motel->status,
                    'district_name' => $motel->district ? $motel->district->name : 'Không xác định',
                    'image' => $motel->mainImage ? $motel->mainImage->image_url : '/storage/images/default-motel.jpg',
                    'amenity_count' => $motel->amenities_count,
                    'room_count' => $motel->available_rooms_count, // Số phòng trống
                    'price' => $minPrice, // Giá nhỏ nhất
                ];
            });

        return $motels;
    }
}
