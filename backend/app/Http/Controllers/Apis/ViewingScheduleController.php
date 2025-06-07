<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\StoreViewingScheduleRequest;
use App\Services\Apis\ViewingScheduleService;
use Illuminate\Http\Request;

class ViewingScheduleController extends Controller
{
    protected $viewingScheduleService;

    public function __construct(ViewingScheduleService $viewingScheduleService)
    {
        $this->viewingScheduleService = $viewingScheduleService;
    }

    public function store(StoreViewingScheduleRequest $request)
    {
        $schedule = $this->viewingScheduleService->createSchedule($request->validated());
        return response()->json(['message' => 'Đặt lịch thành công', 'data' => $schedule], 201);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['sort', 'status']);
        $bookings = $this->viewingScheduleService->getBookings($filters);
        return response()->json(['data' => $bookings], 200);
    }

    public function reject($id)
    {
        $booking = $this->viewingScheduleService->rejectBooking($id);
        return response()->json(['data' => $booking], 200);
    }
}
