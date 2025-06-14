<?php

namespace App\Services\Apis;

use App\Models\Motel;
use App\Models\Room;

class RoomService
{
    /**
     * Lấy chi tiết phòng trọ và các phòng trống khác trong nhà trọ
     *
     * @param string $slug
     * @param int $roomId
     * @return array
     */
    public function getRoomDetail($slug, $roomId)
    {
        // Lấy thông tin nhà trọ
        $motel = Motel::with([
            'district',
            'rooms' => function ($query) {
                $query->select('id', 'motel_id', 'name', 'price', 'area', 'status', 'description')
                    ->where('status', 'Trống')
                    ->with(['amenities' => function ($query) {
                        $query->select('amenities.id', 'amenities.name');
                    }])
                    ->with('mainImage');
            }
        ])->where('slug', $slug)->firstOrFail();

        // Lấy thông tin phòng cụ thể
        $room = Room::with([
            'amenities' => function ($query) {
                $query->select('amenities.id', 'amenities.name');
            },
            'images' => function ($query) {
                $query->select('room_id', 'image_url');
            }
        ])->where('id', $roomId)->where('motel_id', $motel->id)->firstOrFail();

        // Lấy danh sách phòng trống khác (loại trừ phòng đang xem)
        $otherRooms = $motel->rooms->filter(function ($item) use ($roomId) {
            return $item->id != $roomId;
        })->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'area' => $item->area,
                'status' => $item->status,
                'description' => $item->description,
                'main_image' => $item->mainImage ? $item->mainImage->image_url : null,
                'amenities' => $item->amenities->pluck('name')->toArray()
            ];
        })->values();

        // Trả về dữ liệu phòng và nhà trọ
        return [
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
                'price' => $room->price,
                'area' => $room->area,
                'status' => $room->status,
                'description' => $room->description,
                'images' => $room->images->map(function ($image) {
                    return $image->image_url;
                })->values()->all(),
                'amenities' => $room->amenities->pluck('name')->toArray(),
                'motel_name' => $motel->name
            ],
            'other_rooms' => $otherRooms
        ];
    }
}
