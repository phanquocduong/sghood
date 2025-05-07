<?php

namespace App\Services;

use App\Models\Amenity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AmenityService
{
    public function getAllAmenities($querySearch = '', $sortOption = 'name_asc', $perPage = 10)
    {
        $query = Amenity::query();

        if ($querySearch != '') {
            $query->where(function ($q) use ($querySearch) {
                $q->where('name', 'LIKE', '%' . $querySearch . '%')
                  ->orWhere('description', 'LIKE', '%' . $querySearch . '%');
            });
        }

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

        $query->orderBy($sortField, $sortOrder);

        $amenities = $query->paginate($perPage);
        return $amenities;
    }

    public function getAmenity($id, $withTrashed = false)
    {
        $query = Amenity::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        $amenity = $query->findOrFail($id);
        return $amenity;
    }

    public function create($validatedRequest)
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::create($validatedRequest);

            DB::commit();
            return $amenity;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tạo tiện nghi thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, $validatedRequest)
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::findOrFail($id);
            $amenity->update($validatedRequest);

            DB::commit();
            return $amenity;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cập nhật tiện nghi thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::findOrFail($id);
            $amenity->delete();

            DB::commit();
            return $amenity;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xóa tiện nghi thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    public function forceDelete($id)
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::withTrashed()->findOrFail($id);
            if (!$amenity->trashed()) {
                throw new \Exception('Tiện nghi phải bị xóa mềm trước khi xóa vĩnh viễn.');
            }
            $amenity->forceDelete();

            DB::commit();
            return $amenity;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xóa vĩnh viễn tiện nghi thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::withTrashed()->findOrFail($id);
            $amenity->restore();

            DB::commit();
            return $amenity;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Khôi phục tiện nghi thất bại: ' . $e->getMessage());
            throw $e;
        }
    }
}
