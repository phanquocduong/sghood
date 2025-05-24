<?php
namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService) {
        $this->roomService = $roomService;
    }

    public function index(Request $request): JsonResponse {
        $querySearch = $request->query('query', '');
        $status = $request->query('status', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 25);

        $result = $this->roomService->getAllRooms($querySearch, $status, $sortOption, $perPage);

        return $this->handleResponse($result);
    }

    public function show(int $id): JsonResponse {
        $result = $this->roomService->getRoom($id);
        return $this->handleResponse($result);
    }

    public function store(RoomRequest $request): JsonResponse {
        $result = $this->roomService->createRoom($request->validated(), $request->file('images'));

        return $this->handleResponse(
            $result,
            'Phòng đã được tạo thành công!',
            201
        );
    }

    public function update(RoomRequest $request, int $id): JsonResponse {
        $imageFiles = [];
        if ($request->hasFile('images')) {
            $imageFiles = $request->file('images');
        }
        $result = $this->roomService->updateMotel($id, $request->validated(), $imageFiles);

        return $this->handleResponse(
            $result,
            'Phòng đã được cập nhật thành công!'
        );
    }

    public function destroy(int $id): JsonResponse {
        $result = $this->roomService->deleteRoom($id);

        return $this->handleResponse(
            $result,
            'Phòng đã được xoá thành công!'
        );
    }

    public function trash(Request $request): JsonResponse {
        $querySearch = $request->query('query', '');
        $status = $request->query('status', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 25);

        $result = $this->roomService->getTrashedRooms($querySearch, $status, $sortOption, $perPage);

        return $this->handleResponse($result);
    }

    public function showTrashed(int $id): JsonResponse {
        $result = $this->roomService->getRoom($id, true);
        return $this->handleResponse($result);
    }

    public function restore(string $id): JsonResponse {
        $result = $this->roomService->restoreRoom($id);

        return $this->handleResponse(
            $result,
            'Phòng đã được khôi phục thành công!'
        );
    }

    public function forceDelete(string $id): JsonResponse {
        $result = $this->roomService->forceDeleteRoom($id);

        return $this->handleResponse(
            $result,
            'Phòng đã được xóa vĩnh viễn!'
        );
    }

    private function handleResponse(array $result, string $successMessage = '', int $successStatus = 200): JsonResponse {
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        if (isset($result['data'])) {
            $response = ['data' => $result['data']];
        } else {
            $response = ['success' => $result['success']];
        }
        if ($successMessage) {
            $response['message'] = $successMessage;
        }

        if (isset($result['warnings'])) {
            $response['warnings'] = $result['warnings'];
            $response['message'] .= ' Tuy nhiên, một số ảnh không được upload thành công.';
        }

        return response()->json($response, $successStatus);
    }
}
