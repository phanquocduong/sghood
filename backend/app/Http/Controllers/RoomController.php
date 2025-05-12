<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
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
        $querySearch = $request->get('query', '');
        $status = $request->get('status', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->roomService->getAllRooms($querySearch, $status, $sortOption, $perPage);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function show(string $id)
    {
        $result = $this->roomService->getRoom($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function store(StoreRoomRequest $request)
    {
        $result = $this->roomService->create($request->validated(), $request->file('images'));
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        $response = [
            'message' => 'Phòng đã được tạo thành công!',
            'data' => $result['data']
        ];

        if (isset($result['warnings'])) {
            $response['warnings'] = $result['warnings'];
            $response['message'] .= ' Tuy nhiên, một số ảnh không được upload thành công.';
        }

        return response()->json($response, 201);
    }

    public function update(StoreRoomRequest $request, string $id)
    {
        $result = $this->roomService->update($id, $request->validated(), $request->file('images'));

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        $response = [
            'message' => 'Phòng đã được cập nhật thành công!',
            'data' => $result['data']
        ];

        if (isset($result['warnings'])) {
            $response['warnings'] = $result['warnings'];
            $response['message'] .= ' Tuy nhiên, một số ảnh không được upload thành công.';
        }

        return response()->json($response, 200);
    }

    public function destroy(string $id)
    {
        $result = $this->roomService->destroy($id);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json(['message' => 'Phòng được xoá thành công'], 200);
    }

    public function trash(Request $request)
    {
        $querySearch = $request->get('query', '');
        $status = $request->get('status', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->roomService->getTrashedRooms($querySearch, $status, $sortOption, $perPage);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function restore(string $id)
    {
        $result = $this->roomService->restore($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json([
            'message' => 'Phòng đã được khôi phục thành công!',
            'data' => $result['data']
        ], 200);
    }

    public function forceDelete(string $id)
    {
        $result = $this->roomService->forceDelete($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['message' => 'Phòng đã được xóa vĩnh viễn!'], 200);
    }
}
