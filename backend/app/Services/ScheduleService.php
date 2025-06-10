<?php
namespace App\Services;

use App\Models\Schedule;
use Illuminate\Support\Facades\Log;

class ScheduleService
{
    public function getSchedules(string $querySearch = '', string $status = '', int $perPage = 10)
    {
        try {
            $query = Schedule::with(['user', 'room']);

            if ($querySearch) {
                $query->where(function ($q) use ($querySearch) {
                    $q->where('message', 'like', "%$querySearch%");
                });
            }

            if ($status) {
                $query->where('status', $status);
            }

            $schedules = $query->paginate($perPage);
            return ['data' => $schedules];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách lịch xem phòng', 'status' => 500];
        }
    }

    public function getSchedule($id)
    {
        try {
            $schedule = Schedule::with(['user', 'room'])->findOrFail($id);
            return ['data' => $schedule];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Không tìm thấy lịch xem phòng', 'status' => 404];
        }
    }

    public function updateScheduleStatus($id, $status)
    {
        try {
            $schedule = Schedule::findOrFail($id);
            $schedule->update(['status' => $status]);
            return ['data' => $schedule];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi cập nhật trạng thái', 'status' => 500];
        }
    }
}