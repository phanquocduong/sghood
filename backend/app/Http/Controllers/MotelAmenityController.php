<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\MotelAmenityService;
use Illuminate\Http\Request;

class MotelAmenityController extends Controller
{
    protected $motelAmenityService;

    public function __construct(MotelAmenityService $motelAmenityService)
    {
        $this->motelAmenityService = $motelAmenityService;
    }

    public function index(string $motelId)
    {
        try {
            $amenities = $this->motelAmenityService->getAmenitiesForMotel($motelId);

            return response()->json([
                'data' => $amenities,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã có lỗi xảy ra',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request, string $motelId)
    {
        try {
            $validatedRequest = $request->validate([
                'amenity_id' => 'required|exists:amenities,id',
            ]);

            $result = $this->motelAmenityService->assignAmenityToMotel($motelId, $validatedRequest['amenity_id']);

            return response()->json([
                'message' => 'Gán tiện nghi cho motel thành công',
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $motelId, string $amenityId)
    {
        try {
            $result = $this->motelAmenityService->removeAmenityFromMotel($motelId, $amenityId);

            if ($result) {
                return response()->json([
                    'message' => 'Xóa tiện nghi khỏi motel thành công',
                    'data' => [
                        'motel_id' => $motelId,
                        'amenity_id' => $amenityId,
                    ],
                ], 200);
            }

            return response()->json([
                'message' => 'Tiện nghi không tồn tại trong motel hoặc xóa thất bại',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
