<?php

namespace App\Services;

use App\Models\Rooms;

class RoomService
{
    public function createRoom(array $data)
    {
        if ($data['price'] < 0) {
            throw new \Exception('Giá phòng không được âm.');
        }

        if ($data['area'] < 0) {
            throw new \Exception('Diện tích phòng không được âm.');
        }

        return Rooms::create($data);
    }

    public function updateRoom(Rooms $room, array $data)
    {
        $data = array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });
        if (isset($data['price']) && $data['price'] < 0) {
            throw new \Exception('Giá phòng không được âm.');
        }

        if (isset($data['area']) && $data['area'] < 0) {
            throw new \Exception('Diện tích phòng không được âm.');
        }

        $room->update($data);
        $room->refresh();
        return $room;
    }

    public function deleteRoom(Rooms $room)
    {
        $room->delete();
        return $room;
    }
}
