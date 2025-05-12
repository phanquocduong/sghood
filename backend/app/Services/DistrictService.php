<?php

namespace App\Services;

use App\Models\District;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DistrictService {
    public function fetchDistricts(bool $onlyTrashed, string $querySearch, string $sortOption, int $perPage) {
        try {
            $query = $onlyTrashed ? District::onlyTrashed() : District::query();

            if ($querySearch !== '') {
                $query->where('name', 'LIKE', '%' . $querySearch . '%');
            }

            $sort = $this->handleSortOption($sortOption);
            $query->orderBy($sort['field'], $sort['order']);

            $districts = $query->paginate($perPage);

            return ['data' => $districts];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function handleSortOption(string $sortOption) {
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

    public function getAllDistricts(string $querySearch, string $sortOption, int $perPage) {
        return $this->fetchDistricts(false, $querySearch, $sortOption, $perPage);
    }

    public function getDistrictById(int $id) {
        try {
            $district = District::findOrFail($id);
            return ['data' => $district];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function createDistrict(array $data, UploadedFile $imageFile) {
        DB::beginTransaction();
        try {
            $imagePath = $this->uploadDistrictImage($imageFile);
            if (!$imagePath) {
                return ['error' => 'Upload hình không thành công', 'status' => 500];
            }
            $data['image'] = $imagePath;
            $district = District::create($data);
            DB::commit();
            return ['data' => $district];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function updateDistrict(int $id, array $data, ?UploadedFile $imageFile = null) {
        DB::beginTransaction();
        try {
            $district = District::findOrFail($id);
            if (!empty($imageFile)) {
                // Xóa hình ảnh cũ nếu tồn tại
                if ($district->image) {
                    $this->deleteDistrictImage($district->image);
                }
                // Tải hình ảnh mới lên
                $imagePath = $this->uploadDistrictImage($imageFile);
                if (!$imagePath) {
                    return ['error' => 'Upload hình không thành công', 'status' => 500];
                }
                $data['image'] = $imagePath;
            }
            $district->update($data);
            DB::commit();
            return ['data' => $district];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function deleteDistrict(int $id) {
        DB::beginTransaction();
        try {
            $district = District::findOrFail($id);
            $district->delete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function getTrashedDistricts(string $querySearch, string $sortOption, int $perPage) {
        return $this->fetchDistricts(true, $querySearch, $sortOption, $perPage);
    }

    public function restoreDistrict(int $id) {
        DB::beginTransaction();
        try {
            $district = District::onlyTrashed()->findOrFail($id);
            $district->restore();
            DB::commit();
            return ['data' => $district];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function forceDeleteDistrict(int $id) {
        DB::beginTransaction();
        try {
            $district = District::onlyTrashed()->findOrFail($id);
            $this->deleteDistrictImage($district->image);
            $district->forceDelete();
            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    private function uploadDistrictImage(UploadedFile $imageFile) {
        try {
            $imageName = 'district-' . time() . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = $imageFile->storeAs('images/districts', $imageName, 'public');
            return Storage::url($imagePath);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    private function deleteDistrictImage(string $imagePath) {
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
