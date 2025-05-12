<?php

namespace App\Services;

use App\Models\Amenity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AmenityService
{
    public function fetchAmenities(bool $onlyTrashed, string $querySearch, string $sortOption, string $perPage): array
    {
        try {
            $query = $onlyTrashed ? Amenity::onlyTrashed() : Amenity::query();

            $this->applyFilters($query, $querySearch);
            $this->applySorting($query, $sortOption);

            $amenities = $query->paginate($perPage);

            return ['data' => $amenities];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    private function applyFilters($query, string $querySearch): void
    {
        if ($querySearch !== '') {
                $query->where(function ($q) use ($querySearch) {
                    $q->where('name', 'LIKE', '%' . $querySearch . '%')
                      ->orWhere('description', 'LIKE', '%' . $querySearch . '%');
            });
        }
    }

    private function applySorting($query, string $sortOption): void
    {
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

    public function getAllAmenities(string $querySearch, string $sortOption, string $perPage): array
    {
        return $this->fetchAmenities(false, $querySearch, $sortOption, $perPage);
    }

    public function getAmenity(int $id, bool $withTrashed = false): array
    {
        try {
            $query = Amenity::query();
            if ($withTrashed) {
                $query->withTrashed();
            }
            $amenity = $query->findOrFail($id);
            return ['data' => $amenity];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function create(array $data): array
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::create($data);

            DB::commit();
            return ['data' => $amenity];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Tạo tiện nghi thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function update(string $id, array $data): array
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::findOrFail($id);
            $amenity->update($data);

            DB::commit();
            return ['data' => $amenity];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Cập nhật tiện nghi thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function destroy(string $id): array
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::findOrFail($id);
            $amenity->delete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Xóa tiện nghi thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function getTrashedAmenities(string $querySearch, string $sortOption, string $perPage): array
    {
        return $this->fetchAmenities(true, $querySearch, $sortOption, $perPage);
    }

    public function restore(string $id): array
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::onlyTrashed()->findOrFail($id);
            $amenity->restore();
            DB::commit();
            return ['data' => $amenity];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Khôi phục tiện nghi thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function forceDelete(string $id): array
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::withTrashed()->findOrFail($id);
            $amenity->forceDelete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Xóa vĩnh viễn tiện nghi thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }
}
