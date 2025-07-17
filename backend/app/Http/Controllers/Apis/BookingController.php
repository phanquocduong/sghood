<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\BookingService;
use Illuminate\Http\Request;
use App\Http\Requests\Apis\StoreBookingRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
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
        } catch (HttpResponseException $e) {
            // Lỗi do validation hoặc logic nghiệp vụ
            Log::error('Lỗi khi đặt phòng', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            throw $e; // Ném lại để trả về lỗi 422 cho client
        } catch (\Exception $e) {
            // Các lỗi khác (cơ sở dữ liệu, cấu hình, v.v.)
            Log::error('Lỗi hệ thống khi đặt phòng', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi đặt phòng. Vui lòng thử lại sau.'
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
        } catch (ModelNotFoundException $e) {
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
