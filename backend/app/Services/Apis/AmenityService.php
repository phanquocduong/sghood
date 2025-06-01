<?php

namespace App\Services\Apis;

use App\Models\Amenity;

class AmenityService
{
    public function getAmenities()
    {
        $amenities = Amenity::where('type', 'Nhà trọ')
            ->where('status', 'Hoạt động')
            ->select('name')
            ->orderBy('order', 'asc') // Sắp xếp theo cột order nếu có
            ->get()
            ->map(function ($amenity) {
                return [
                    'value' => $amenity->name,
                    'label' => $amenity->name,
                ];
            });

        return $amenities;
    }
}
