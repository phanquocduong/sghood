<?php

namespace App\Http\Controllers;

use App\Services\ScheduleService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index(Request $request)
    {
        $sortBy = (string) $request->input('sort_by', '');
        $schedules = $this->scheduleService->getSchedules(
            (string) $request->get('querySearch', ''),
            (string) $request->get('status', ''),
            (int) $request->get('perPage', 10),
            $sortBy
        );

        if (isset($schedules['error'])) {
            return redirect()->route('schedules.index')->with('error', $schedules['error']);
        }

        return view('schedules.index', [
            'schedules' => $schedules['data'],
            'querySearch' => $request->get('querySearch', ''),
            'status' => $request->get('status', ''),
            'perPage' => $request->get('perPage', 10),
            'sortBy' => $sortBy,
            'sortOptions' => [
                '' => 'Mặc định',
                'created_at_desc' => 'Mới nhất',
                'created_at_asc' => 'Cũ nhất',
            ],
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Chờ xác nhận,Đã xác nhận,Từ chối,Huỷ bỏ,Hoàn thành',
            'cancel_reason' => 'required_if:status,Huỷ bỏ,Từ chối|string|max:500|nullable',
        ]);

        $result = $this->scheduleService->updateScheduleStatus(
            $id,
            $request->input('status'),
            $request->input('cancel_reason')
        );

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }
        return redirect()->back()->with('success', 'Trạng thái đã được cập nhật thành công!');
    }
}