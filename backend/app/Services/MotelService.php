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
    public function fetchMotels(bool $onlyTrashed, string $querySearch, string $status, string $sortOption, int $perPage) {
        try {
            $query = $onlyTrashed ? Motel::onlyTrashed() : Motel::query();

            if ($querySearch !== '') {
                $query->where('name', 'LIKE', '%' . $querySearch . '%');
            }

            if (!empty($status)) {
                $query->where('status', $status);
            }

            $sort = $this->handleSortOption($sortOption);
            $query->orderBy($sort['field'], $sort['order']);

            $motels = $query->paginate($perPage);

            return ['data' => $motels->load(['images', 'amenities'])];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function handleSortOption(string $sortOption) {
        switch ($sortOption) {
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

    public function getAllMotels(string $querySearch, string $status, string $sortOption, int $perPage) {
        return $this->fetchMotels(false, $querySearch, $status, $sortOption, $perPage);
    }

    public function getMotelById(int $id) {
        try {
            $motel = Motel::findOrFail($id);
            return ['data' => $motel->load(['images', 'amenities'])];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function createMotel(array $data, array $imageFiles) {
        DB::beginTransaction();
        try {
            $data['slug'] = $this->generateUniqueSlug($data['address']);

            $motel = Motel::create($data);

            foreach ($imageFiles as $file) {
                $imagePath = $this->uploadMotelImage($file);
                if ($imagePath) {
                    MotelImage::create([
                        'motel_id' => $motel['id'],
                        'image_url' => $imagePath
                    ]);
                } else {
                    $failedUploads[] = $file->getClientOriginalName();
                }
            }

            DB::commit();

            // Xử lý amenities
            if (isset($data['amenities']) && !empty($data['amenities'])) {
                $motel->amenities()->sync($data['amenities']);
            }

            $result = ['data' => $motel->load(['images', 'amenities'])];
            if (!empty($failedUploads)) {
                $result['warnings'] = ['failed_images' => $failedUploads];
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function updateMotel(int $id, array $data, array $imageFiles) {
        DB::beginTransaction();
        try {
            $motel = Motel::findOrFail($id);

            if (isset($data['address']) && $data['address'] !== $motel->address) {
                $data['slug'] = $this->generateUniqueSlug($data['address']);
            }

            $motel->update($data);

            $failedUploads = [];
            if (!empty($imageFiles)) {
                $oldImages = $motel->images()->get();
                // Optional: Xóa ảnh cũ nếu cần (tùy yêu cầu)
                $motel->images()->delete();
                foreach ($oldImages as $image) {
                    $this->deleteMotelImage($image->image_url);
                }

                // Thêm ảnh mới
                foreach ($imageFiles as $file) {
                    $imagePath = $this->uploadMotelImage($file);
                    if ($imagePath) {
                        MotelImage::create([
                            'motel_id' => $motel->id,
                            'image_url' => $imagePath
                        ]);
                    } else {
                        $failedUploads[] = $file->getClientOriginalName();
                    }
                }
            }

            if (isset($data['amenities'])) {
                $motel->amenities()->sync($data['amenities']);
            }

            DB::commit();

            $result = ['data' => $motel->load(['images', 'amenities'])];
            if (!empty($failedUploads)) {
                $result['warnings'] = ['failed_images' => $failedUploads];
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function deleteMotel(int $id) {
        DB::beginTransaction();
        try {
            $motel = Motel::findOrFail($id);
            $motel->delete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function getTrashedMotels(string $querySearch, string $status, string $sortOption, int $perPage) {
        return $this->fetchMotels(true, $querySearch, $status, $sortOption, $perPage);
    }

    public function restoreMotel(int $id) {
        DB::beginTransaction();
        try {
            $motel = Motel::onlyTrashed()->findOrFail($id);
            $motel->restore();
            DB::commit();
            return ['data' => $motel->load(['images', 'amenities'])];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function forceDeleteMotel(int $id) {
        DB::beginTransaction();
        try {
            $motel = Motel::withTrashed()->findOrFail($id);

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
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    private function generateUniqueSlug(string $address) {
        $slug = Str::slug($address);
        $originalSlug = $slug;
        $counter = 1;

        while (Motel::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    private function uploadMotelImage(UploadedFile $imageFile) {
        try {
            $imageName = 'motel-' . time() . '-' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = $imageFile->storeAs('images/motels', $imageName, 'public');
            return Storage::url($imagePath);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
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
