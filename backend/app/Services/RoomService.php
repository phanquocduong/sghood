<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Motel;
use App\Models\Amenity;
use App\Models\RoomImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class RoomService
{
    // Tiêu chuẩn hóa phản hồi thành công
    protected function successResponse($data)
    {
        return ['data' => $data];
    }

    // Tiêu chuẩn hóa phản hồi lỗi
    protected function errorResponse($message, $status = 400)
    {
        return ['error' => $message, 'status' => $status];
    }

    // Xác thực và lấy thông tin nhà trọ
    public function validateMotel($motelId)
    {
        if (empty($motelId)) {
            return $this->errorResponse('Vui lòng chọn một nhà trọ.', 400);
        }

        $motel = Motel::where('status', 'Hoạt động')->find($motelId);
        if (!$motel) {
            return $this->errorResponse('Nhà trọ không tồn tại hoặc không hoạt động.', 404);
        }

        return $this->successResponse($motel);
    }

    // Lấy danh sách tiện nghi của phòng trọ
    public function getActiveRoomAmenities()
    {
        $amenities = Amenity::where('status', 'Hoạt động')
            ->where('type', 'Phòng trọ')
            ->get();
        return $this->successResponse($amenities);
    }

    // Lấy danh sách phòng trọ theo các tiêu chí
    public function fetchRooms(bool $onlyTrashed, string $motelId, string $querySearch, string $status, string $sortOption, string $perPage): array
    {
        try {
            // Kiểm tra nhà trọ trước
            $motelResult = $this->validateMotel($motelId);
            if (isset($motelResult['error'])) {
                return $motelResult;
            }

            $query = $onlyTrashed ? Room::onlyTrashed() : Room::query();
            $query->with(['motel', 'images', 'amenities']);
            $query->where('motel_id', $motelId);

            $this->applyFilters($query, $querySearch, $status);
            $this->applySorting($query, $sortOption);

            $rooms = $query->paginate($perPage);

            return $this->successResponse($rooms);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi lấy danh sách phòng trọ', 500);
        }
    }

    // Áp dụng bộ lọc cho truy vấn
    private function applyFilters($query, string $querySearch, string $status): void
    {
        if ($querySearch !== '') {
            $query->where('name', 'LIKE', '%' . $querySearch . '%');
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }
    }

    // Áp dụng sắp xếp cho truy vấn
    private function applySorting($query, string $sortOption): void
    {
        $sort = $this->handleSortOption($sortOption);
        $query->orderBy($sort['field'], $sort['order']);
    }

    // Xử lý tùy chọn sắp xếp
    public function handleSortOption(string $sortOption): array
    {
        switch ($sortOption) {
            case 'name_asc':
                return ['field' => 'name', 'order' => 'asc'];
            case 'name_desc':
                return ['field' => 'name', 'order' => 'desc'];
            case 'status_asc':
                return ['field' => 'status', 'order' => 'asc'];
            case 'status_desc':
                return ['field' => 'status', 'order' => 'desc'];
            case 'created_at_asc':
                return ['field' => 'created_at', 'order' => 'asc'];
            case 'created_at_desc':
                return ['field' => 'created_at', 'order' => 'desc'];
            default:
                return ['field' => 'created_at', 'order' => 'desc'];
        }
    }

    // Lấy danh sách phòng trọ theo các tiêu chí
    public function getRoomsByMotelId(string $motelId, string $querySearch, string $status, string $sortOption, string $perPage): array
    {
        return $this->fetchRooms(false, $motelId, $querySearch, $status, $sortOption, $perPage);
    }

    // Lấy danh sách phòng trọ đã xóa theo các tiêu chí
    public function getTrashedRoomsByMotelId(string $motelId, string $querySearch, string $status, string $sortOption, string $perPage): array
    {
        return $this->fetchRooms(true, $motelId, $querySearch, $status, $sortOption, $perPage);
    }

    // Lấy thông tin phòng trọ theo ID
    public function getRoom(string $id, bool $onlyTrashed = false): array
    {
        try {
            $query = $onlyTrashed ? Room::onlyTrashed() : Room::query();
            $query->with(['motel', 'images', 'amenities']);

            $room = $query->find($id);
            if (!$room) {
                return $this->errorResponse('Phòng trọ không tìm thấy', 404);
            }
            return $this->successResponse($room);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi tìm phòng trọ', 500);
        }
    }

    // đếm tổng số phòng trọ
    public function getAllRoomsCount(): int
    {
        try {
            return Room::count();
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return 0; // Trả về 0 nếu có lỗi
        }
    }
    // Đếm số phòng trọ có status = 'Đã thuê'
    public function getRentedRoomsCount(): int
    {
        try {
            return Room::where('status', 'Đã thuê')->count();
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return 0; // Trả về 0 nếu có lỗi
        }
    }

    // Tạo phòng trọ mới
    public function createRoom(array $data, array $imageFiles, int $mainImageIndex = 0): array
    {
        // Kiểm tra nhà trọ trước khi tạo phòng
        $motelResult = $this->validateMotel($data['motel_id'] ?? null);
        if (isset($motelResult['error'])) {
            return $motelResult;
        }

        DB::beginTransaction();
        try {
            $room = Room::create($data);

            $failedUploads = $this->processRoomImages($room->id, $imageFiles, $mainImageIndex);

            if (isset($data['amenities'])) {
                $room->amenities()->sync($data['amenities']);
            }

            DB::commit();

            $result = $this->successResponse($room->load(['motel', 'images', 'amenities']));
            if (!empty($failedUploads)) {
                $result['warnings'] = ['failed_images' => $failedUploads];
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi tạo phòng trọ: ' . $e->getMessage(), 500);
        }
    }

    // Xử lý hình ảnh chính
    public function processMainImage($roomId, $mainImageId)
    {
        try {
            // Đặt tất cả hình ảnh của phòng về is_main = 0
            RoomImage::where('room_id', $roomId)->update(['is_main' => 0]);

            // Đặt hình ảnh được chọn thành hình ảnh chính
            if ($mainImageId) {
                $image = RoomImage::where('id', $mainImageId)->where('room_id', $roomId)->first();
                if (!$image) {
                    return $this->errorResponse('Không tìm thấy hình ảnh được chọn làm hình ảnh chính');
                }

                $image->is_main = 1;
                $image->save();
            } else {
                // Nếu không có hình ảnh chính được chỉ định, đặt hình đầu tiên là hình chính
                $firstImage = RoomImage::where('room_id', $roomId)->first();
                if ($firstImage) {
                    $firstImage->is_main = 1;
                    $firstImage->save();
                }
            }

            return $this->successResponse(true);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi xử lý hình ảnh chính', 500);
        }
    }

    // Xử lý hình ảnh phòng trọ
    private function processRoomImages(int $roomId, array $imageFiles, int $mainImageIndex = 0): array
    {
        $failedUploads = [];
        $uploadedImages = [];

        foreach ($imageFiles as $index => $file) {
            try {
                $imagePath = $this->uploadRoomImage($file);
                if ($imagePath) {
                    $image = RoomImage::create([
                        'room_id' => $roomId,
                        'image_url' => $imagePath,
                        'is_main' => $index === $mainImageIndex ? 1 : 0
                    ]);
                    $uploadedImages[] = $image;
                } else {
                    $failedUploads[] = $file->getClientOriginalName();

                    // Adjust main image index if the main image failed to upload
                    if ($index === $mainImageIndex && $index < count($imageFiles) - 1) {
                        $mainImageIndex = $index + 1;
                    }
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $failedUploads[] = $file->getClientOriginalName();

                // Adjust main image index if the main image failed to upload
                if ($index === $mainImageIndex && $index < count($imageFiles) - 1) {
                    $mainImageIndex = $index + 1;
                }
            }
        }

        // Ensure at least one image is marked as main if uploads were successful
        if (!empty($uploadedImages)) {
            $hasMainImage = false;
            foreach ($uploadedImages as $image) {
                if ($image->is_main == 1) {
                    $hasMainImage = true;
                    break;
                }
            }

            if (!$hasMainImage) {
                $uploadedImages[0]->is_main = 1;
                $uploadedImages[0]->save();
            }
        }

        return $failedUploads;
    }

    // Tải lên hình ảnh phòng trọ
    private function uploadRoomImage(UploadedFile $imageFile): string|false
    {
        try {
            $manager = new ImageManager(new Driver());
            $filename = 'images/rooms/room-' . time() . '-' . uniqid() . '.webp';

            $image = $manager->read($imageFile)->toWebp(quality: 85)->toString();

            Storage::disk('public')->put($filename, $image);

            return '/storage/' . $filename;
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    // Cập nhật thông tin phòng trọ
    public function updateRoom(string $id, array $data, array $imageFiles, ?string $mainImageId = null): array
    {
        // Kiểm tra phòng tồn tại
        $roomResult = $this->getRoom($id);
        if (isset($roomResult['error'])) {
            return $roomResult;
        }

        // Kiểm tra nhà trọ nếu thay đổi motel_id
        if (isset($data['motel_id']) && $data['motel_id'] != $roomResult['data']->motel_id) {
            $motelResult = $this->validateMotel($data['motel_id']);
            if (isset($motelResult['error'])) {
                return $motelResult;
            }
        }

        DB::beginTransaction();
        try {
            $room = Room::findOrFail($id);
            $room->update($data);

            // Xử lý thêm hình ảnh mới
            $failedUploads = [];
            $newMainImageIndex = isset($data['new_main_image_index']) ? (int)$data['new_main_image_index'] : 0;

            if (!empty($imageFiles)) {
                $failedUploads = $this->processRoomImages($room->id, $imageFiles, $newMainImageIndex);
            }

            // Xử lý cập nhật ảnh chính
            if ($mainImageId) {
                // Existing image selected as main
                $mainImageResult = $this->processMainImage($room->id, $mainImageId);
                if (isset($mainImageResult['error'])) {
                    DB::rollBack();
                    return $mainImageResult;
                }
            } elseif (!empty($imageFiles) && isset($data['new_main_image_index'])) {
                // New image selected as main
                // The processRoomImages already handled this with $newMainImageIndex
                // Just ensure no existing image is marked as main
                RoomImage::where('room_id', $room->id)
                    ->where('created_at', '<', now()->subSeconds(5)) // Only existing images
                    ->update(['is_main' => 0]);
            } else {
                // No specific main image selected, ensure at least one image is main
                $this->ensureMainImage($room->id);
            }

            if (isset($data['amenities'])) {
                $room->amenities()->sync($data['amenities']);
            }

            DB::commit();

            $result = $this->successResponse($room->load(['motel', 'images', 'amenities']));
            if (!empty($failedUploads)) {
                $result['warnings'] = ['failed_images' => $failedUploads];
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi cập nhật phòng trọ: ' . $e->getMessage(), 500);
        }
    }

    // Xóa phòng trọ
    public function deleteRoom(string $id): array
    {
        DB::beginTransaction();
        try {
            $room = Room::find($id);
            if (!$room) {
                return $this->errorResponse('Phòng trọ không tìm thấy', 404);
            }
            $room->delete();

            DB::commit();
            return $this->successResponse(true);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi xóa phòng trọ', 500);
        }
    }

    // Khôi phục phòng trọ từ thùng rác
    public function restoreRoom(string $id): array
    {
        DB::beginTransaction();
        try {
            $room = Room::onlyTrashed()->find($id);
            if (!$room) {
                return $this->errorResponse('Phòng trọ không tìm thấy trong thùng rác', 404);
            }
            $room->restore();

            DB::commit();
            return $this->successResponse($room->load(['motel', 'images', 'amenities']));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi khôi phục phòng trọ', 500);
        }
    }

    // Xóa vĩnh viễn phòng trọ
    public function permanentlyDeleteRoom(string $id): array
    {
        DB::beginTransaction();
        try {
            $room = Room::withTrashed()->find($id);
            if (!$room) {
                return $this->errorResponse('Phòng trọ không tìm thấy', 404);
            }

            foreach ($room->images as $image) {
                $this->deleteRoomImage($image->image_url);
            }
            $room->images()->forceDelete();

            $room->amenities()->detach();
            $room->forceDelete();

            DB::commit();
            return $this->successResponse(true);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi xóa vĩnh viễn phòng trọ', 500);
        }
    }

    // Xóa hình ảnh phòng trọ
    public function deleteSingleRoomImage(int $imageId, int $roomId): array
    {
        DB::beginTransaction();
        try {
            $image = RoomImage::where('id', $imageId)->where('room_id', $roomId)->first();
            if (!$image) {
                return $this->errorResponse('Hình ảnh không tìm thấy', 404);
            }

            $isMainImage = $image->is_main == 1;
            $imageUrl = $image->image_url;

            // Xóa record trong database trước
            $image->delete();

            // Xóa file vật lý sau (không ảnh hưởng đến kết quả)
            $this->deleteRoomImage($imageUrl);

            // Nếu xóa hình ảnh chính, đặt hình đầu tiên còn lại làm hình chính
            if ($isMainImage) {
                $firstRemainingImage = RoomImage::where('room_id', $roomId)->first();
                if ($firstRemainingImage) {
                    $firstRemainingImage->is_main = 1;
                    $firstRemainingImage->save();
                }
            }

            DB::commit();
            return $this->successResponse(['message' => 'Hình ảnh đã được xóa thành công']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error deleting single room image: ' . $e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi xóa hình ảnh: ' . $e->getMessage(), 500);
        }
    }

    // Xóa hình ảnh phòng trọ
    private function deleteRoomImage(string $imagePath): bool
    {
        try {
            // Chuẩn hóa đường dẫn hình ảnh
            $filePath = str_replace('/storage/', '', $imagePath);

            // Luôn trả về true để xóa được record trong database
            // bất kể có xóa được file thực hay không
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
                Log::info('File deleted successfully: ' . $filePath);
            } else {
                // File không tồn tại nhưng không cần báo lỗi
                Log::warning('File not found but continuing: ' . $filePath);
            }

            return true;
        } catch (\Throwable $e) {
            // Ghi log lỗi nhưng vẫn trả về true để xóa được record
            Log::error('Error deleting image (continuing anyway): ' . $e->getMessage());
            return true;
        }
    }
    // Đảm bảo có ít nhất một ảnh chính
    private function ensureMainImage($roomId)
    {
        $hasMainImage = RoomImage::where('room_id', $roomId)->where('is_main', 1)->exists();

        if (!$hasMainImage) {
            $firstImage = RoomImage::where('room_id', $roomId)->first();
            if ($firstImage) {
                $firstImage->is_main = 1;
                $firstImage->save();
            }
        }
    }
    // Lấy số lượng phòng trống theo từng nhà trọ
    public function getAvailableRoomsPerMotel()
    {
        return Room::with('motel')
            ->selectRaw('motel_id, COUNT(*) as total_rooms, COUNT(CASE WHEN status = "Trống" THEN 1 END) as available_rooms')
            ->groupBy('motel_id')
            ->get();
    }
}
