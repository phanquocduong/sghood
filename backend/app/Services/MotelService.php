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
    public function fetchMotels(bool $onlyTrashed, string $querySearch, string $status, string $area, string $sortOption, int $perPage): array
    {
        try {
            $query = $onlyTrashed ? Motel::onlyTrashed() : Motel::query();
            $query->with(['district', 'images', 'amenities']);

            // Thêm withCount để đếm số lượng phòng
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
            // Mặc định sắp xếp theo created_at giảm dần
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
                // Mặc định sắp xếp theo created_at giảm dần (mới nhất trước)
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
            return ['error' => 'Đã xảy ra lỗi khi tạo nhà trọ', 'status' => 500];
        }
    }

    public function createMotel(array $data, array $imageFiles, int $mainImageIndex = 0): array
    {
        DB::beginTransaction();
        try {
            $data['slug'] = $this->generateUniqueSlug($data['name']);

            if (!isset($data['status'])) {
                $data['status'] = 'Hoạt động'; // Đảm bảo trạng thái mặc định
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

    private function processMotelImages(int $motelId, array $imageFiles, int $mainImageIndex = 0): array
    {
        $failedUploads = [];
        $totalImages = count($imageFiles);

        if ($totalImages === 0) {
            return $failedUploads;
        }

        // Upload và lưu từng ảnh
        foreach ($imageFiles as $index => $file) {
           $isMain = ($index === $mainImageIndex) ? 1 : 0; // Ảnh đầu tiên sẽ là ảnh chính

            $imagePath = $this->uploadMotelImage($file);
            if ($imagePath) {
                MotelImage::create([
                    'motel_id' => $motelId,
                    'image_url' => $imagePath,
                    'is_main' => $isMain
                ]);
            } else {
                $failedUploads[] = $file->getClientOriginalName();
            }
        }

        // Đảm bảo chỉ có đúng 1 ảnh is_main = 1
        $mainImages = MotelImage::where('motel_id', $motelId)->where('is_main', 1)->get();

        if ($mainImages->count() === 0) {
            // Không có ảnh chính, gán ảnh đầu tiên
            $firstImage = MotelImage::where('motel_id', $motelId)->first();
            if ($firstImage) {
                $firstImage->update(['is_main' => 1]);
            }
        } elseif ($mainImages->count() > 1) {
            // Có nhiều ảnh chính, chỉ giữ ảnh chính đầu tiên
            foreach ($mainImages as $i => $image) {
                $image->update(['is_main' => $i === 0 ? 1 : 0]);
            }
        }

        return $failedUploads;
    }


    private function uploadMotelImage(UploadedFile $imageFile): string|false
    {
        try {
            $manager = new ImageManager(new Driver());
            $filename = 'images/motels/motel-' . time() . '-' . uniqid() . '.' . 'webp';

            $image = $manager->read($imageFile)->toWebp(quality: 85)->toString();

            Storage::disk('public')->put($filename, $image);

            return '/storage/' . $filename;
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public function updateMotel(int $id, array $data, array $imageFiles): array
    {
        Log::info('FULL REQUEST DATA', request()->all());
        DB::beginTransaction();
        try {
            $motel = Motel::find($id);
            if (!$motel) {
                return ['error' => 'Nhà trọ không tìm thấy', 'status' => 404];
            }

            if (isset($data['name']) && $data['name'] !== $motel->name) {
                $data['slug'] = $this->generateUniqueSlug($data['name']);
            }

            $motel->update($data);

            // Xử lý xóa ảnh hiện tại
            if (!empty($data['delete_images'])) {
                foreach ($data['delete_images'] as $imageId) {
                    $image = MotelImage::find($imageId);
                    if ($image) {
                        $this->deleteMotelImage($image->image_url);
                        $image->delete();
                    }
                }
            }

            // Xử lý ảnh mới
            if (!empty($imageFiles)) {
                // Xóa tất cả ảnh hiện tại trước khi thêm ảnh mới (nếu có upload lại)
                $existingImages = $motel->images()->get();
                foreach ($existingImages as $image) {
                    $this->deleteMotelImage($image->image_url);
                    $image->delete();
                }

                $mainImageIndex = request()->input('main_image_index', 0); // Lấy index ảnh chính từ form
                $failedUploads = $this->processMotelImages($motel->id, $imageFiles, $mainImageIndex);
            } elseif (request()->has('main_image_index')) {
                // Cập nhật is_main cho ảnh hiện tại nếu có thay đổi
                $mainImageId = request()->input('main_image_index');
                MotelImage::where('motel_id', $id)->update(['is_main' => 0]);
                MotelImage::where('id', $mainImageId)->update(['is_main' => 1]);
            }

            if (isset($data['amenities'])) {
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
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi cập nhật nhà trọ', 'status' => 500];
        }
    }

    public function deleteMotel(int $id): array
    {
        DB::beginTransaction();
        try {
            $motel = Motel::find($id);
            // Kiểm tra khoá ngoại
            if ($motel->rooms()->count() > 0) {
                return ['error' => 'Không thể xóa nhà trọ này vì có phòng liên quan, vui lòng xoá các phòng liên quan trước!', 'status' => 400];
            }
            if (!$motel) {
                return ['error' => 'Nhà trọ không tìm thấy', 'status' => 404];
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

    public function restoreMotel(int $id)
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

    public function forceDeleteMotel(int $id)
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

    private function deleteMotelImage(string $imagePath)
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
