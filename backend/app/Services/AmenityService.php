<?php

namespace App\Services;

use App\Models\Amenity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AmenityService
{
    public function fetchAmenities(bool $onlyTrashed, string $querySearch, string $sortOption, string $perPage)
    {
        try {
            $query = $onlyTrashed ? Amenity::onlyTrashed() : Amenity::query();

            if ($querySearch !== '') {
                $query->where(function ($q) use ($querySearch) {
                    $q->where('name', 'LIKE', '%' . $querySearch . '%')
                      ->orWhere('description', 'LIKE', '%' . $querySearch . '%');
                });
            }

            $sort = $this->handleSortOption($sortOption);
            $query->orderBy($sort['field'], $sort['order']);

            $amenities = $query->paginate($perPage);

            return ['data' => $amenities];
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

    public function getAllAmenities(string $querySearch, string $sortOption, string $perPage)
    {
        return $this->fetchAmenities(false, $querySearch, $sortOption, $perPage);
    }

    public function getAmenity(string $id)
    {
        try {
            $amenity = Amenity::findOrFail($id);
            return ['data' => $amenity];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function create(array $data)
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

    public function update(string $id, array $data)
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

    public function destroy(string $id)
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

    public function getTrashedAmenities(string $querySearch, string $sortOption, string $perPage)
    {
        return $this->fetchAmenities(true, $querySearch, $sortOption, $perPage);
    }

    public function restore(string $id)
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

    public function forceDelete(string $id)
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
