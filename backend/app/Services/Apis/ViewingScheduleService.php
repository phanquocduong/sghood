<?php

namespace App\Services\Apis;

use App\Jobs\Apis\SendScheduleNotification;
use App\Models\Schedule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Dịch vụ xử lý logic nghiệp vụ liên quan đến lịch xem nhà trọ.
 */
class ViewingScheduleService
{
    /**
     * Xác định thứ tự sắp xếp dựa trên tham số sort.
     *
     * @param string|null $sort Tiêu chí sắp xếp (oldest, latest, default)
     * @return string Thứ tự sắp xếp (asc hoặc desc)
     */
    private function getSortOrder($sort): string
    {
        return match ($sort) {
            'oldest' => 'asc', // Sắp xếp theo thứ tự cũ nhất
            'latest', 'default' => 'desc', // Sắp xếp theo thứ tự mới nhất hoặc mặc định
            default => 'desc',
        };
    }

    /**
     * Lấy danh sách lịch xem nhà trọ của người dùng.
     *
     * @param array $filters Mảng chứa các bộ lọc (sort, status)
     * @return Collection Danh sách lịch xem đã được định dạng
     */
    public function getSchedules(array $filters): Collection
    {
        // Xây dựng truy vấn lấy lịch xem của người dùng
        $query = Schedule::query()
            ->where('user_id', Auth::id()) // Lọc theo ID người dùng
            ->with(['motel']); // Lấy thông tin nhà trọ liên quan

        // Áp dụng bộ lọc trạng thái nếu có
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Sắp xếp theo tiêu chí
        $query->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

        // Lấy và định dạng dữ liệu lịch xem
        return $query->get()->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'motel_id' => $schedule->motel->id,
                'motel_slug' => $schedule->motel->slug,
                'motel_name' => $schedule->motel->name,
                'motel_address' => $schedule->motel->address,
                'motel_image' => $schedule->motel->main_image->image_url, // Ảnh chính của nhà trọ
                'scheduled_at' => $schedule->scheduled_at, // Thời gian xem
                'message' => $schedule->message, // Lời nhắn
                'status' => $schedule->status, // Trạng thái lịch
                'rejection_reason' => $schedule->rejection_reason, // Lý do từ chối (nếu có)
            ];
        })->values();
    }

    /**
     * Tạo mới lịch xem nhà trọ.
     *
     * @param array $data Dữ liệu đã xác thực (ngày, khung giờ, lời nhắn, user_id, motel_id)
     * @return Schedule Mô hình lịch xem vừa tạo
     * @throws HttpResponseException Nếu có xung đột lịch hoặc dữ liệu không hợp lệ
     */
    public function createSchedule(array $data): Schedule
    {
        // Sử dụng giao dịch để đảm bảo tính toàn vẹn dữ liệu
        $schedule = DB::transaction(function () use ($data) {
            // Chuyển đổi định dạng ngày từ dd/mm/yyyy
            $date = \DateTime::createFromFormat('d/m/Y', $data['date']);
            // Tách khung giờ bắt đầu từ timeSlot
            $timeParts = explode(' - ', $data['timeSlot']);
            $startTime = trim($timeParts[0]);

            // Xử lý giờ và phút từ khung giờ
            [$hour, $minute] = explode(':', preg_replace('/\s*(sáng|chiều)/', '', $startTime));
            $hour = (int)$hour + (strpos($startTime, 'chiều') !== false && $hour < 12 ? 12 : 0);
            $minute = (int)($minute ?? 0);

            // Tạo thời gian đầy đủ (datetime)
            $dateTime = $date->setTime($hour, $minute)->format('Y-m-d H:i:s');

            // Kiểm tra lịch xem chưa hoàn thành cho cùng nhà trọ
            $existingSchedule = Schedule::where('user_id', $data['user_id'])
                ->where('motel_id', $data['motel_id'])
                ->whereNotIn('status', ['Hoàn thành', 'Từ chối', 'Huỷ bỏ'])
                ->first();

            if ($existingSchedule) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Bạn đã có một lịch xem nhà trọ chưa hoàn thành cho nhà trọ này.',
                ], 422));
            }

            // Kiểm tra xung đột lịch trong cùng khung giờ
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

            // Tạo lịch xem mới
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

        // Gửi thông báo đến quản trị viên
        SendScheduleNotification::dispatch($schedule, 'pending',
            'Lịch xem nhà trọ mới #' . $schedule->id,
            "Người dùng {$schedule->user->name} đã đặt lịch xem {$schedule->motel->name} vào lúc {$schedule->scheduled_at->format('d/m/Y H:i')}."
        );

        return $schedule;
    }

    /**
     * Cập nhật lịch xem nhà trọ.
     *
     * @param int $id ID của lịch xem
     * @param array $data Dữ liệu đã xác thực (ngày, khung giờ, lời nhắn)
     * @return Schedule Mô hình lịch xem đã cập nhật
     * @throws HttpResponseException Nếu có xung đột lịch hoặc trạng thái không hợp lệ
     */
    public function updateSchedule($id, array $data): Schedule
    {
        // Sử dụng giao dịch để đảm bảo tính toàn vẹn dữ liệu
        $schedule = DB::transaction(function () use ($id, $data) {
            // Tìm lịch xem theo ID
            $schedule = Schedule::findOrFail($id);

            // Kiểm tra trạng thái lịch xem
            if ($schedule->status !== 'Chờ xác nhận') {
                throw new HttpResponseException(response()->json([
                    'error' => 'Chỉ có thể chỉnh sửa lịch khi trạng thái là Chờ xác nhận.',
                ], 422));
            }

            // Chuyển đổi định dạng ngày từ dd/mm/yyyy
            $date = \DateTime::createFromFormat('d/m/Y', $data['date']);
            // Tách khung giờ bắt đầu từ timeSlot
            $timeParts = explode(' - ', $data['timeSlot']);
            $startTime = trim($timeParts[0]);

            // Xử lý giờ và phút từ khung giờ
            [$hour, $minute] = explode(':', preg_replace('/\s*(sáng|chiều)/', '', $startTime));
            $hour = (int)$hour + (strpos($startTime, 'chiều') !== false && $hour < 12 ? 12 : 0);
            $minute = (int)($minute ?? 0);

            // Tạo thời gian đầy đủ (datetime)
            $dateTime = $date->setTime($hour, $minute)->format('Y-m-d H:i:s');

            // Kiểm tra xung đột lịch trong cùng khung giờ
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

            // Cập nhật thông tin lịch xem
            $schedule->update([
                'scheduled_at' => $dateTime,
                'message' => $data['message'] ?? null,
                'updated_at' => now(),
            ]);

            return $schedule;
        });

        // Gửi thông báo đến quản trị viên
        SendScheduleNotification::dispatch($schedule, 'updated',
            'Lịch xem nhà trọ #' . $schedule->id . ' đã cập nhật',
            "Người dùng {$schedule->user->name} đã cập nhật lịch xem {$schedule->motel->name} vào lúc {$schedule->scheduled_at->format('d/m/Y H:i')}."
        );

        return $schedule;
    }

    /**
     * Hủy lịch xem nhà trọ.
     *
     * @param int $id ID của lịch xem
     * @return Schedule Mô hình lịch xem đã hủy
     */
    public function cancelSchedule($id): Schedule
    {
        // Sử dụng giao dịch để đảm bảo tính toàn vẹn dữ liệu
        $schedule = DB::transaction(function () use ($id) {
            // Tìm lịch xem theo ID
            $schedule = Schedule::findOrFail($id);
            // Cập nhật trạng thái thành Huỷ bỏ
            $schedule->update(['status' => Schedule::STATUS_CANCELED]);

            return $schedule;
        });

        // Gửi thông báo đến quản trị viên
        SendScheduleNotification::dispatch($schedule, 'canceled',
            'Lịch xem nhà trọ #' . $schedule->id . ' đã bị hủy',
            "Người dùng {$schedule->user->name} đã hủy lịch xem {$schedule->motel->name} vào lúc {$schedule->scheduled_at->format('d/m/Y H:i')}."
        );

        return $schedule;
    }
}
