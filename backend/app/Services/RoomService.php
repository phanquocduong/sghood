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
    public function fetchRooms(bool $onlyTrashed, string $querySearch, string $status, string $sortOption, string $perPage): array
    {
        try {
            $query = $onlyTrashed ? Room::onlyTrashed() : Room::query();

            $this->applyFilters($query, $querySearch, $status);
            $this->applySorting($query, $sortOption);

            $rooms = $query->paginate($perPage);

            return ['data' => $rooms->load(['motel', 'images', 'amenities'])];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
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

    public function getAllRooms(string $querySearch, string $status, string $sortOption, string $perPage): array
    {
        return $this->fetchRooms(false, $querySearch, $status, $sortOption, $perPage);
    }

    public function getRoom(string $id, bool $withTrashed = false): array
    {
        try {
            $query = Room::query()->with(['motel', 'images', 'amenities']);
            if ($withTrashed) {
                $query->withTrashed();
            }
            $room = $query->findOrFail($id);
            return ['data' => $room];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function create(array $data, ?array $imageFiles = []): array
    {
        DB::beginTransaction();
        try {
            $room = Room::create($data);

            $failedUploads = $this->processRoomImages($room->id, $imageFiles);
            $this->syncAmenities($room, $data['amenities'] ?? []);

            DB::commit();

            $result = ['data' => $room->load(['motel', 'images', 'amenities'])];
            if (!empty($failedUploads)) {
                $result['warnings'] = ['failed_images' => $failedUploads];
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Tạo phòng thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function update(string $id, array $data, ?array $imageFiles = []): array
    {
        DB::beginTransaction();
        try {
            $room = Room::findOrFail($id);

            $room->update($data);

            $failedUploads = $this->processRoomImages($room->id, $imageFiles, true);
            $this->syncAmenities($room, $data['amenities'] ?? []);

            DB::commit();

            $result = ['data' => $room->load(['motel', 'images', 'amenities'])];
            if (!empty($failedUploads)) {
                $result['warnings'] = ['failed_images' => $failedUploads];
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Cập nhật phòng thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function destroy(string $id): array
    {
        DB::beginTransaction();
        try {
            $room = Room::findOrFail($id);
            $room->delete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Xóa phòng thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function getTrashedRooms(string $querySearch, string $status, string $sortOption, int $perPage): array
    {
        return $this->fetchRooms(true, $querySearch, $status, $sortOption, $perPage);
    }

    public function restore(string $id): array
    {
        DB::beginTransaction();
        try {
            $room = Room::onlyTrashed()->findOrFail($id);
            $room->restore();

            DB::commit();
            return ['data' => $room->load(['motel', 'images', 'amenities'])];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Khôi phục phòng thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function forceDelete(string $id): array
    {
        DB::beginTransaction();
        try {
            $room = Room::withTrashed()->findOrFail($id);

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
            Log::error('Xóa vĩnh viễn phòng thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    private function processRoomImages(int $roomId, ?array $imageFiles = [], bool $deleteOld = false): array
    {
        $failedUploads = [];
        if ($deleteOld) {
            $this->deleteOldImages($roomId);
        }

        if (!empty($imageFiles)) {
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
        }
        return $failedUploads;
    }

    private function deleteOldImages(int $roomId): void
    {
        $oldImages = RoomImage::where('room_id', $roomId)->get();
        foreach ($oldImages as $image) {
            $this->deleteRoomImage($image->image_url);
        }
        RoomImage::where('room_id', $roomId)->delete();
    }

    private function syncAmenities(Room $room, array $amenities): void
    {
        if (!empty($amenities)) {
            $room->amenities()->sync($amenities);
        }
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

    private function deleteRoomImage(string $imagePath): void
    {
        try {
            if ($imagePath) {
                $filePath = str_replace('/storage/', '', $imagePath);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }
    }
}
