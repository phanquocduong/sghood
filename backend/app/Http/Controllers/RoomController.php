<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    protected RoomService $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index(Request $request): JsonResponse
    {
        $querySearch = $request->query('query', '');
        $status = $request->query('status', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 25);

        $result = $this->roomService->getAllRooms($querySearch, $status, $sortOption, $perPage);

        return $this->handleResponse($result);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->roomService->getRoom($id);

        return $this->handleResponse($result);
    }

    public function store(StoreRoomRequest $request): JsonResponse
    {
        $result = $this->roomService->create($request->validated(), $request->file('images'));

        return $this->handleResponse(
            $result,
            'Phòng đã được tạo thành công!',
            201
        );
    }

    public function update(StoreRoomRequest $request, string $id): JsonResponse
    {
        $result = $this->roomService->update($id, $request->validated(), $request->file('images'));

        return $this->handleResponse(
            $result,
            'Phòng đã được cập nhật thành công!'
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $result = $this->roomService->destroy($id);

        return $this->handleResponse(
            $result,
            'Phòng đã được xoá thành công!'
        );
    }

    public function trash(Request $request): JsonResponse
    {
        $querySearch = $request->query('query', '');
        $status = $request->query('status', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 25);

        $result = $this->roomService->getTrashedRooms($querySearch, $status, $sortOption, $perPage);

        return $this->handleResponse($result);
    }

    public function restore(string $id): JsonResponse
    {
        $result = $this->roomService->restore($id);

        return $this->handleResponse(
            $result,
            'Phòng đã được khôi phục thành công!'
        );
    }

    public function forceDelete(string $id): JsonResponse
    {
        $result = $this->roomService->forceDelete($id);

        return $this->handleResponse(
            $result,
            'Phòng đã được xóa vĩnh viễn!'
        );
    }

    private function handleResponse(array $result, string $successMessage = '', int $successStatus = 200): JsonResponse
    {
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        $response = ['data' => $result['data']];
        if ($successMessage) {
            $response['message'] = $successMessage;
        }

        if (isset($result['warnings'])) {
            $response['warnings'] = $result['warnings'];
        }

        return response()->json($response, $successStatus);
    }
}
