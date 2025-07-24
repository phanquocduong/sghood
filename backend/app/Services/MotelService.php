<?php

namespace App\Services;

use App\Models\Motel;
use App\Models\MotelImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;

class MotelService
{
    /**
     * Lấy danh sách nhà trọ với filters và pagination
     */
    public function fetchMotels(bool $onlyTrashed, string $querySearch, string $status, string $area, string $sortOption, int $perPage): array
    {
        try {
            $query = $onlyTrashed ? Motel::onlyTrashed() : Motel::query();
            $query->with(['district', 'images', 'amenities']);
            $query->withCount('rooms');

            $this->applyFilters($query, $querySearch, $status, $area);
            $this->applySorting($query, $sortOption);

            $motels = $query->paginate($perPage);

            return ['data' => $motels];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách nhà trọ', 'status' => 500];
        }
    }

    private function applyFilters($query, string $querySearch, string $status, string $area): void
    {
        if ($querySearch !== '') {
            $query->where(function ($q) use ($querySearch) {
                $q->where('name', 'LIKE', '%' . $querySearch . '%')
                    ->orWhere('address', 'LIKE', '%' . $querySearch . '%');
            });
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($area)) {
            $query->where('district_id', $area);
        }
    }

    private function applySorting($query, string $sortOption): void
    {
        if (empty($sortOption)) {
            $query->orderBy('created_at', 'desc');
            return;
        }

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

    public function getAvailableMotels(string $querySearch, string $status, string $area, string $sortOption, int $perPage): array
    {
        return $this->fetchMotels(false, $querySearch, $status, $area, $sortOption, $perPage);
    }

    public function getTrashedMotels(string $querySearch, string $status, string $area, string $sortOption, int $perPage): array
    {
        return $this->fetchMotels(true, $querySearch, $status, $area, $sortOption, $perPage);
    }

    public function getMotel(int $id, bool $onlyTrashed = false): array
    {
        try {
            $query = $onlyTrashed ? Motel::onlyTrashed() : Motel::query();
            $query->with(['district', 'images', 'amenities']);

            $motel = $query->find($id);
            if (!$motel) {
                return ['error' => 'Nhà trọ không tìm thấy', 'status' => 404];
            }
            return ['data' => $motel];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy thông tin nhà trọ', 'status' => 500];
        }
    }

    public function findById($id)
    {
        return Motel::find($id);
    }

    public function findByName($name)
    {
        return Motel::where('name', $name)->first();
    }


    /**
     * Tạo nhà trọ mới
     *
     * @param array $data
     * @param array $imageFiles
     * @param int $mainImageIndex
     * @return array
     */
    public function createMotel(array $data, array $imageFiles, int $mainImageIndex = 0): array
    {
        DB::beginTransaction();
        try {
            $data['slug'] = $this->generateUniqueSlug($data['name']);

            if (!isset($data['status'])) {
                $data['status'] = 'Hoạt động';
            }

            $motel = Motel::create(array_filter($data, function ($value) {
                return $value !== null && $value !== '';
            }));

            // Xử lý images nếu có
            $failedUploads = [];
            if (!empty($imageFiles)) {
                $failedUploads = $this->processMotelImages($motel->id, $imageFiles, $mainImageIndex);
            }

            // Xử lý amenities nếu có
            if (isset($data['amenities']) && is_array($data['amenities'])) {
                $motel->amenities()->sync($data['amenities']);
            }

            DB::commit();

            $result = ['data' => $motel->load(['district', 'images', 'amenities'])];
            if (!empty($failedUploads)) {
                $result['warnings'] = ['failed_images' => $failedUploads];
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo nhà trọ: ' . $e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi tạo nhà trọ: ' . $e->getMessage(), 'status' => 500];
        }
    }

    /**
     * Cập nhật thông tin nhà trọ và xử lý ảnh
     *
     * @param int $id
     * @param array $data
     * @param array $imageFiles
     * @return array
     */
    public function updateMotel(int $id, array $data, array $imageFiles): array
    {
        DB::beginTransaction();
        try {
            $motel = Motel::findOrFail($id);

            // Cập nhật slug nếu tên thay đổi
            if (isset($data['name']) && $data['name'] !== $motel->name) {
                $data['slug'] = $this->generateUniqueSlug($data['name']);
            }

            // Cập nhật thông tin nhà trọ
            $motel->update(array_filter($data, function ($value) {
                return $value !== null && $value !== '';
            }));

            // Xử lý ảnh mới
            $failedUploads = [];
            $newMainImageIndex = isset($data['new_main_image_index']) ? (int) $data['new_main_image_index'] : null;
            if (!empty($imageFiles)) {
                $failedUploads = $this->processMotelImages($motel->id, $imageFiles, $newMainImageIndex ?? 0);
            }

            // Xử lý ảnh chính
            if (isset($data['is_main']) && !empty($data['is_main'])) {
                // Ảnh hiện có được chọn làm ảnh chính
                Log::info('Processing existing main image', ['is_main' => $data['is_main']]);
                $mainImageResult = $this->processMainImage($motel->id, $data['is_main']);
                if (isset($mainImageResult['error'])) {
                    DB::rollBack();
                    return $mainImageResult;
                }
            } elseif (!empty($imageFiles) && isset($data['new_main_image_index'])) {
                // Ảnh mới được chọn làm ảnh chính
                Log::info('Processing new main image', ['new_main_image_index' => $data['new_main_image_index']]);
                // Xóa cờ is_main của ảnh hiện có
                MotelImage::where('motel_id', $motel->id)
                    ->where('created_at', '<', now()->subSeconds(5))
                    ->update(['is_main' => 0]);
                // processMotelImages đã đặt is_main cho ảnh mới
            } else {
                // Không có ảnh chính cụ thể được chọn, đảm bảo có ít nhất một ảnh chính
                $this->ensureMainImage($motel->id);
            }

            // Đồng bộ tiện ích
            if (isset($data['amenities']) && is_array($data['amenities'])) {
                $motel->amenities()->sync($data['amenities']);
            }

            DB::commit();

            $result = ['data' => $motel->load(['district', 'images', 'amenities'])];
            if (!empty($failedUploads)) {
                $result['warnings'] = ['failed_images' => $failedUploads];
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật nhà trọ: ' . $e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi cập nhật nhà trọ: ' . $e->getMessage(), 'status' => 500];
        }
    }

    /**
     * Xóa nhà trọ (soft delete)
     */
    public function deleteMotel(int $id): array
    {
        DB::beginTransaction();
        try {
            $motel = Motel::find($id);
            if (!$motel) {
                return ['error' => 'Nhà trọ không tìm thấy', 'status' => 404];
            }

            // Kiểm tra khoá ngoại
            if ($motel->rooms()->count() > 0) {
                return ['error' => 'Không thể xóa nhà trọ này vì có phòng liên quan, vui lòng xoá các phòng liên quan trước!', 'status' => 400];
            }

            $motel->delete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi xóa nhà trọ', 'status' => 500];
        }
    }

    /**
     * Khôi phục nhà trọ từ thùng rác
     */
    public function restoreMotel(int $id): array
    {
        DB::beginTransaction();
        try {
            $motel = Motel::onlyTrashed()->find($id);
            if (!$motel) {
                return ['error' => 'Nhà trọ không tìm thấy trong thùng rác', 'status' => 404];
            }
            $motel->restore();

            DB::commit();
            return ['data' => $motel->load(['district', 'images', 'amenities'])];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi khôi phục nhà trọ', 'status' => 500];
        }
    }

    /**
     * Xóa vĩnh viễn nhà trọ
     */
    public function forceDeleteMotel(int $id): array
    {
        DB::beginTransaction();
        try {
            $motel = Motel::withTrashed()->find($id);
            if (!$motel) {
                return ['error' => 'Nhà trọ không tìm thấy trong thùng rác', 'status' => 404];
            }

            foreach ($motel->images as $image) {
                $this->deleteMotelImage($image->image_url);
            }
            $motel->images()->forceDelete();

            // Xóa mối quan hệ amenities
            $motel->amenities()->detach();

            // Xóa vĩnh viễn motel
            $motel->forceDelete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi xóa vĩnh viễn nhà trọ', 'status' => 500];
        }
    }

    /**
     * Xử lý ảnh chính từ ảnh hiện có
     *
     * @param int $motelId
     * @param string $mainImageId
     * @return array
     */
    private function processMainImage(int $motelId, string $mainImageId): array
    {
        try {
            MotelImage::where('motel_id', $motelId)->update(['is_main' => 0]);
            $image = MotelImage::where('motel_id', $motelId)->find($mainImageId);
            if ($image) {
                $image->update(['is_main' => 1]);
                return ['data' => true];
            }
            return ['error' => 'Không tìm thấy ảnh chính', 'status' => 404];
        } catch (\Throwable $e) {
            Log::error('Lỗi xử lý ảnh chính: ' . $e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi xử lý ảnh chính', 'status' => 500];
        }
    }

    /**
     * Xử lý các ảnh mới được tải lên
     *
     * @param int $motelId
     * @param array $imageFiles
     * @param int $mainImageIndex
     * @return array
     */
    private function processMotelImages(int $motelId, array $imageFiles, int $mainImageIndex = 0): array
    {
        $failedUploads = [];
        $uploadedImages = [];

        foreach ($imageFiles as $index => $file) {
            try {
                $imagePath = $this->uploadMotelImage($file);
                if ($imagePath) {
                    $image = MotelImage::create([
                        'motel_id' => $motelId,
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
                Log::error('Lỗi tải ảnh: ' . $e->getMessage());
                $failedUploads[] = $file->getClientOriginalName();
                // Adjust main image index if the main image failed to upload
                if ($index === $mainImageIndex && $index < count($imageFiles) - 1) {
                    $mainImageIndex = $index + 1;
                }
            }
        }

        // Đảm bảo có ít nhất một ảnh chính nếu không có ảnh nào được đánh dấu
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

    /**
     * Đảm bảo có ít nhất một ảnh được đánh dấu là ảnh chính
     *
     * @param int $motelId
     * @return void
     */
    public function ensureMainImage(int $motelId): void
    {
        $hasMainImage = MotelImage::where('motel_id', $motelId)->where('is_main', 1)->exists();

        if (!$hasMainImage) {
            $firstImage = MotelImage::where('motel_id', $motelId)->first();
            if ($firstImage) {
                $firstImage->update(['is_main' => 1]);
            }
        }
    }

    /**
     * Tải ảnh lên và trả về đường dẫn
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string|null
     */
    private function uploadMotelImage(UploadedFile $file): ?string
    {
        try {
            $manager = new ImageManager(new Driver());
            $filename = 'images/motels/motel-' . time() . '-' . uniqid() . '.' . 'webp';

            $image = $manager->read($file)->toWebp(quality: 85)->toString();

            Storage::disk('public')->put($filename, $image);

            return '/storage/' . $filename;
        } catch (\Throwable $e) {
            Log::error('Lỗi tải ảnh: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Xóa file ảnh vật lý
     */
    private function deleteMotelImage(string $imagePath): void
    {
        try {
            if ($imagePath) {
                $filePath = str_replace('/storage/', '', $imagePath);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Lỗi xóa file ảnh: ' . $e->getMessage());
        }
    }

    /**
     * Tạo slug duy nhất từ tên nhà trọ
     *
     * @param string $name
     * @return string
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Motel::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }
}
