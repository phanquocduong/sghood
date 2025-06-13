<?php
namespace App\Services;

use App\Mail\ScheduleStatusMail;
use App\Models\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ScheduleService
{
    public function getSchedules(string $querySearch = '', string $status = '', int $perPage = 10)
    {
        try {
            $query = Schedule::with(['user', 'room']);

            if ($querySearch) {
                $query->where(function ($q) use ($querySearch) {
                    $q->where('message', 'like', "%$querySearch%")
                      ->orWhere('cancel_reason', 'like', "%$querySearch%");
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

    public function updateScheduleStatus(int $id, string $newStatus, ?string $cancelReason = null): array
    {
        try {
            $schedule = Schedule::with(['room', 'user'])->find($id);
            if (!$schedule) {
                return ['error' => 'Lịch xem phòng không tồn tại'];
            }
            
            // Lưu trạng thái cũ trước khi cập nhật
            $oldStatus = $schedule->status;
            
            // Cập nhật trạng thái mới và lý do hủy (nếu có)
            $schedule->status = $newStatus;
            if ($newStatus === 'Huỷ bỏ' && $cancelReason) {
                $schedule->cancellation_reason = $cancelReason;
            } elseif ($newStatus !== 'Huỷ bỏ') {
                $schedule->cancellation_reason = null;
            }
            $schedule->save();
            
            // Gửi email thông báo
            $this->sendStatusUpdateEmail($schedule, $oldStatus, $newStatus);
            
            return ['data' => $schedule];
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật trạng thái lịch xem phòng: ' . $e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi cập nhật trạng thái lịch xem phòng: ' . $e->getMessage(), 'status' => 500];
        }
    }
    
    /**
     * Gửi email thông báo cập nhật trạng thái
     */
    private function sendStatusUpdateEmail(Schedule $schedule, string $oldStatus, string $newStatus): void
    {
        try {
            if ($schedule->user && $schedule->user->email) {
                Mail::to($schedule->user->email)->send(new ScheduleStatusMail($schedule, $oldStatus, $newStatus));
                Log::info('Đã gửi email thông báo cập nhật trạng thái lịch xem phòng', [
                    'schedule_id' => $schedule->id,
                    'user_email' => $schedule->user->email,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'cancel_reason' => $schedule->cancel_reason ?? 'N/A'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi gửi email thông báo: ' . $e->getMessage());
        }
    }
}