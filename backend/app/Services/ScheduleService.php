<?php
namespace App\Services;

use App\Mail\ScheduleStatusMail;
use App\Jobs\SendScheduleStatusUpdatedNotification;
use App\Models\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
class ScheduleService
{
    public function getSchedules(string $querySearch = '', string $status = '', int $perPage = 10, string $sortBy = ''): array
    {
        try {
            $query = Schedule::with(['user', 'motel']);

            if ($querySearch) {
                $query->where(function ($q) use ($querySearch) {
                    $q->where('message', 'like', "%$querySearch%")
                        ->orWhere('rejection_reason', 'like', "%$querySearch%")
                        ->orWhereHas('user', function ($q) use ($querySearch) {
                            $q->where('name', 'like', "%$querySearch%")
                                ->orWhere('email', 'like', "%$querySearch%");
                        })
                        ->orWhereHas('motel', function ($q) use ($querySearch) {
                            $q->where('name', 'like', "%$querySearch%")
                                ->orWhere('description', 'like', "%$querySearch%");
                        });
                });
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($sortBy === 'created_at_desc') {
                $query->orderBy('created_at', 'desc');
            } elseif ($sortBy === 'created_at_asc') {
                $query->orderBy('created_at', 'asc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $schedules = $query->paginate($perPage);
            return ['data' => $schedules];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách lịch xem phòng' . $e->getMessage(), 'status' => 500];
        }
    }

    public function getSchedule($id)
    {
        try {
            $schedule = Schedule::with(['user', 'motel'])->findOrFail($id);
            return ['data' => $schedule];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Không tìm thấy lịch xem phòng', 'status' => 404];
        }
    }

    public function updateScheduleStatus(int $id, string $newStatus, ?string $cancelReason = null): array
    {
        try {
            $schedule = Schedule::with(['motel', 'user'])->findOrFail($id);

            // Lưu trạng thái cũ trước khi cập nhật
            $oldStatus = $schedule->status;

            // Cập nhật trạng thái mới và lý do hủy (nếu có)
            $schedule->status = $newStatus;
            if (in_array($newStatus, ['Huỷ bỏ', 'Từ chối']) && $cancelReason) {
                $schedule->rejection_reason = $cancelReason;
            } else {
                $schedule->rejection_reason = null;
            }
            $schedule->save();

            // Đẩy job gửi email + FCM notification
            dispatch(new SendScheduleStatusUpdatedNotification($schedule, $oldStatus, $newStatus));

            return ['data' => $schedule];
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật trạng thái lịch xem phòng: ' . $e->getMessage());
            return [
                'error' => 'Đã xảy ra lỗi khi cập nhật trạng thái lịch xem phòng: ' . $e->getMessage(),
                'status' => 500
            ];
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
                    'cancel_reason' => $schedule->rejection_reason ?? 'N/A'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi gửi email thông báo: ' . $e->getMessage());
        }
    }
}
