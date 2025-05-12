<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAmenityRequest;
use App\Services\AmenityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    protected AmenityService $amenityService;

    public function __construct(AmenityService $amenityService)
    {
        $this->amenityService = $amenityService;
    }

    public function index(Request $request): JsonResponse
    {
        $querySearch = $request->query('query', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 25);

        $result = $this->amenityService->getAllAmenities($querySearch, $sortOption, $perPage);

        return $this->handleResponse($result);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->amenityService->getAmenity($id);

        return $this->handleResponse($result);
    }

    public function store(StoreAmenityRequest $request): JsonResponse
    {
        $result = $this->amenityService->create($request->validated());

        return $this->handleResponse(
            $result,
            'Tiện nghi đã được tạo thành công!',
            201
        );
    }

    public function update(StoreAmenityRequest $request, string $id): JsonResponse
    {
        $result = $this->amenityService->update($id, $request->validated());

        return $this->handleResponse(
            $result,
            'Tiện nghi đã được cập nhật thành công!'
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $result = $this->amenityService->destroy($id);

        return $this->handleResponse(
            $result,
            'Tiện nghi đã được xoá thành công!'
        );
    }

    public function trash(Request $request): JsonResponse
    {
        $querySearch = $request->query('query', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 25);

        $result = $this->amenityService->getTrashedAmenities($querySearch, $sortOption, $perPage);

        return $this->handleResponse($result);
    }

    public function restore(string $id): JsonResponse
    {
        $result = $this->amenityService->restore($id);

        return $this->handleResponse(
            $result,
            'Tiện nghi đã được khôi phục thành công!'
        );
    }

    public function forceDestroy(string $id): JsonResponse
    {
        $result = $this->amenityService->forceDelete($id);

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

        $response = ['data' => $result['data']];
        if ($successMessage) {
            $response['message'] = $successMessage;
        }

        return response()->json($response, $successStatus);
    }
}
