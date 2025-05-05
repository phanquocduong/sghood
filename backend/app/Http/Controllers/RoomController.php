<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Services\RoomService;
use App\Models\Rooms;
use App\Http\Requests\UpdateRoomRequest;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index()
    {
        try {
            $rooms = Rooms::query()
                ->with('motel')
                ->paginate(10);

            $formattedRooms = $rooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'price' => $room->price,
                    'area' => $room->area,
                    'status' => $room->status,
                    'motel_id' => $room->motel_id,
                    'motel' => $room->motel ? [
                        'id' => $room->motel->id,
                    ] : null,
                    'created_at' => $room->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'data' => $formattedRooms,
                'current_page' => $rooms->currentPage(),
                'per_page' => $rooms->perPage(),
                'total' => $rooms->total(),
                'last_page' => $rooms->lastPage(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể lấy danh sách phòng.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function store(StoreRoomRequest $request)
    {
        try {
            $room = $this->roomService->createRoom($request->validated());

            return response()->json([
                'id' => $room->id,
                'name' => $room->name,
                'price' => $room->price,
                'area' => $room->area,
                'status' => $room->status,
                'motel_id' => $room->motel_id,
                'created_at' => $room->created_at->toDateTimeString(),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể tạo phòng.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function show($id)
    {
        $withTrashed = request()->query('with_trashed', false);

        $query = Rooms::query();

        if ($withTrashed) {
            $query = $query->withTrashed();
        }

        $room = $query->with('motel')->find($id);

        if (!$room) {
            return response()->json([
                'message' => 'Phòng không tồn tại.',
            ], 404);
        }

        return response()->json([
            'id' => $room->id,
            'name' => $room->name,
            'price' => $room->price,
            'area' => $room->area,
            'status' => $room->status,
            'motel_id' => $room->motel_id,
            'motel' => $room->motel ? [
                'id' => $room->motel->id,
            ] : null,
            'created_at' => $room->created_at->toDateTimeString(),
            'updated_at' => $room->updated_at->toDateTimeString(),
            'deleted_at' => $room->deleted_at ? $room->deleted_at->toDateTimeString() : null,
        ], 200);
    }

    public function update(UpdateRoomRequest $request, Rooms $room)
    {
        try {
            $validatedData = $request->validated();

            $room = $this->roomService->updateRoom($room, $validatedData);

            return response()->json([
                'id' => $room->id,
                'name' => $room->name,
                'price' => $room->price,
                'area' => $room->area,
                'status' => $room->status,
                'motel_id' => $room->motel_id,
                'created_at' => $room->created_at->toDateTimeString(),
                'updated_at' => $room->updated_at->toDateTimeString(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể cập nhật phòng.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function destroy(Rooms $room)
    {
        try {
            $room = $this->roomService->deleteRoom($room);

            return response()->json([
                'message' => 'Phòng đã được xóa thành công.',
                'id' => $room->id,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể xóa phòng.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function forceDelete($id)
    {
        $room = Rooms::withTrashed()->find($id);

        if (!$room) {
            return response()->json(['message' => 'Phòng không tồn tại.'], 404);
        }

        if (!$room->trashed()) {
            return response()->json(['message' => 'Phòng phải bị xóa mềm trước khi xóa vĩnh viễn.'], 400);
        }

        $room->forceDelete();

        return response()->json([
            'message' => 'Phòng đã bị xóa vĩnh viễn.',
            'id' => $id,
        ], 200);
    }

    public function restore($id)
    {
        $room = Rooms::withTrashed()->find($id);

        if (!$room) {
            return response()->json(['message' => 'Phòng không tồn tại.'], 404);
        }

        $room->restore();

        return response()->json(['message' => 'Phòng đã được khôi phục.', 'id' => $id], 200);
    }
}
