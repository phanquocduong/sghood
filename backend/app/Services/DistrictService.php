<?php

namespace App\Services;

use App\Models\District;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DistrictService {
    private function fetchDistricts(bool $onlyTrashed, string $querySearch, string $sortOption, int $perPage): array {
        try {
            $query = $onlyTrashed ? District::onlyTrashed() : District::query();
            $query->with(['motels']);

            if ($querySearch !== '') {
                $query->where('name', 'LIKE', '%' . $querySearch . '%');
            }

            $this->applySorting($query, $sortOption);

            $districts = $query->paginate($perPage);

            return ['data' => $districts];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách khu vực', 'status' => 500];
        }
    }

    private function applySorting($query, string $sortOption): void {
        $sort = $this->handleSortOption($sortOption);
        $query->orderBy($sort['field'], $sort['order']);
    }

    private function handleSortOption(string $sortOption): array {
        switch ($sortOption) {
            case 'name_asc':
                return ['field' => 'name', 'order' => 'asc'];
            case 'name_asc':
                return ['field' => 'name', 'order' => 'desc'];
            case 'created_at_asc':
                return ['field' => 'created_at', 'order' => 'asc'];
            case 'created_at_desc':
                return ['field' => 'created_at', 'order' => 'desc'];
            default:
                return ['field' => 'created_at', 'order' => 'desc'];
        }
    }

    public function getAvailableDistricts(string $querySearch, string $sortOption, int $perPage): array {
        return $this->fetchDistricts(false, $querySearch, $sortOption, $perPage);
    }

    public function getTrashedDistricts(string $querySearch, string $sortOption, int $perPage): array {
        return $this->fetchDistricts(true, $querySearch, $sortOption, $perPage);
    }

    public function getDistrict(int $id, bool $onlyTrashed = false): array {
        try {
            $query = $onlyTrashed ? District::onlyTrashed() : District::query();
            $query->with(['motels']);

            $district = $query->find($id);
            if (!$district) {
                return ['error' => 'Khu vực không tìm thấy', 'status' => 404];
            }
            return ['data' => $district];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy khu vực', 'status' => 500];
        }
    }

    public function createDistrict(array $data, UploadedFile $imageFile): array {
        DB::beginTransaction();
        try {
            $failedUpload = false;
            $imagePath = $this->uploadDistrictImage($imageFile);
            if ($imagePath) {
                $data['image'] = $imagePath;
            } else {
                $failedUpload = true;
            }

            $district = District::create($data);
            DB::commit();

            $result = ['data' => $district->load(['motels'])];
            if ($failedUpload) {
                $result['warning'] = $failedUpload;
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi tạo khu vực', 'status' => 500];
        }
    }

    private function uploadDistrictImage(UploadedFile $imageFile): string|false {
        try {
            $imageName = 'district-' . time() . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = $imageFile->storeAs('images/districts', $imageName, 'public');
            return Storage::url($imagePath);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public function updateDistrict(int $id, array $data, ?UploadedFile $imageFile = null): array {
        DB::beginTransaction();
        try {
            $district = District::find($id);
            if (!$district) {
                return ['error' => 'Khu vực không tìm thấy', 'status' => 404];
            }
            if (!empty($imageFile)) {
                if ($district->image) {
                    $this->deleteDistrictImage($district->image);
                }
                $failedUpload = false;
                $imagePath = $this->uploadDistrictImage($imageFile);
                if ($imagePath) {
                    $data['image'] = $imagePath;
                } else {
                    $failedUpload = true;
                }
            }
            $district->update($data);
            DB::commit();

            $result = ['data' => $district->load(['motels'])];
            if ($failedUpload) {
                $result['warning'] = $failedUpload;
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi cập nhật khu vực', 'status' => 500];
        }
    }

    public function deleteDistrict(int $id): array {
        DB::beginTransaction();
        try {
            $district = District::find($id);
            if (!$district) {
                return ['error' => 'Khu vực không tìm thấy', 'status' => 404];
            }
            $district->delete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi xóa khu vực', 'status' => 500];
        }
    }

    public function restoreDistrict(int $id): array {
        DB::beginTransaction();
        try {
            $district = District::onlyTrashed()->find($id);
            if (!$district) {
                return ['error' => 'Khu vực không tìm thấy trong thùng rác', 'status' => 404];
            }
            $district->restore();

            DB::commit();
            return ['data' => $district->load(['motels'])];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi khôi phục khu vực', 'status' => 500];
        }
    }

    public function forceDeleteDistrict(int $id): array {
        DB::beginTransaction();
        try {
            $district = District::onlyTrashed()->find($id);
            if (!$district) {
                return ['error' => 'Khu vực không tìm thấy trong thùng rác', 'status' => 404];
            }
            $this->deleteDistrictImage($district->image);
            $district->forceDelete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi xóa vĩnh viễn khu vực', 'status' => 500];
        }
    }

    private function deleteDistrictImage(string $imagePath): void {
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
