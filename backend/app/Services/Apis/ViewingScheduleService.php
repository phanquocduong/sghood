<?php

namespace App\Services\Apis;

use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Mail\SchedulePendingEmail;
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
            ->with(['motel'])
            ->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

        $schedules = $query->get()->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'motel_id' => $schedule->motel->id,
                'motel_name' => $schedule->motel->name,
                'motel_image' => $schedule->motel->images->first()->image_url ?? null,
                'scheduled_at' => $schedule->scheduled_at->toIso8601String(),
                'message' => $schedule->message,
                'status' => $schedule->status,
                'cancellation_reason' => $schedule->cancellation_reason,
                'created_at' => $schedule->created_at->toIso8601String(),
            ];
        });

        $sortOrder = $this->getSortOrder($filters['sort'] ?? 'default');
        if ($sortOrder === 'asc') {
            $schedules = $schedules->sortBy('created_at');
        } else {
            $schedules = $schedules->sortByDesc('created_at');
        }

        return $schedules->values();
    }

    public function createSchedule(array $data)
    {
        $date = \DateTime::createFromFormat('d/m/Y', $data['date']);
        $timeParts = explode(' - ', $data['timeSlot']);
        $startTime = trim($timeParts[0]);

        $timeParts = explode(' ', $startTime);
        $time = $timeParts[0];
        $period = isset($timeParts[1]) ? $timeParts[1] : '';
        $hourMinute = explode(':', $time);
        $hour = intval($hourMinute[0]);
        $minute = intval($hourMinute[1]);

        if ($period === 'chiều') {
            $hour += 12;
        }

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
}
