<?php
namespace App\Http\Controllers;

use App\Services\MotelService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\MotelRequest;

class MotelController extends Controller
{
    protected $motelService;

    public function __construct(MotelService $motelService) {
        $this->motelService = $motelService;
    }

   public function index(Request $request): JsonResponse {
        $querySearch = $request->get('query', '');
        $status = $request->get('status', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->motelService->getAvailableMotels($querySearch, $status, $sortOption, $perPage);
        return $this->handleResponse($result);
    }

    public function show(int $id): JsonResponse {
        $result = $this->motelService->getMotel($id);
        return $this->handleResponse($result);
    }

    public function store(MotelRequest $request): JsonResponse {
        $result = $this->motelService->createMotel($request->validated(), $request->file('images'));

        return $this->handleResponse(
            $result,
            'Nhà trọ đã được tạo thành công!',
            201
        );
    }

    public function update(MotelRequest $request, int $id): JsonResponse {
        $imageFiles = [];
        if ($request->hasFile('images')) {
            $imageFiles = $request->file('images');
        }
        dd($request->validated());
        $result = $this->motelService->updateMotel($id, $request->validated(), $imageFiles);

        return $this->handleResponse(
            $result,
            'Nhà trọ đã được cập nhật thành công!',
        );
    }

    public function destroy(int $id): JsonResponse {
        $result = $this->motelService->deleteMotel($id);

        return $this->handleResponse(
            $result,
            'Nhà trọ đã được xoá thành công!'
        );
    }

    public function trash(Request $request): JsonResponse {
        $querySearch = $request->get('query', '');
        $status = $request->get('status', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->motelService->getTrashedMotels($querySearch, $status, $sortOption, $perPage);
        return $this->handleResponse($result);
    }

    public function showTrashed(int $id): JsonResponse {
        $result = $this->motelService->getMotel($id, true);
        return $this->handleResponse($result);
    }

    public function restore(int $id): JsonResponse {
        $result = $this->motelService->restoreMotel($id);
        return $this->handleResponse(
            $result,
            'Nhà trọ đã được khôi phục thành công!'
        );
    }

    public function forceDestroy(int $id): JsonResponse {
        $result = $this->motelService->forceDeleteMotel($id);
        return $this->handleResponse(
            $result,
            'Nhà trọ đã được xóa vĩnh viễn!'
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
