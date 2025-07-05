<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Apis\StoreBookingRequest;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['sort']);
            $bookings = $this->bookingService->getBookings($filters);
            return response()->json([
                'data' => $bookings
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi lấy danh sách đặt phòng. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function store(StoreBookingRequest $request)
    {
        try {
            $validated = $request->validated();
            $validated['user_id'] = Auth::id();

            $booking = $this->bookingService->createBooking($validated);
            return response()->json([
                'message' => 'Đặt phòng thành công',
                'data' => $booking
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi đặt phòng: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            $booking = $this->bookingService->rejectBooking($id);
            return response()->json([
                'message' => 'Hủy đặt phòng thành công',
                'data' => $booking
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Không tìm thấy đặt phòng.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi hủy đặt phòng. Vui lòng thử lại.'
            ], 500);
        }
    }
}
