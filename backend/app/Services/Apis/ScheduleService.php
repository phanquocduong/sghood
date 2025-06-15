<?php

namespace App\Services\Apis;

use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;

class ScheduleService
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

        // Kiểm tra 1: Người dùng đã có lịch xem phòng cho phòng này chưa hoàn thành
        $existingSchedule = Schedule::where('user_id', $data['user_id'])
            ->where('room_id', $data['room_id'])
            ->whereNotIn('status', ['Hoàn thành'])
            ->first();

        if ($existingSchedule) {
            throw new HttpResponseException(response()->json([
                'error' => 'Bạn đã có một lịch xem phòng chưa hoàn thành cho phòng này. Vui lòng hoàn thành hoặc hủy lịch trước khi đặt lịch mới.'
            ], 422));
        }

        // Kiểm tra 2: Người dùng có lịch xem phòng khác trùng khung giờ
        $timeSlotStart = $date->setTime($hour, $minute);
        $timeSlotEnd = (clone $timeSlotStart)->modify('+30 minutes'); // Giả sử mỗi khung giờ kéo dài 30 phút

        $conflictingSchedule = Schedule::where('user_id', $data['user_id'])
            ->where('room_id', '!=', $data['room_id']) // Khác phòng
            ->whereBetween('scheduled_at', [$timeSlotStart->format('Y-m-d H:i:s'), $timeSlotEnd->format('Y-m-d H:i:s')])
            ->whereNotIn('status', ['Huỷ bỏ', 'Hoàn thành']) // Chỉ kiểm tra lịch chưa hủy hoặc chưa hoàn thành
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
    }
}
