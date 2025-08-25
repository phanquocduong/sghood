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

/**
 * Controller xử lý các yêu cầu API liên quan đến đặt phòng.
 */
class BookingController extends Controller
{
    protected $bookingService;

    /**
     * Khởi tạo controller với BookingService.
     *
     * @param BookingService $bookingService Dịch vụ xử lý logic đặt phòng
     */
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Lấy danh sách đặt phòng của người dùng.
     *
     * @param Request $request Yêu cầu HTTP chứa các bộ lọc
     * @return \Illuminate\Http\JsonResponse Phản hồi JSON chứa danh sách đặt phòng
     */
    public function index(Request $request)
    {
        try {
            // Lấy các bộ lọc từ request (sắp xếp, trạng thái)
            $filters = $request->only(['sort', 'status']);
            // Gọi dịch vụ để lấy danh sách đặt phòng
            $bookings = $this->bookingService->getBookings($filters);
            // Trả về phản hồi JSON với danh sách đặt phòng
            return response()->json(['data' => $bookings], 200);
        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Đã có lỗi xảy ra khi lấy danh sách đặt phòng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi lấy danh sách đặt phòng. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Tạo mới một đặt phòng.
     *
     * @param StoreBookingRequest $request Yêu cầu chứa dữ liệu đã xác thực
     * @return \Illuminate\Http\JsonResponse Phản hồi JSON với thông tin đặt phòng
     */
    public function store(StoreBookingRequest $request)
    {
        try {
            // Lấy dữ liệu đã xác thực từ request
            $validated = $request->validated();
            // Thêm user_id của người dùng hiện tại vào dữ liệu
            $validated['user_id'] = Auth::id();

            // Gọi dịch vụ để tạo đặt phòng
            $booking = $this->bookingService->createBooking($validated);
            // Trả về phản hồi JSON với thông báo thành công và dữ liệu đặt phòng
            return response()->json([
                'message' => 'Đặt phòng thành công',
                'data' => $booking
            ], 201);
        } catch (HttpResponseException $e) {
            // Ném lại ngoại lệ HTTP nếu có (ví dụ: lỗi xác thực)
            throw $e;
        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ hệ thống
            Log::error('Lỗi hệ thống khi đặt phòng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi đặt phòng. Vui lòng thử lại sau.'], 500);
        }
    }

    /**
     * Hủy một đặt phòng theo ID.
     *
     * @param int $id ID của đặt phòng cần hủy
     * @return \Illuminate\Http\JsonResponse Phản hồi JSON với thông tin đặt phòng đã hủy
     */
    public function cancel($id)
    {
        try {
            // Gọi dịch vụ để hủy đặt phòng
            $booking = $this->bookingService->cancelBooking($id);
            // Trả về phản hồi JSON với thông báo thành công và dữ liệu đặt phòng
            return response()->json([
                'message' => 'Hủy đặt phòng thành công',
                'data' => $booking
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Trả về lỗi 404 nếu không tìm thấy đặt phòng
            return response()->json(['error' => 'Không tìm thấy đặt phòng.'], 404);
        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ hệ thống
            Log::error('Đã có lỗi xảy ra khi hủy đặt phòng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi hủy đặt phòng. Vui lòng thử lại.'], 500);
        }
    }
}
