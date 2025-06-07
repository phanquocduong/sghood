<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\RoomService;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    /**
     * Lấy chi tiết phòng trọ theo slug nhà trọ và ID phòng
     */
    public function show($slug, $roomId)
    {
        try {
            $roomData = $this->roomService->getRoomDetail($slug, $roomId);
            return response()->json([
                'success' => true,
                'data' => $roomData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phòng hoặc nhà trọ',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
