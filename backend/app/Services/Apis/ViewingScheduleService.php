<?php

namespace App\Services\Apis;

use App\Models\ViewingSchedule;
use Illuminate\Support\Facades\Auth;

class ViewingScheduleService
{
    public function createSchedule($data)
    {
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

        return ViewingSchedule::create([
            'user_id' => $data['user_id'],
            'room_id' => $data['room_id'],
            'scheduled_at' => $dateTime,
            'message' => $data['message'] ?? null,
            'status' => 'Chờ xác nhận',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getBookings(array $filters)
    {
        $query = ViewingSchedule::query();

        // Chỉ lấy viewing_schedules của user hiện tại
        $query->where('user_id', Auth::id());

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['sort'] === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($filters['sort'] === 'latest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($filters['sort'] === 'default') {
            $query->orderBy('created_at', 'desc');
        }

        // Eager load quan hệ 'room' và 'motel' của room
        return $query->with(['room.motel'])->get()->map(function ($booking) {
            return [
                'id' => $booking->id,
                'room_id' => $booking->room->id,
                'room_name' => $booking->room->name,
                'room_image' => $booking->room->main_image->image_url,
                'motel_name' => $booking->room->motel->name,
                'scheduled_at' => $booking->scheduled_at,
                'message' => $booking->message,
                'status' => $booking->status,
                'created_at' => $booking->created_at,
            ];
        });
    }

    public function rejectBooking($id)
    {
        $booking = ViewingSchedule::findOrFail($id);
        $booking->update(['status' => ViewingSchedule::STATUS_CANCELED]);
        return $booking;
    }
}
