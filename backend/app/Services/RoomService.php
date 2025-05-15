<?php
namespace App\Services;

use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RoomService {
    public function fetchRooms(bool $onlyTrashed, string $querySearch, string $status, string $sortOption, string $perPage): array {
        try {
            $query = $onlyTrashed ? Room::onlyTrashed() : Room::query();
            $query->with(['motel', 'images', 'amenities']);

            $this->applyFilters($query, $querySearch, $status);
            $this->applySorting($query, $sortOption);

            $rooms = $query->paginate($perPage);

            return ['data' => $rooms];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách phòng trọ', 'status' => 500];
        }
    }

    private function applyFilters($query, string $querySearch, string $status): void {
        if ($querySearch !== '') {
            $query->where('name', 'LIKE', '%' . $querySearch . '%');
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }
    }

    private function applySorting($query, string $sortOption): void {
        $sort = $this->handleSortOption($sortOption);
        $query->orderBy($sort['field'], $sort['order']);
    }

    public function handleSortOption(string $sortOption): array {
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

    public function getAllRooms(string $querySearch, string $status, string $sortOption, string $perPage): array {
        return $this->fetchRooms(false, $querySearch, $status, $sortOption, $perPage);
    }

    public function getTrashedRooms(string $querySearch, string $status, string $sortOption, int $perPage): array {
        return $this->fetchRooms(true, $querySearch, $status, $sortOption, $perPage);
    }

    public function getRoom(int $id, bool $onlyTrashed = false): array {
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

    public function createRoom(array $data, array $imageFiles): array {
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

    private function processRoomImages(int $roomId, array $imageFiles): array {
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

    private function uploadRoomImage(UploadedFile $imageFile): string|false {
        try {
            $imageName = 'room-' . time() . '-' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = $imageFile->storeAs('images/rooms', $imageName, 'public');
            return Storage::url($imagePath);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public function updateMotel(int $id, array $data, array $imageFiles): array {
        DB::beginTransaction();
        try {
            $room = Room::findOrFail($id);
            if (!$room) {
                return ['error' => 'Phòng trọ không tìm thấy', 'status' => 404];
            }

            $room->update($data);

            if (!empty($imageFiles)) {
                foreach ($room->images()->get() as $image) {
                    $this->deleteRoomImage($image->image_url);
                }
                $room->images()->delete();

                $failedUploads = $this->processRoomImages($room->id, $imageFiles);
            }

            if (isset($data['amenities'])) {
                $room->amenities()->sync($data['amenities']);
            }

            DB::commit();

            $result = ['data' => $room->load(['motel', 'images', 'amenities'])];
            if (!empty($failedUploads)) {
                $result['warnings'] = ['failed_images' => $failedUploads];
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi cập nhật phòng trọ', 'status' => 500];
        }
    }

    public function deleteRoom(int $id): array {
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

    public function restoreRoom(string $id): array {
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

    public function forceDeleteRoom(int $id): array {
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

    private function deleteRoomImage(string $imagePath): void {
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
