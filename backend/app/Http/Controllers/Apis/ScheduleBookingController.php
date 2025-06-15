<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\ScheduleBookingService;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Apis\StoreScheduleRequest;
use App\Http\Requests\Apis\StoreBookingRequest;
use Illuminate\Support\Facades\Auth;

class ScheduleBookingController extends Controller
{
    protected $scheduleBookingService;

    public function __construct(ScheduleBookingService $scheduleBookingService)
    {
        $this->scheduleBookingService = $scheduleBookingService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['sort', 'type']);
            $items = $this->scheduleBookingService->getItems($filters);
            return response()->json([
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi lấy danh sách. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $type = $request->input('type');
            $data = $request->all();

            // Xác thực dữ liệu dựa trên type
            if ($type === 'schedule') {
                $validator = Validator::make($data, (new StoreScheduleRequest())->rules());
            } elseif ($type === 'booking') {
                $validator = Validator::make($data, (new StoreBookingRequest())->rules());
            } else {
                return response()->json([
                    'error' => 'Loại không hợp lệ. Vui lòng chọn "schedule" hoặc "booking".'
                ], 400);
            }

            // Kiểm tra validation
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dữ liệu không hợp lệ.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Lấy dữ liệu đã xác thực
            $validated = $validator->validated();
            $validated['user_id'] = Auth::id(); // Thêm user_id từ auth

            $item = $this->scheduleBookingService->createItem($validated, $type);
            return response()->json([
                'message' => $type === 'schedule' ? 'Đặt lịch thành công' : 'Đặt phòng thành công',
                'data' => $item
            ], 201);
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi ' . ($type === 'schedule' ? 'đặt lịch' : 'đặt phòng') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject($id, $type)
    {
        try {
            $item = $this->scheduleBookingService->rejectItem($id, $type);
            return response()->json([
                'message' => 'Hủy ' . ($type === 'schedule' ? 'lịch xem phòng' : 'đặt phòng') . ' thành công',
                'data' => $item
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Không tìm thấy ' . ($type === 'schedule' ? 'lịch xem phòng' : 'đặt phòng') . '.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi hủy. Vui lòng thử lại.'
            ], 500);
        }
    }
}
