<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\ViewingScheduleService;
use Illuminate\Http\Request;
use App\Http\Requests\Apis\StoreScheduleRequest;
use Illuminate\Support\Facades\Auth;

class ViewingScheduleController extends Controller
{
    protected $viewingScheduleService;

    public function __construct(ViewingScheduleService $viewingScheduleService)
    {
        $this->viewingScheduleService = $viewingScheduleService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['sort']);
            $schedules = $this->viewingScheduleService->getSchedules($filters);
            return response()->json([
                'data' => $schedules
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi lấy danh sách lịch xem nhà trọ. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function store(StoreScheduleRequest $request)
    {
        try {
            $validated = $request->validated();
            $validated['user_id'] = Auth::id();

            $schedule = $this->viewingScheduleService->createSchedule($validated);
            return response()->json([
                'message' => 'Đặt lịch xem nhà trọ thành công',
                'data' => $schedule
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi đặt lịch xem nhà trọ: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            $schedule = $this->viewingScheduleService->rejectSchedule($id);
            return response()->json([
                'message' => 'Hủy lịch xem nhà trọ thành công',
                'data' => $schedule
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Không tìm thấy lịch xem nhà trọ.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi hủy lịch xem nhà trọ. Vui lòng thử lại.'
            ], 500);
        }
    }
}
