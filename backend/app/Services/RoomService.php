<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class RoomService
{
    public function fetchRooms(bool $onlyTrashed, string $motelId, string $querySearch, string $status, string $sortOption, string $perPage): array
    {
        try {
            $query = $onlyTrashed ? Room::onlyTrashed() : Room::query();
            $query->with(['motel', 'images', 'amenities']);
            $query->where('motel_id', $motelId);

            $this->applyFilters($query, $querySearch, $status);
            $this->applySorting($query, $sortOption);

            $rooms = $query->paginate($perPage);

            return ['data' => $rooms];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách phòng trọ', 'status' => 500];
        }
    }

    private function applyFilters($query, string $querySearch, string $status): void
    {
        if ($querySearch !== '') {
            $query->where('name', 'LIKE', '%' . $querySearch . '%');
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }
    }

    private function applySorting($query, string $sortOption): void
    {
        $sort = $this->handleSortOption($sortOption);
        $query->orderBy($sort['field'], $sort['order']);
    }

    public function handleSortOption(string $sortOption): array
    {
        switch ($sortOption) {
            case 'name_asc':
                return ['field' => 'name', 'order' => 'asc'];
            case 'name_desc':
                return ['field' => 'name', 'order' => 'desc'];
            case 'created_at_asc':
                return ['field' => 'created_at', 'order' => 'asc'];
            case 'created_at_desc':
                return ['field' => 'created_at', 'order' => 'desc'];
            default:
                return ['field' => 'created_at', 'order' => 'desc'];
        }
    }

    public function getRoomsByMotelId(string $motelId, string $querySearch, string $status, string $sortOption, string $perPage): array
    {
        return $this->fetchRooms(false, $motelId, $querySearch, $status, $sortOption, $perPage);
    }

    public function getTrashedRoomsByMotelId(string $motelId, string $querySearch, string $status, string $sortOption, string $perPage): array
    {
        return $this->fetchRooms(true, $motelId, $querySearch, $status, $sortOption, $perPage);
    }

    public function getRoom(string $id, bool $onlyTrashed = false): array
    {
        try {
            $query = $onlyTrashed ? Room::onlyTrashed() : Room::query();
            $query->with(['motel', 'images', 'amenities']);

            $room = $query->find($id);
            if (!$room) {
                return ['error' => 'Phòng trọ không tìm thấy', 'status' => 404];
            }
            return ['data' => $room];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi tạo phòng trọ', 'status' => 500];
        }
    }

    public function createRoom(array $data, array $imageFiles): array
    {
        DB::beginTransaction();
        try {
            $room = Room::create($data);

            $failedUploads = $this->processRoomImages($room->id, $imageFiles);
            $room->amenities()->sync($data['amenities']);

            DB::commit();

            $result = ['data' => $room->load(['motel', 'images', 'amenities'])];
            if (!empty($failedUploads)) {
                $result['warnings'] = ['failed_images' => $failedUploads];
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi tạo phòng trọ', 'status' => 500];
        }
    }

    private function processRoomImages(int $roomId, array $imageFiles): array
    {
        $failedUploads = [];
        foreach ($imageFiles as $file) {
            $imagePath = $this->uploadRoomImage($file);
            if ($imagePath) {
                RoomImage::create([
                    'room_id' => $roomId,
                    'image_url' => $imagePath
                ]);
            } else {
                $failedUploads[] = $file->getClientOriginalName();
            }
        }
        return $failedUploads;
    }

    private function uploadRoomImage(UploadedFile $imageFile): string|false
    {
        try {
            $manager = new ImageManager(new Driver());
            $filename = 'images/rooms/room-' . time() . '-' . uniqid() . '.webp';

            $image = $manager->read($imageFile)->toWebp(quality: 85)->toString();

            Storage::disk('public')->put($filename, $image);

            return Storage::url($filename);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public function updateRoom(string $id, array $data, array $imageFiles, ?string $mainImageId = null): array
{
    DB::beginTransaction();
    try {
        $room = Room::findOrFail($id);
        if (!$room) {
            return ['error' => 'Phòng trọ không tìm thấy', 'status' => 404];
        }

        $room->update($data);

        // Xử lý xóa hình ảnh từ select box
        if (isset($data['delete_images']) && is_array($data['delete_images'])) {
            foreach ($data['delete_images'] as $imageId) {
                $image = RoomImage::where('id', $imageId)->where('room_id', $room->id)->first();
                if ($image) {
                    $this->deleteRoomImage($image->image_url);
                    $image->delete();
                }
            }
        }

        // Xử lý thêm hình ảnh mới
        if (!empty($imageFiles)) {
            $failedUploads = $this->processRoomImages($room->id, $imageFiles);
        }

        // Xử lý cập nhật ảnh chính (is_main)
        if ($mainImageId) {
            // Đặt tất cả hình ảnh của phòng này thành is_main = 0
            RoomImage::where('room_id', $room->id)->update(['is_main' => 0]);

            // Đặt hình ảnh được chọn thành is_main = 1
            $mainImage = RoomImage::where('id', $mainImageId)
                ->where('room_id', $room->id)
                ->first();

            if ($mainImage) {
                $mainImage->update(['is_main' => 1]);
            }
        }

        if (isset($data['amenities'])) {
            $room->amenities()->sync($data['amenities']);
        }

        DB::commit();

        $result = ['data' => $room->load(['motel', 'images', 'amenities'])];
        if (!empty($failedUploads ?? [])) {
            $result['warnings'] = ['failed_images' => $failedUploads];
        }
        return $result;
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error($e->getMessage());
        return ['error' => 'Đã xảy ra lỗi khi cập nhật phòng trọ: ' . $e->getMessage(), 'status' => 500];
    }
}

    public function deleteRoom(string $id): array
    {
        DB::beginTransaction();
        try {
            $room = Room::find($id);
            if (!$room) {
                return ['error' => 'Phòng trọ không tìm thấy', 'status' => 404];
            }
            $room->delete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi xóa phòng trọ', 'status' => 500];
        }
    }

    public function restoreRoom(string $id): array
    {
        DB::beginTransaction();
        try {
            $room = Room::onlyTrashed()->find($id);
            if (!$room) {
                return ['error' => 'Phòng trọ không tìm thấy trong thùng rác', 'status' => 404];
            }
            $room->restore();

            DB::commit();
            return ['data' => $room->load(['motel', 'images', 'amenities'])];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi khôi phục phòng trọ', 'status' => 500];
        }
    }

    public function permanentlyDeleteRoom(string $id): array
    {
        DB::beginTransaction();
        try {
            $room = Room::withTrashed()->find($id);
            if (!$room) {
                return ['error' => 'Phòng trọ không tìm thấy trong thùng rác', 'status' => 404];
            }

            foreach ($room->images as $image) {
                $this->deleteRoomImage($image->image_url);
            }
            $room->images()->forceDelete();

            $room->amenities()->detach();
            $room->forceDelete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi xóa vĩnh viễn phòng trọ', 'status' => 500];
        }
    }

    public function deleteSingleRoomImage(int $imageId, int $roomId): array
{
    DB::beginTransaction();
    try {
        $image = RoomImage::where('id', $imageId)->where('room_id', $roomId)->first();
        if (!$image) {
            return ['error' => 'Hình ảnh không tìm thấy', 'status' => 404];
        }

        $this->deleteRoomImage($image->image_url);
        $image->delete();

        DB::commit();
        return ['success' => true, 'message' => 'Hình ảnh đã được xóa thành công'];
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Error deleting single room image: ' . $e->getMessage());
        return ['error' => 'Đã xảy ra lỗi khi xóa hình ảnh', 'status' => 500];
    }
}

private function deleteRoomImage(string $imagePath): bool
{
    try {
        Log::info('Attempting to delete image: ' . $imagePath);
        $filePath = str_replace('/storage/', '', $imagePath);

        Log::info('Converted file path: ' . $filePath);

        if (Storage::disk('public')->exists($filePath)) {
            Log::info('File exists, deleting: ' . $filePath);
            Storage::disk('public')->delete($filePath);
            return true;
        } else {
            Log::warning('File does not exist: ' . $filePath);
            return false;
        }
    } catch (\Throwable $e) {
        Log::error('Error deleting image: ' . $e->getMessage());
        return false;
    }
}
}
