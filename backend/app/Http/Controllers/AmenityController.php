<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAmenityRequest;
use App\Services\AmenityService;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    protected $amenityService;

    public function __construct(AmenityService $amenityService)
    {
        $this->amenityService = $amenityService;
    }

    public function index(Request $request)
    {
        $querySearch = $request->get('query', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->amenityService->getAllAmenities($querySearch, $sortOption, $perPage);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function show(string $id)
    {
        $result = $this->amenityService->getAmenity($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function store(StoreAmenityRequest $request)
    {
        $result = $this->amenityService->create($request->validated());
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json([
            'message' => 'Tiện nghi đã được tạo thành công!',
            'data' => $result['data']
        ], 201);
    }

    public function update(StoreAmenityRequest $request, string $id)
    {
        $result = $this->amenityService->update($id, $request->validated());
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json([
            'message' => 'Tiện nghi đã được cập nhật thành công!',
            'data' => $result['data']
        ], 200);
    }

    public function destroy(string $id)
    {
        $result = $this->amenityService->destroy($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['message' => 'Tiện nghi được xoá thành công'], 200);
    }

    public function trash(Request $request)
    {
        $querySearch = $request->get('query', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->amenityService->getTrashedAmenities($querySearch, $sortOption, $perPage);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function restore(string $id)
    {
        $result = $this->amenityService->restore($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json([
            'message' => 'Tiện nghi đã được khôi phục thành công!',
            'data' => $result['data']
        ], 200);
    }

    public function forceDestroy(string $id)
    {
        $result = $this->amenityService->forceDelete($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['message' => 'Tiện nghi đã được xóa vĩnh viễn!'], 200);
    }
}
