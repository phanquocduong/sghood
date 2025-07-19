<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\BookingService;
use App\Http\Requests\Apis\StoreBookingRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            $filters = $request->only(['sort', 'status']);
            $bookings = $this->bookingService->getBookings($filters);
            return response()->json(['data' => $bookings], 200);
        } catch (\Exception $e) {
            Log::error('Đã có lỗi xảy ra khi lấy danh sách đặt phòng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi lấy danh sách đặt phòng. Vui lòng thử lại.'], 500);
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
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Lỗi hệ thống khi đặt phòng' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi đặt phòng. Vui lòng thử lại sau.'], 500);
        }
    }

    public function cancel($id)
    {
        try {
            $booking = $this->bookingService->cancelBooking($id);
            return response()->json([
                'message' => 'Hủy đặt phòng thành công',
                'data' => $booking
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy đặt phòng.'], 404);
        } catch (\Exception $e) {
            Log::error('Đã có lỗi xảy ra khi hủy đặt phòng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi hủy đặt phòng. Vui lòng thử lại.'], 500);
        }
    }
}
