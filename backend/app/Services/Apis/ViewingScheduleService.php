<?php

namespace App\Services\Apis;

use App\Mail\Apis\ScheduleCanceledEmail;
use App\Mail\Apis\SchedulePendingEmail;
use App\Mail\Apis\ScheduleUpdatedEmail;
use App\Models\Schedule;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ViewingScheduleService
{
    private function getSortOrder($sort): string
    {
        return match ($sort) {
            'oldest' => 'asc',
            'latest', 'default' => 'desc',
            default => 'desc',
        };
    }

    private function notifyAdminsGeneric($schedule, $emailClass, $title, $body): void
    {
        try {
            $admins = User::where('role', 'Quản trị viên')->get();
            if ($admins->isEmpty()) {
                Log::warning('Không tìm thấy admin với role Quản trị viên');
                return;
            }

            Mail::to($admins->pluck('email'))->send(new $emailClass($schedule));
            $messaging = app('firebase.messaging');
            $baseUrl = config('app.url');
            $link = "$baseUrl/schedules";

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $title,
                    'content' => $body,
                ]);

                if ($admin->fcm_token) {
                    $message = CloudMessage::fromArray([
                        'token' => $admin->fcm_token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data' => ['link' => $link],
                    ]);
                    $messaging->send($message);
                }
            }
        } catch (\Throwable $e) {
            Log::error("Lỗi gửi thông báo: {$title}", [
                'schedule_id' => $schedule->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getSchedules(array $filters): Collection
    {
        $query = Schedule::query()
            ->where('user_id', Auth::id())
            ->with(['motel']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

        return $query->get()->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'motel_id' => $schedule->motel->id,
                'motel_slug' => $schedule->motel->slug,
                'motel_name' => $schedule->motel->name,
                'motel_address' => $schedule->motel->address,
                'motel_image' => $schedule->motel->main_image->image_url,
                'scheduled_at' => $schedule->scheduled_at,
                'message' => $schedule->message,
                'status' => $schedule->status,
                'cancellation_reason' => $schedule->cancellation_reason,
            ];
        })->values();
    }

    public function createSchedule(array $data): Schedule
    {
        $date = \DateTime::createFromFormat('d/m/Y', $data['date']);
        $timeParts = explode(' - ', $data['timeSlot']);
        $startTime = trim($timeParts[0]);

        [$hour, $minute] = explode(':', preg_replace('/\s*(sáng|chiều)/', '', $startTime));
        $hour = (int)$hour + (strpos($startTime, 'chiều') !== false && $hour < 12 ? 12 : 0);
        $minute = (int)($minute ?? 0);

        $dateTime = $date->setTime($hour, $minute)->format('Y-m-d H:i:s');

        $existingSchedule = Schedule::where('user_id', $data['user_id'])
            ->where('motel_id', $data['motel_id'])
            ->whereNotIn('status', ['Hoàn thành'])
            ->first();

        if ($existingSchedule) {
            throw new HttpResponseException(response()->json([
                'error' => 'Bạn đã có một lịch xem nhà trọ chưa hoàn thành cho nhà trọ này.',
            ], 422));
        }

        $timeSlotStart = $date->setTime($hour, $minute);
        $timeSlotEnd = (clone $timeSlotStart)->modify('+30 minutes');

        $conflictingSchedule = Schedule::where('user_id', $data['user_id'])
            ->where('motel_id', '!=', $data['motel_id'])
            ->whereBetween('scheduled_at', [$timeSlotStart, $timeSlotEnd])
            ->whereNotIn('status', ['Huỷ bỏ', 'Hoàn thành'])
            ->first();

        if ($conflictingSchedule) {
            throw new HttpResponseException(response()->json([
                'error' => 'Bạn đã có một lịch xem nhà trọ khác trong khung giờ này.',
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

        $this->notifyAdminsGeneric(
            $schedule,
            SchedulePendingEmail::class,
            'Lịch xem nhà trọ mới #' . $schedule->id,
            "Người dùng {$schedule->user->name} đã đặt lịch xem nhà trọ {$schedule->motel->name} vào lúc {$schedule->scheduled_at->format('d/m/Y H:i')}."
        );

        return $schedule;
    }

    public function updateSchedule($id, array $data): Schedule
    {
        $schedule = Schedule::findOrFail($id);

        if ($schedule->status !== 'Chờ xác nhận') {
            throw new HttpResponseException(response()->json([
                'error' => 'Chỉ có thể chỉnh sửa lịch khi trạng thái là Chờ xác nhận.',
            ], 422));
        }

        $date = \DateTime::createFromFormat('d/m/Y', $data['date']);
        $timeParts = explode(' - ', $data['timeSlot']);
        $startTime = trim($timeParts[0]);

        [$hour, $minute] = explode(':', preg_replace('/\s*(sáng|chiều)/', '', $startTime));
        $hour = (int)$hour + (strpos($startTime, 'chiều') !== false && $hour < 12 ? 12 : 0);
        $minute = (int)($minute ?? 0);

        $dateTime = $date->setTime($hour, $minute)->format('Y-m-d H:i:s');

        $timeSlotStart = $date->setTime($hour, $minute);
        $timeSlotEnd = (clone $timeSlotStart)->modify('+30 minutes');

        $conflictingSchedule = Schedule::where('user_id', Auth::id())
            ->where('id', '!=', $id)
            ->whereBetween('scheduled_at', [$timeSlotStart, $timeSlotEnd])
            ->whereNotIn('status', ['Huỷ bỏ', 'Hoàn thành'])
            ->first();

        if ($conflictingSchedule) {
            throw new HttpResponseException(response()->json([
                'error' => 'Bạn đã có một lịch xem nhà trọ khác trong khung giờ này.',
            ], 422));
        }

        $schedule->update([
            'scheduled_at' => $dateTime,
            'message' => $data['message'] ?? null,
            'updated_at' => now(),
        ]);

        $this->notifyAdminsGeneric(
            $schedule,
            ScheduleUpdatedEmail::class,
            'Lịch xem nhà trọ #' . $schedule->id . ' đã cập nhật',
            "Người dùng {$schedule->user->name} đã cập nhật lịch xem nhà trọ {$schedule->motel->name} vào lúc {$schedule->scheduled_at->format('d/m/Y H:i')}."
        );

        return $schedule;
    }

    public function rejectSchedule($id): Schedule
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->update(['status' => Schedule::STATUS_CANCELED]);

        $this->notifyAdminsGeneric(
            $schedule,
            ScheduleCanceledEmail::class,
            'Lịch xem nhà trọ #' . $schedule->id . ' đã bị hủy',
            "Người dùng {$schedule->user->name} đã hủy lịch xem nhà trọ {$schedule->motel->name} vào lúc {$schedule->scheduled_at->format('d/m/Y H:i')}."
        );

        return $schedule;
    }
}
