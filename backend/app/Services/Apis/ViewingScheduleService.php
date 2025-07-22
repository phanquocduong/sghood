<?php

namespace App\Services\Apis;

use App\Jobs\Apis\SendScheduleNotification;
use App\Models\Schedule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                'rejection_reason' => $schedule->rejection_reason,
            ];
        })->values();
    }

    public function createSchedule(array $data): Schedule
    {
        $schedule = DB::transaction(function () use ($data) {
            $date = \DateTime::createFromFormat('d/m/Y', $data['date']);
            $timeParts = explode(' - ', $data['timeSlot']);
            $startTime = trim($timeParts[0]);

            [$hour, $minute] = explode(':', preg_replace('/\s*(sáng|chiều)/', '', $startTime));
            $hour = (int)$hour + (strpos($startTime, 'chiều') !== false && $hour < 12 ? 12 : 0);
            $minute = (int)($minute ?? 0);

            $dateTime = $date->setTime($hour, $minute)->format('Y-m-d H:i:s');

            $existingSchedule = Schedule::where('user_id', $data['user_id'])
                ->where('motel_id', $data['motel_id'])
                ->whereNotIn('status', ['Hoàn thành', 'Từ chối', 'Huỷ bỏ'])
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
                ->whereNotIn('status', ['Huỷ bỏ', 'Từ chối', 'Hoàn thành'])
                ->first();

            if ($conflictingSchedule) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Bạn đã có một lịch xem nhà trọ khác trong khung giờ này.',
                ], 422));
            }

            return Schedule::create([
                'user_id' => $data['user_id'],
                'motel_id' => $data['motel_id'],
                'scheduled_at' => $dateTime,
                'message' => $data['message'] ?? null,
                'status' => 'Chờ xác nhận',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        SendScheduleNotification::dispatch($schedule, 'pending',
            'Lịch xem nhà trọ mới #' . $schedule->id,
            "Người dùng {$schedule->user->name} đã đặt lịch xem {$schedule->motel->name} vào lúc {$schedule->scheduled_at->format('d/m/Y H:i')}."
        );

        return $schedule;
    }

    public function updateSchedule($id, array $data): Schedule
    {
        $schedule = DB::transaction(function () use ($id, $data) {
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

            return $schedule;
        });

        SendScheduleNotification::dispatch($schedule, 'updated',
            'Lịch xem nhà trọ #' . $schedule->id . ' đã cập nhật',
            "Người dùng {$schedule->user->name} đã cập nhật lịch xem {$schedule->motel->name} vào lúc {$schedule->scheduled_at->format('d/m/Y H:i')}."
        );

        return $schedule;
    }

    public function cancelSchedule($id): Schedule
    {
        $schedule = DB::transaction(function () use ($id) {
            $schedule = Schedule::findOrFail($id);
            $schedule->update(['status' => Schedule::STATUS_CANCELED]);

            return $schedule;
        });

        SendScheduleNotification::dispatch($schedule, 'canceled',
            'Lịch xem nhà trọ #' . $schedule->id . ' đã bị hủy',
            "Người dùng {$schedule->user->name} đã hủy lịch xem {$schedule->motel->name} vào lúc {$schedule->scheduled_at->format('d/m/Y H:i')}."
        );

        return $schedule;
    }
}
