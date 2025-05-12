<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\DistrictRequest;
use App\Services\DistrictService;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    protected $districtService;

    public function __construct(DistrictService $districtService)
    {
        $this->districtService = $districtService;
    }

    public function index(Request $request) {
        $querySearch = $request->get('query', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->districtService->getAllDistricts($querySearch, $sortOption, $perPage);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function show(int $id) {
        $result = $this->districtService->getDistrictById($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function store(DistrictRequest $request) {
        $result = $this->districtService->createDistrict($request->validated(), $request->file('image'));
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json([
            'message' => 'Khu vực đã được tạo thành công!',
            'data' => $result['data']
        ], 201);
    }

    public function update(DistrictRequest $request, int $id)
    {
        $imageFile = null;
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
        }
        $result = $this->districtService->updateDistrict($id, $request->validated(), $imageFile);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json([
            'message' => 'Khu vực đã được cập nhật thành công!',
            'data' => $result['data']
        ], 200);
    }

    public function destroy(int $id) {
        $result = $this->districtService->deleteDistrict($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['message' => 'Khu vực đã được xoá thành công!'], 200);
    }

    public function trash(Request $request) {
        $querySearch = $request->get('query', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->districtService->getTrashedDistricts($querySearch, $sortOption, $perPage);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function restore(int $id) {
        $result = $this->districtService->restoreDistrict($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json([
            'message' => 'Khu vực đã được khôi phục thành công!',
            'data' => $result['data']
        ], 200);
    }

    public function forceDestroy(int $id) {
        $result = $this->districtService->forceDeleteDistrict($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['message' => 'Khu vực đã được xóa vĩnh viễn!'], 200);
    }
}
