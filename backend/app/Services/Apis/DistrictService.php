<?php

namespace App\Services\Apis;

use App\Models\District;

class DistrictService
{
    public function getFeaturedDistricts()
    {
        $districts = District::select('id', 'name', 'image')
            ->withCount('motels') // Đếm số lượng nhà trọ
            ->orderBy('motels_count', 'desc') // Sắp xếp theo motels_count giảm dần
            ->take(5) // Chỉ lấy 5 quận có số lượng lớn nhất
            ->get()
            ->map(function ($district) {
                return [
                    'id' => $district->id,
                    'name' => $district->name,
                    'image' => $district->image,
                    'motel_count' => $district->motels_count
                ];
            });

        return $districts;
    }
}
