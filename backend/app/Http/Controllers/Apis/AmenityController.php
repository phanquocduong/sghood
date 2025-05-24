<?php
namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\AmenityRequest;
use App\Services\AmenityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    protected $amenityService;

    public function __construct(AmenityService $amenityService) {
        $this->amenityService = $amenityService;
    }

    public function index(Request $request): JsonResponse {
        $querySearch = $request->query('query', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 25);

        $result = $this->amenityService->getAvailableAmenities($querySearch, $sortOption, $perPage);
        return $this->handleResponse($result);
    }

    public function show(string $id): JsonResponse {
        $result = $this->amenityService->getAmenity($id);
        return $this->handleResponse($result);
    }

    public function store(AmenityRequest $request): JsonResponse {
        $result = $this->amenityService->createAmenity($request->validated());

        return $this->handleResponse(
            $result,
            'Tiện nghi đã được tạo thành công!',
            201
        );
    }

    public function update(AmenityRequest $request, int $id): JsonResponse {
        $result = $this->amenityService->updateAmenity($id, $request->validated());

        return $this->handleResponse(
            $result,
            'Tiện nghi đã được cập nhật thành công!'
        );
    }

    public function destroy(string $id): JsonResponse {
        $result = $this->amenityService->deleteAmenity($id);

        return $this->handleResponse(
            $result,
            'Tiện nghi đã được xoá thành công!'
        );
    }

    public function trash(Request $request): JsonResponse {
        $querySearch = $request->query('query', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 25);

        $result = $this->amenityService->getTrashedAmenities($querySearch, $sortOption, $perPage);

        return $this->handleResponse($result);
    }

    public function restore(string $id): JsonResponse {
        $result = $this->amenityService->restoreAmenity($id);

        return $this->handleResponse(
            $result,
            'Tiện nghi đã được khôi phục thành công!'
        );
    }

    public function forceDelete(string $id): JsonResponse
    {
        $result = $this->amenityService->forceDeleteAmenity($id);

        return $this->handleResponse(
            $result,
            'Tiện nghi đã được xóa vĩnh viễn!'
        );
    }

    private function handleResponse(array $result, string $successMessage = '', int $successStatus = 200): JsonResponse
    {
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

        return response()->json($response, $successStatus);
    }
}
