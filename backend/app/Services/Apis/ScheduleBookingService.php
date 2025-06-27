<?php

namespace App\Services\Apis;

use App\Models\Schedule;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;

class ScheduleBookingService
{
    public function getItems(array $filters)
    {
        $items = collect();

        if (empty($filters['type']) || $filters['type'] === 'schedule') {
            $querySchedules = Schedule::query()
                ->where('user_id', Auth::id())
                ->with(['room.motel'])
                ->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

            $schedules = $querySchedules->get()->map(function ($schedule) {
                $hasBooked = Booking::where('user_id', Auth::id())
                    ->where('room_id', $schedule->room_id)
                    ->exists();

                return [
                    'type' => 'schedule',
                    'id' => $schedule->id,
                    'room_id' => $schedule->room->id,
                    'room_name' => $schedule->room->name,
                    'room_image' => $schedule->room->main_image->image_url,
                    'motel_name' => $schedule->room->motel->name,
                    'scheduled_at' => $schedule->scheduled_at->toIso8601String(),
                    'message' => $schedule->message,
                    'cancellation_reason' => $schedule->cancellation_reason,
                    'status' => $schedule->status,
                    'created_at' => $schedule->created_at->toIso8601String(),
                    'has_booked' => $hasBooked,
                ];
            });

            $items = $items->merge($schedules);
        }

        if (empty($filters['type']) || $filters['type'] === 'booking') {
            $queryBookings = Booking::query()
                ->where('user_id', Auth::id())
                ->with(['room.motel'])
                ->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

            $bookings = $queryBookings->get()->map(function ($booking) {
                return [
                    'type' => 'booking',
                    'id' => $booking->id,
                    'room_id' => $booking->room->id,
                    'room_name' => $booking->room->name,
                    'room_image' => $booking->room->main_image->image_url,
                    'motel_name' => $booking->room->motel->name,
                    'start_date' => $booking->start_date->toIso8601String(),
                    'end_date' => $booking->end_date->toIso8601String(),
                    'note' => $booking->note,
                    'status' => $booking->status,
                    'created_at' => $booking->created_at->toIso8601String(),
                ];
            });

            $items = $items->merge($bookings);
        }

        $sortOrder = $this->getSortOrder($filters['sort'] ?? 'default');
        if ($sortOrder === 'asc') {
            $items = $items->sortBy('created_at');
        } else {
            $items = $items->sortByDesc('created_at');
        }

        return $items->values();
    }

    public function createItem(array $data, string $type)
    {
        if ($type === 'schedule') {
            $date = \DateTime::createFromFormat('d/m/Y', $data['date']);
            $timeParts = explode(' - ', $data['timeSlot']);
            $startTime = trim($timeParts[0]);

            // Chuyển đổi thời gian sáng/chiều sang định dạng 24h
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

            // Kiểm tra lịch xem phòng chưa hoàn thành cho phòng này
            $existingSchedule = Schedule::where('user_id', $data['user_id'])
                ->where('room_id', $data['room_id'])
                ->whereNotIn('status', ['Hoàn thành'])
                ->first();

            if ($existingSchedule) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Bạn đã có một lịch xem phòng chưa hoàn thành cho phòng này. Vui lòng hoàn thành hoặc hủy lịch trước khi đặt lịch mới.'
                ], 422));
            }

            // Kiểm tra lịch xem phòng trùng khung giờ
            $timeSlotStart = $date->setTime($hour, $minute);
            $timeSlotEnd = (clone $timeSlotStart)->modify('+30 minutes');

            $conflictingSchedule = Schedule::where('user_id', $data['user_id'])
                ->where('room_id', '!=', $data['room_id'])
                ->whereBetween('scheduled_at', [$timeSlotStart->format('Y-m-d H:i:s'), $timeSlotEnd->format('Y-m-d H:i:s')])
                ->whereNotIn('status', ['Huỷ bỏ', 'Hoàn thành'])
                ->first();

            if ($conflictingSchedule) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Bạn đã có một lịch xem phòng khác trong khung giờ này. Vui lòng chọn khung giờ khác.'
                ], 422));
            }

            return Schedule::create([
                'user_id' => $data['user_id'],
                'room_id' => $data['room_id'],
                'scheduled_at' => $dateTime,
                'message' => $data['message'] ?? null,
                'status' => 'Chờ xác nhận',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif ($type === 'booking') {
            $startDate = Carbon::createFromFormat('d/m/Y', $data['start_date']);
            $duration = (int) str_replace(' năm', '', $data['duration']);
            $endDate = $startDate->copy()->addYears($duration);

            return Booking::create([
                'user_id' => $data['user_id'],
                'room_id' => $data['room_id'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'note' => $data['note'] ?? null,
                'status' => 'Chờ xác nhận'
            ]);
        }

        throw new HttpResponseException(response()->json([
            'error' => 'Loại không hợp lệ. Vui lòng chọn "schedule" hoặc "booking".'
        ], 400));
    }

    public function rejectItem($id, $type)
    {
        if ($type === 'schedule') {
            $item = Schedule::findOrFail($id);
            $item->update(['status' => Schedule::STATUS_CANCELED]);
        } else {
            $item = Booking::findOrFail($id);
            $item->update(['status' => Booking::STATUS_CANCELED]);
        }
        return $item;
    }

    protected function getSortOrder($sort)
    {
        return match ($sort) {
            'oldest' => 'asc',
            'latest', 'default' => 'desc',
            default => 'desc',
        };
    }
}
