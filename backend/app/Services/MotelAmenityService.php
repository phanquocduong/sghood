<?php

namespace App\Services;

use App\Models\Motel;
use App\Models\Amenity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MotelAmenityService
{
    public function getAmenitiesForMotel(string $motelId)
    {
        $motel = Motel::findOrFail($motelId);

        $amenities = $motel->amenities()->get()->map(function ($amenity) use ($motelId) {
            $amenity->pivot = (object) [
                'motel_id' => (int) $motelId,
                'amenity_id' => $amenity->id,
            ];

            return $amenity;
        });

        return $amenities;
    }

    public function assignAmenityToMotel(string $motelId, string $amenityId)
    {
        DB::beginTransaction();
        try {
            $motel = Motel::findOrFail($motelId);
            $amenity = Amenity::findOrFail($amenityId);

            // Thêm tiện nghi vào motel và gán giá trị created_at, updated_at
            $motel->amenities()->syncWithoutDetaching([
                $amenityId => [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);

            DB::commit();

            // Trả về thông tin của tiện nghi đã thêm
            return [
                'motel_id' => (int) $motelId,
                'amenity' => $amenity,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gán tiện nghi cho motel thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    public function removeAmenityFromMotel(string $motelId, string $amenityId)
    {
        DB::beginTransaction();
        try {
            $motel = Motel::findOrFail($motelId);
            $amenity = Amenity::findOrFail($amenityId);

            $exists = $motel->amenities()->where('amenity_id', $amenityId)->exists();

            if (!$exists) {
                return false;
            }
            $motel->amenities()->detach($amenityId);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xóa tiện nghi khỏi motel thất bại: ' . $e->getMessage());
            throw $e;
        }
    }
}
