<?php
namespace App\Services;

use App\Models\Motel;
use App\Models\MotelImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MotelService {
    public function fetchMotels(bool $onlyTrashed, string $querySearch, string $status, string $sortOption, int $perPage): array {
        try {
            $query = $onlyTrashed ? Motel::onlyTrashed() : Motel::query();
            $query->with(['district', 'images', 'amenities']);

            $this->applyFilters($query, $querySearch, $status);
            $this->applySorting($query, $sortOption);

            $motels = $query->paginate($perPage);

            return ['data' => $motels];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách nhà trọ', 'status' => 500];
        }
    }

    private function applyFilters($query, string $querySearch, string $status): void {
        if ($querySearch !== '') {
            $query->where('address', 'LIKE', '%' . $querySearch . '%');
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
            case 'created_at_asc':
                return ['field' => 'created_at', 'order' => 'asc'];
            case 'created_at_desc':
                return ['field' => 'created_at', 'order' => 'desc'];
            default:
                return ['field' => 'created_at', 'order' => 'desc'];
        }
    }

    public function getAvailableMotels(string $querySearch, string $status, string $sortOption, int $perPage): array {
        return $this->fetchMotels(false, $querySearch, $status, $sortOption, $perPage);
    }

    public function getTrashedMotels(string $querySearch, string $status, string $sortOption, int $perPage): array {
        return $this->fetchMotels(true, $querySearch, $status, $sortOption, $perPage);
    }

    public function getMotel(int $id, bool $onlyTrashed = false): array {
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

    public function createMotel(array $data, array $imageFiles): array {
        DB::beginTransaction();
        try {
            $data['slug'] = $this->generateUniqueSlug($data['address']);

            $motel = Motel::create($data);

            $failedUploads = $this->processMotelImages($motel->id, $imageFiles);
            $motel->amenities()->sync($data['amenities']);

            DB::commit();

            $result = ['data' => $motel->load(['district', 'images', 'amenities'])];
            if (!empty($failedUploads)) {
                $result['warnings'] = ['failed_images' => $failedUploads];
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi tạo nhà trọ', 'status' => 500];
        }
    }

    private function generateUniqueSlug(string $address): string {
        $slug = Str::slug($address);
        $originalSlug = $slug;
        $counter = 1;

        while (Motel::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    private function processMotelImages(int $motelId, array $imageFiles): array {
        $failedUploads = [];
        foreach ($imageFiles as $file) {
            $imagePath = $this->uploadMotelImage($file);
            if ($imagePath) {
                MotelImage::create([
                    'motel_id' => $motelId,
                    'image_url' => $imagePath
                ]);
            } else {
                $failedUploads[] = $file->getClientOriginalName();
            }
        }
        return $failedUploads;
    }

    private function uploadMotelImage(UploadedFile $imageFile): string|false {
        try {
            $imageName = 'motel-' . time() . '-' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = $imageFile->storeAs('images/motels', $imageName, 'public');
            return Storage::url($imagePath);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public function updateMotel(int $id, array $data, array $imageFiles): array {
        DB::beginTransaction();
        try {
            $motel = Motel::find($id);
            if (!$motel) {
                return ['error' => 'Nhà trọ không tìm thấy', 'status' => 404];
            }

            if (isset($data['address']) && $data['address'] !== $motel->address) {
                $data['slug'] = $this->generateUniqueSlug($data['address']);
            }

            $motel->update($data);

            if (!empty($imageFiles)) {
                foreach ($motel->images()->get() as $image) {
                    $this->deleteMotelImage($image->image_url);
                }
                $motel->images()->delete();

                $failedUploads = $this->processMotelImages($motel->id, $imageFiles);
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

    public function deleteMotel(int $id): array {
        DB::beginTransaction();
        try {
            $motel = Motel::find($id);
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

    public function restoreMotel(int $id) {
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

    public function forceDeleteMotel(int $id) {
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

    private function deleteMotelImage(string $imagePath) {
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
