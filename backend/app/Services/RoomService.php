<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RoomService
{
    public function fetchRooms(bool $onlyTrashed, string $querySearch, string $status, string $sortOption, string $perPage)
    {
        try {
            $query = $onlyTrashed ? Room::onlyTrashed() : Room::query();

            if ($querySearch !== '') {
                $query->where('name', 'LIKE', '%' . $querySearch . '%');
            }

            if (!empty($status)) {
                $query->where('status', $status);
            }

            $sort = $this->handleSortOption($sortOption);
            $query->orderBy($sort['field'], $sort['order']);

            $rooms = $query->paginate($perPage);

            return ['data' => $rooms->load(['motel', 'images', 'amenities'])];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function handleSortOption(string $sortOption)
    {
        switch ($sortOption) {
            case 'name_asc':
                $sortField = 'name';
                $sortOrder = 'asc';
                break;
            case 'name_desc':
                $sortField = 'name';
                $sortOrder = 'desc';
                break;
            case 'created_at_asc':
                $sortField = 'created_at';
                $sortOrder = 'asc';
                break;
            case 'created_at_desc':
                $sortField = 'created_at';
                $sortOrder = 'desc';
                break;
            default:
                $sortField = 'created_at';
                $sortOrder = 'desc';
        }
        return [
            'field' => $sortField,
            'order' => $sortOrder
        ];
    }

    public function getAllRooms(string $querySearch, string $status, string $sortOption, string $perPage)
    {
        return $this->fetchRooms(false, $querySearch, $status, $sortOption, $perPage);
    }

    public function getRoom(string $id, bool $withTrashed = false)
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

    public function create(array $data, ?array $imageFiles = [])
    {
        DB::beginTransaction();
        try {
            $room = Room::create($data);

            $failedUploads = [];
            if (!empty($imageFiles)) {
                foreach ($imageFiles as $file) {
                    $imagePath = $this->uploadRoomImage($file);
                    if ($imagePath) {
                        RoomImage::create([
                            'room_id' => $room->id,
                            'image_url' => $imagePath
                        ]);
                    } else {
                        $failedUploads[] = $file->getClientOriginalName();
                    }
                }
            }

            // Xử lý amenities
            if (isset($data['amenities']) && !empty($data['amenities'])) {
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
            Log::error('Tạo phòng thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function update(string $id, array $data, ?array $imageFiles = [])
    {
        DB::beginTransaction();
        try {
            $room = Room::findOrFail($id);

            $room->update($data);

            $failedUploads = [];
            if (!empty($imageFiles)) {
                $oldImages = $room->images()->get();
                $room->images()->delete();
                foreach ($oldImages as $image) {
                    $this->deleteRoomImage($image->image_url);
                }

                foreach ($imageFiles as $file) {
                    $imagePath = $this->uploadRoomImage($file);
                    if ($imagePath) {
                        RoomImage::create([
                            'room_id' => $room->id,
                            'image_url' => $imagePath
                        ]);
                    } else {
                        $failedUploads[] = $file->getClientOriginalName();
                    }
                }
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
            Log::error('Cập nhật phòng thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function destroy(string $id)
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

    public function getTrashedRooms(string $querySearch, string $status, string $sortOption, string $perPage)
    {
        return $this->fetchRooms(true, $querySearch, $status, $sortOption, $perPage);
    }

    public function restore(string $id)
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

    public function forceDelete(string $id)
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

    private function uploadRoomImage(UploadedFile $imageFile)
    {
        try {
            $imageName = 'room-' . time() . '-' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = $imageFile->storeAs('images/rooms', $imageName, 'public');
            return Storage::url($imagePath);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    private function deleteRoomImage(string $imagePath)
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
