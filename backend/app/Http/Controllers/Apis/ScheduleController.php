<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\StoreScheduleRequest;
use App\Services\Apis\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Exception;

class ScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function store(StoreScheduleRequest $request)
    {
        try {
            $schedule = $this->scheduleService->createSchedule($request->validated());
            return response()->json([
                'message' => 'Đặt lịch thành công',
                'data' => $schedule
            ], 201);
        } catch (HttpResponseException $e) {
            // Lỗi từ ScheduleService (ví dụ: lịch trùng)
            throw $e; // Chuyển tiếp ngoại lệ để Laravel xử lý, giữ nguyên phản hồi JSON từ ScheduleService
        } catch (Exception $e) {
            // Các lỗi khác (ví dụ: lỗi cơ sở dữ liệu)
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi đặt lịch. Vui lòng thử lại.'
            ], 500);
        }
    }
}
