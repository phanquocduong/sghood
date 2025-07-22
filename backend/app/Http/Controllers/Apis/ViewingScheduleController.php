<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\ViewingScheduleService;
use App\Http\Requests\Apis\StoreScheduleRequest;
use App\Http\Requests\Apis\UpdateScheduleRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ViewingScheduleController extends Controller
{
    protected $viewingScheduleService;

    public function __construct(ViewingScheduleService $viewingScheduleService)
    {
        $this->viewingScheduleService = $viewingScheduleService;
    }

    protected function jsonResponse($data, int $status = 200, array $headers = []): JsonResponse
    {
        return response()->json($data, $status, $headers);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['sort', 'status']);
            $schedules = $this->viewingScheduleService->getSchedules($filters);
            return response()->json(['data' => $schedules], 200);
        } catch (\Exception $e) {
            Log::error('Đã có lỗi xảy ra khi lấy danh sách lịch xem nhà trọ: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi lấy danh sách lịch xem nhà trọ.'], 500);
        }
    }

    public function store(StoreScheduleRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['user_id'] = Auth::id();
            $schedule = $this->viewingScheduleService->createSchedule($validated);
            return response()->json([
                'message' => 'Đặt lịch xem nhà trọ thành công',
                'data' => $schedule,
            ], 201);
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Đã có lỗi xảy ra khi đặt lịch xem nhà trọ: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi đặt lịch xem nhà trọ.'], 500);
        }
    }

    public function update(UpdateScheduleRequest $request, $id): JsonResponse
    {
        try {
            $schedule = $this->viewingScheduleService->updateSchedule($id, $request->validated());
            return response()->json([
                'message' => 'Cập nhật lịch xem nhà trọ thành công',
                'data' => $schedule,
            ]);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Không tìm thấy lịch xem nhà trọ.'], 404);
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Đã có lỗi xảy ra khi cập nhật lịch xem nhà trọ: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi cập nhật lịch xem nhà trọ.'], 500);
        }
    }

    public function cancel($id): JsonResponse
    {
        try {
            $schedule = $this->viewingScheduleService->cancelSchedule($id);
            return response()->json([
                'message' => 'Hủy lịch xem nhà trọ thành công',
                'data' => $schedule,
            ], 200);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Không tìm thấy lịch xem nhà trọ.'], 404);
        } catch (\Exception $e) {
            Log::error('Đã có lỗi xảy ra khi hủy lịch xem nhà trọ: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi hủy lịch xem nhà trọ.'], 500);
        }
    }
}

