<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomAmenityRequest;
use App\Services\RoomAmenityService;
use Illuminate\Http\Request;

class RoomAmenityController extends Controller
{
    protected $roomAmenityService;

    public function __construct(RoomAmenityService $roomAmenityService)
    {
        $this->roomAmenityService = $roomAmenityService;
    }

    public function index(string $roomId)
    {
        try {
            $amenities = $this->roomAmenityService->getAmenitiesForRoom($roomId);

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

    public function store(RoomAmenityRequest $request, string $roomId)
    {
        try {
            $validatedData = $request->validated();
            $amenityId = $validatedData['amenity_id'];

            $result = $this->roomAmenityService->assignAmenityToRoom($roomId, $amenityId);

            return response()->json([
                'message' => 'Gán tiện nghi cho phòng thành công',
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $roomId, string $amenityId)
    {
        try {
            $result = $this->roomAmenityService->removeAmenityFromRoom($roomId, $amenityId);

            if ($result) {
                return response()->json([
                    'message' => 'Xóa tiện nghi khỏi phòng thành công',
                    'data' => [
                        'room_id' => $roomId,
                        'amenity_id' => $amenityId,
                    ],
                ], 200);
            }

            return response()->json([
                'message' => 'Tiện nghi không tồn tại trong phòng hoặc xóa thất bại',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
