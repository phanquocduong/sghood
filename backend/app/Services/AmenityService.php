<?php

namespace App\Services;

use App\Models\Amenity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AmenityService
{
    public function fetchAmenities(bool $onlyTrashed, string $querySearch, string $sortOption, string $perPage): array {
        try {
            $query = $onlyTrashed ? Amenity::onlyTrashed() : Amenity::query();

            $this->applyFilters($query, $querySearch);
            $this->applySorting($query, $sortOption);

            $amenities = $query->paginate($perPage);

            return ['data' => $amenities];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách tiện ích', 'status' => 500];
        }
    }

     private function applyFilters($query, string $querySearch): void {
        if ($querySearch !== '') {
            $query->where('name', 'LIKE', '%' . $querySearch . '%');
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

    public function getAvailableAmenities(string $querySearch, string $sortOption, string $perPage): array {
        return $this->fetchAmenities(false, $querySearch, $sortOption, $perPage);
    }

    public function getTrashedAmenities(string $querySearch, string $sortOption, string $perPage): array {
        return $this->fetchAmenities(true, $querySearch, $sortOption, $perPage);
    }

    public function getAmenity(int $id): array {
        try {
            $amenity = Amenity::find($id);
            if (!$amenity) {
                return ['error' => 'Tiện ích không tìm thấy', 'status' => 404];
            }

            return ['data' => $amenity];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy tiện ích', 'status' => 500];
        }
    }

    public function createAmenity(array $data): array {
        DB::beginTransaction();
        try {
            $amenity = Amenity::create($data);

            DB::commit();
            return ['data' => $amenity];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi tạo tiện ích', 'status' => 500];
        }
    }

    public function updateAmenity(int $id, array $data): array
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::find($id);
            if (!$amenity) {
                return ['error' => 'Tiện ích không tìm thấy', 'status' => 404];
            }
            $amenity->update($data);

            DB::commit();
            return ['data' => $amenity];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi cập nhật tiện ích', 'status' => 500];
        }
    }

    public function deleteAmenity(int $id): array {
        DB::beginTransaction();
        try {
            $amenity = Amenity::find($id);
            if (!$amenity) {
                return ['error' => 'Tiện ích không tìm thấy', 'status' => 404];
            }
            $amenity->delete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi xóa tiện ích', 'status' => 500];
        }
    }

    public function restoreAmenity(int $id): array {
        DB::beginTransaction();
        try {
            $amenity = Amenity::onlyTrashed()->find($id);
            if (!$amenity) {
                return ['error' => 'Tiện ích không tìm thấy trong thùng rác', 'status' => 404];
            }
            $amenity->restore();

            DB::commit();
            return ['data' => $amenity];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi khôi phục tiện ích', 'status' => 500];
        }
    }

    public function forceDeleteAmenity(int $id): array {
        DB::beginTransaction();
        try {
            $amenity = Amenity::onlyTrashed()->find($id);
            if (!$amenity) {
                return ['error' => 'Tiện ích không tìm thấy trong thùng rác', 'status' => 404];
            }
            $amenity->forceDelete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi khôi phục tiện ích', 'status' => 500];
        }
    }
}
