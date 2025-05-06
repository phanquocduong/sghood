<?php

namespace App\Services;

use App\Models\Rooms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomService
{
    public function getAllRooms($querySearch = '', $status = '', $sortOption = 'name_asc', $perPage = 10)
    {
        $query = Rooms::query()->with('motel');

        if ($querySearch != '') {
            $query->where('name', 'LIKE', '%' . $querySearch . '%');
        }

        if ($status != '') {
            $query->where('status', $status);
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

        $rooms = $query->paginate($perPage);
        return $rooms;
    }

    public function getRoom($id, $withTrashed = false)
    {
        $query = Rooms::query()->with(['motel', 'images']);

        if ($withTrashed) {
            $query->withTrashed();
        }
        $room = $query->findOrFail($id);
        return $room;
    }

    public function create($validatedRequest)
    {
        DB::beginTransaction();
        try {
            $room = Rooms::create($validatedRequest);

            DB::commit();
            return $room;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tạo phòng thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, $validatedRequest)
    {
        DB::beginTransaction();
        try {
            $room = Rooms::findOrFail($id);
            $room->update($validatedRequest);

            DB::commit();
            return $room;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cập nhật phòng thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $room = Rooms::findOrFail($id);
            $room->delete();

            DB::commit();
            return $room;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xóa phòng thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    public function forceDelete($id)
    {
        DB::beginTransaction();
        try {
            $room = Rooms::withTrashed()->findOrFail($id);
            if (!$room->trashed()) {
                throw new \Exception('Phòng phải bị xóa mềm trước khi xóa vĩnh viễn.');
            }
            $room->forceDelete();

            DB::commit();
            return $room;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xóa vĩnh viễn phòng thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $room = Rooms::withTrashed()->findOrFail($id);
            $room->restore();

            DB::commit();
            return $room;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Khôi phục phòng thất bại: ' . $e->getMessage());
            throw $e;
        }
    }
}
