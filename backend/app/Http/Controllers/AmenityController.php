<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAmenityRequest;
use App\Http\Requests\UpdateAmenityRequest;
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
        try {
            $querySearch = $request->get('query');
            $sortOption = $request->get('sortOption');
            $perPage = $request->get('per_page', 10);

            $amenities = $this->amenityService->getAllAmenities(querySearch: $querySearch, sortOption: $sortOption, perPage: $perPage);

            return response()->json([
                'data' => $amenities->items(),
                'current_page' => $amenities->currentPage(),
                'per_page' => $amenities->perPage(),
                'total' => $amenities->total(),
                'last_page' => $amenities->lastPage(),
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
            $amenity = $this->amenityService->getAmenity($id, $withTrashed);

            return response()->json([
                'data' => $amenity
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã có lỗi xảy ra',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreAmenityRequest $request)
    {
        try {
            $validatedRequest = $request->validated();
            $amenity = $this->amenityService->create($validatedRequest);

            return response()->json([
                'message' => 'Tạo tiện nghi thành công',
                'data' => $amenity,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateAmenityRequest $request, string $id)
    {
        try {
            $validatedRequest = $request->validated();
            $amenity = $this->amenityService->update($id, $validatedRequest);

            return response()->json([
                'message' => 'Cập nhật tiện nghi thành công',
                'data' => $amenity,
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
            $amenity = $this->amenityService->destroy($id);

            if ($amenity) {
                return response()->json([
                    'message' => 'Xóa tiện nghi thành công',
                    'data' => ['id' => $id],
                ], 200);
            }

            return response()->json([
                'message' => 'Tiện nghi không tìm thấy hoặc xóa thất bại',
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
            $amenity = $this->amenityService->forceDelete($id);

            if ($amenity) {
                return response()->json([
                    'message' => 'Xóa vĩnh viễn tiện nghi thành công',
                    'data' => ['id' => $id],
                ], 200);
            }

            return response()->json([
                'message' => 'Tiện nghi không tìm thấy hoặc chưa bị xóa mềm',
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
            $amenity = $this->amenityService->restore($id);

            if ($amenity) {
                return response()->json([
                    'message' => 'Khôi phục tiện nghi thành công',
                    'data' => $amenity,
                ], 200);
            }

            return response()->json([
                'message' => 'Tiện nghi không tìm thấy hoặc chưa bị xóa mềm',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
