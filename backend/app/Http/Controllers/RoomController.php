<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Services\RoomService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index(Request $request)
    {
        try {
            $querySearch = $request->get('query');
            $status = $request->get('status');
            $sortOption = $request->get('sortOption');
            $perPage = $request->get('per_page', 10);

            $rooms = $this->roomService->getAllRooms(querySearch: $querySearch, status: $status, sortOption: $sortOption, perPage: $perPage);

            return response()->json([
                'data' => $rooms->items(),
                'current_page' => $rooms->currentPage(),
                'per_page' => $rooms->perPage(),
                'total' => $rooms->total(),
                'last_page' => $rooms->lastPage(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã có lỗi xảy ra',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $withTrashed = request()->query('with_trashed', false);
            $room = $this->roomService->getRoom($id, $withTrashed);

            return response()->json([
                'data' => $room
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã có lỗi xảy ra',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreRoomRequest $request)
    {
        try {
            $validatedRequest = $request->validated();
            $room = $this->roomService->create($validatedRequest);

            return response()->json([
                'message' => 'Tạo phòng thành công',
                'data' => $room,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateRoomRequest $request, string $id)
    {
        try {
            $validatedRequest = $request->validated();
            $room = $this->roomService->update($id, $validatedRequest);

            return response()->json([
                'message' => 'Cập nhật phòng thành công',
                'data' => $room,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $room = $this->roomService->destroy($id);

            if ($room) {
                return response()->json([
                    'message' => 'Xóa phòng thành công',
                    'data' => ['id' => $id],
                ], 200);
            }

            return response()->json([
                'message' => 'Phòng không tìm thấy hoặc xóa thất bại',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function forceDelete(string $id)
    {
        try {
            $room = $this->roomService->forceDelete($id);

            if ($room) {
                return response()->json([
                    'message' => 'Xóa vĩnh viễn phòng thành công',
                    'data' => ['id' => $id],
                ], 200);
            }

            return response()->json([
                'message' => 'Phòng không tìm thấy hoặc chưa bị xóa mềm',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function restore(string $id)
    {
        try {
            $room = $this->roomService->restore($id);

            if ($room) {
                return response()->json([
                    'message' => 'Khôi phục phòng thành công',
                    'data' => $room,
                ], 200);
            }

            return response()->json([
                'message' => 'Phòng không tìm thấy hoặc chưa bị xóa mềm',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
