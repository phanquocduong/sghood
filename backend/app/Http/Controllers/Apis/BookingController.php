<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\StoreBookingRequest;
use App\Services\Apis\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function index(Request $request)
    {
        $filters = $request->only(['sort', 'status']);
        $bookings = $this->bookingService->getBookings($filters);
        return response()->json(['data' => $bookings], 200);
    }
}
