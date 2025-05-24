<?php
namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\DistrictRequest;
use App\Services\DistrictService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    protected $districtService;

    public function __construct(DistrictService $districtService)
    {
        $this->districtService = $districtService;
    }

    public function index(Request $request): JsonResponse {
        $querySearch = $request->get('query', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->districtService->getAvailableDistricts($querySearch, $sortOption, $perPage);
        return $this->handleResponse($result);
    }

    public function show(int $id): JsonResponse {
        $result = $this->districtService->getDistrict($id);
        return $this->handleResponse($result);
    }

    public function store(DistrictRequest $request): JsonResponse {
        $result = $this->districtService->createDistrict($request->validated(), $request->file('image'));
        return $this->handleResponse(
            $result,
            'Khu vực đã được tạo thành công!',
            201
        );
    }

    public function update(DistrictRequest $request, int $id): JsonResponse {
        $imageFile = null;
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
        }
        $result = $this->districtService->updateDistrict($id, $request->validated(), $imageFile);
        return $this->handleResponse(
            $result,
            'Khu vực đã được cập nhật thành công!',
        );
    }

    public function destroy(int $id): JsonResponse {
        $result = $this->districtService->deleteDistrict($id);

        return $this->handleResponse(
            $result,
            'Khu vực đã được xoá thành công!'
        );
    }

    public function trash(Request $request): JsonResponse {
        $querySearch = $request->get('query', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->districtService->getTrashedDistricts($querySearch, $sortOption, $perPage);
        return $this->handleResponse($result);
    }

    public function showTrashed(int $id): JsonResponse {
        $result = $this->districtService->getDistrict($id, true);
        return $this->handleResponse($result);
    }

    public function restore(int $id): JsonResponse {
        $result = $this->districtService->restoreDistrict($id);
        return $this->handleResponse(
            $result,
            'Khu vực đã được khôi phục thành công!'
        );
    }

    public function forceDestroy(int $id): JsonResponse {
        $result = $this->districtService->forceDeleteDistrict($id);
        return $this->handleResponse(
            $result,
            'Khu vực đã được xóa vĩnh viễn!'
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

        if (isset($result['warning'])) {
            $response['warning'] = $result['warnings'];
            $response['message'] .= ' Tuy nhiên, ảnh không được upload thành công.';
        }

        return response()->json($response, $successStatus);
    }
}
