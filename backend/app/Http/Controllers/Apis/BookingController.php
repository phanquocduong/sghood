<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\StoreBookingRequest;
use App\Services\Apis\BookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->createBooking($request->validated());
            return response()->json([
                'message' => 'Đặt phòng thành công',
                'data' => $booking
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi đặt phòng: ' . $e->getMessage()
            ], 500);
        }
    }
}
