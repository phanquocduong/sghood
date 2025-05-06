<?php

namespace App\Services;

use App\Models\RoomImages;
use App\Models\Rooms;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RoomImageService
{
    public function getAllImages($roomId, $perPage = 10)
    {
        $query = RoomImages::query()->where('room_id', $roomId);

        $images = $query->paginate($perPage);
        return $images;
    }

    public function create($roomId, $validatedRequest)
    {
        DB::beginTransaction();
        try {
            $room = Rooms::findOrFail($roomId);

            $validatedRequest['room_id'] = $roomId;
            $image = RoomImages::create($validatedRequest);

            DB::commit();
            return $image;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Thêm hình ảnh thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($roomId, $id, $validatedRequest)
    {
        DB::beginTransaction();
        try {
            $image = RoomImages::where('room_id', $roomId)->findOrFail($id);

            if ($image->image_url) {
                $path = str_replace(Storage::url(''), '', $image->image_url);
                Storage::disk('public')->delete($path);
            }

            $image->update($validatedRequest);

            DB::commit();
            return $image;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cập nhật hình ảnh thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    public function destroy($roomId, $id)
    {
        DB::beginTransaction();
        try {
            $image = RoomImages::where('room_id', $roomId)->findOrFail($id);

            if ($image->image_url) {
                $path = str_replace(Storage::url(''), '', $image->image_url);
                Storage::disk('public')->delete($path);
            }

            $image->delete();

            DB::commit();
            return $image;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xóa hình ảnh thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    public function handleImage($image)
    {
        try {
            $imageName = 'room-' . time() . '-' . rand(1000, 9999) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('rooms', $imageName, 'public');
            return Storage::url($imagePath);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải hình ảnh: ' . $e->getMessage());
            throw $e;
        }
    }
}
