<?php

namespace App\Services\Apis;

use App\Mail\Apis\ScheduleCanceledEmail;
use App\Mail\Apis\SchedulePendingEmail;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ViewingScheduleService
{
    public function getSchedules(array $filters)
    {
        $query = Schedule::query()
            ->where('user_id', Auth::id())
            ->with(['motel']);

        // Lọc theo trạng thái nếu có
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

        $schedules = $query->get()->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'motel_id' => $schedule->motel->id,
                'motel_name' => $schedule->motel->name,
                'motel_image' => $schedule->motel->main_image->image_url,
                'scheduled_at' => $schedule->scheduled_at,
                'message' => $schedule->message,
                'status' => $schedule->status,
                'cancellation_reason' => $schedule->cancellation_reason,
            ];
        });

        return $schedules->values();
    }

    public function createSchedule(array $data)
    {
        $date = \DateTime::createFromFormat('d/m/Y', $data['date']);
        $timeParts = explode(' - ', $data['timeSlot']);
        $startTime = trim($timeParts[0]);

        // Tách giờ và phút từ startTime
        $hourMinute = explode(':', $startTime);
        $hour = intval($hourMinute[0]);
        $minute = isset($hourMinute[1]) ? intval($hourMinute[1]) : 0;

        // Kiểm tra nếu có định dạng "sáng" hoặc "chiều"
        $period = '';
        if (strpos($startTime, 'sáng') !== false) {
            $period = 'sáng';
        } elseif (strpos($startTime, 'chiều') !== false) {
            $period = 'chiều';
            // Nếu là buổi chiều và giờ nhỏ hơn 12, tăng thêm 12 để chuyển sang định dạng 24h
            if ($hour < 12) {
                $hour += 12;
            }
        }

        // Đặt thời gian cho ngày
        $dateTime = $date->setTime($hour, $minute)->format('Y-m-d H:i:s');

        $existingSchedule = Schedule::where('user_id', $data['user_id'])
            ->where('motel_id', $data['motel_id'])
            ->whereNotIn('status', ['Hoàn thành'])
            ->first();

        if ($existingSchedule) {
            throw new HttpResponseException(response()->json([
                'error' => 'Bạn đã có một lịch xem nhà trọ chưa hoàn thành cho nhà trọ này. Vui lòng hoàn thành hoặc hủy lịch trước khi đặt lịch mới.'
            ], 422));
        }

        $timeSlotStart = $date->setTime($hour, $minute);
        $timeSlotEnd = (clone $timeSlotStart)->modify('+30 minutes');

        $conflictingSchedule = Schedule::where('user_id', $data['user_id'])
            ->where('motel_id', '!=', $data['motel_id'])
            ->whereBetween('scheduled_at', [$timeSlotStart->format('Y-m-d H:i:s'), $timeSlotEnd->format('Y-m-d H:i:s')])
            ->whereNotIn('status', ['Huỷ bỏ', 'Hoàn thành'])
            ->first();

        if ($conflictingSchedule) {
            throw new HttpResponseException(response()->json([
                'error' => 'Bạn đã có một lịch xem nhà trọ khác trong khung giờ này. Vui lòng chọn khung giờ khác.'
            ], 422));
        }

        $schedule = Schedule::create([
            'user_id' => $data['user_id'],
            'motel_id' => $data['motel_id'],
            'scheduled_at' => $dateTime,
            'message' => $data['message'] ?? null,
            'status' => 'Chờ xác nhận',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->notifyAdmins($schedule);

        return $schedule;
    }

    public function rejectSchedule($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->update(['status' => Schedule::STATUS_CANCELED]);
        $this->notifyAdminsCanceled($schedule);
        return $schedule;
    }

    protected function getSortOrder($sort)
    {
        return match ($sort) {
            'oldest' => 'asc',
            'latest', 'default' => 'desc',
            default => 'desc',
        };
    }

    private function notifyAdmins($schedule)
    {
        try {
            $admins = User::where('role', 'Quản trị viên')->get();

            if ($admins->isEmpty()) {
                Log::warning('Không tìm thấy admin với role Quản trị viên');
                return;
            }

            $title = 'Lịch xem nhà trọ mới';
            $body = "Lịch xem nhà trọ #{$schedule->id} từ người dùng {$schedule->user->name} đang chờ xác nhận.";

            Mail::to($admins->pluck('email'))->send(new SchedulePendingEmail($schedule));

            $messaging = app('firebase.messaging');

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $title,
                    'content' => $body,
                ]);

                if ($admin->fcm_token) {
                    $baseUrl = config('app.url');
                    $link = "$baseUrl/schedules";
                    $message = CloudMessage::fromArray([
                        'token' => $admin->fcm_token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data' => [
                            'link' => $link,
                        ],
                    ]);

                    $messaging->send($message);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Lỗi gửi thông báo cho admin', [
                'schedule_id' => $schedule->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function notifyAdminsCanceled($schedule)
    {
        try {
            $admins = User::where('role', 'Quản trị viên')->get();

            if ($admins->isEmpty()) {
                Log::warning('Không tìm thấy admin với role Quản trị viên');
                return;
            }

            $title = 'Lịch xem nhà trọ đã bị hủy';
            $body = "Lịch xem nhà trọ #{$schedule->id} từ người dùng {$schedule->user->name} đã bị hủy.";

            Mail::to($admins->pluck('email'))->send(new ScheduleCanceledEmail($schedule));

            $messaging = app('firebase.messaging');

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $title,
                    'content' => $body,
                ]);

                if ($admin->fcm_token) {
                    $baseUrl = config('app.url');
                    $link = "$baseUrl/schedules";
                    $message = CloudMessage::fromArray([
                        'token' => $admin->fcm_token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data' => [
                            'link' => $link,
                        ],
                    ]);

                    $messaging->send($message);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Lỗi gửi thông báo hủy lịch cho admin', [
                'schedule_id' => $schedule->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
