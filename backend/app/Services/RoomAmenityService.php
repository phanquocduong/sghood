<?php

namespace App\Services;

use App\Models\Rooms;
use App\Models\Amenity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomAmenityService
{
    public function getAmenitiesForRoom(string $roomId)
    {
        $room = Rooms::findOrFail($roomId);

        $amenities = $room->amenities()->get()->map(function ($amenity) use ($roomId) {
            $amenity->pivot = (object) [
                'room_id' => (int) $roomId,
                'amenity_id' => $amenity->id,
            ];

            return $amenity;
        });

        return $amenities;
    }

    public function assignAmenityToRoom(string $roomId, string $amenityId)
    {
        DB::beginTransaction();
        try {
            $room = Rooms::findOrFail($roomId);
            $amenity = Amenity::findOrFail($amenityId);

            // Thêm tiện nghi vào phòng và gán giá trị created_at, updated_at
            $room->amenities()->syncWithoutDetaching([
                $amenityId => [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);

            DB::commit();

            // Trả về thông tin của tiện nghi đã thêm
            return [
                'room_id' => (int) $roomId,
                'amenity' => $amenity,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gán tiện nghi cho phòng thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    public function removeAmenityFromRoom(string $roomId, string $amenityId)
    {
        DB::beginTransaction();
        try {
            $room = Rooms::findOrFail($roomId);
            $amenity = Amenity::findOrFail($amenityId);

            $exists = $room->amenities()->where('amenity_id', $amenityId)->exists();

            if (!$exists) {
                return false;
            }
            $room->amenities()->detach($amenityId);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xóa tiện nghi khỏi phòng thất bại: ' . $e->getMessage());
            throw $e;
        }
    }
}
