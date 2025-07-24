<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Lấy danh sách user từ id 6 đến 106 có viewing_schedules status 'Đã hoàn thành'
        $users = User::whereBetween('id', [6, 106])
            ->whereHas('schedules', function ($query) {
                $query->where('status', Schedule::STATUS_COMPLETED);
            })
            ->get();

        foreach ($users as $user) {
            // Lấy các viewing_schedules có status 'Đã hoàn thành' của user
            $completedSchedules = $user->schedules()->where('status', Schedule::STATUS_COMPLETED)->get();

            // Random số lượng booking (1 hoặc 2)
            $bookingCount = rand(1, 2);

            for ($i = 0; $i < $bookingCount; $i++) {
                // Random một viewing_schedule từ danh sách
                $schedule = $completedSchedules->random();

                // Lấy motel từ schedule
                $motel = $schedule->motel;

                // Random một room từ motel, kiểm tra không có booking với status 'Chấp nhận'
                $room = null;
                $attempts = 0;
                $maxAttempts = $motel->rooms()->count(); // Số lần thử tối đa bằng số phòng

                while ($attempts < $maxAttempts) {
                    $potentialRoom = $motel->rooms()->inRandomOrder()->first();
                    if (!$potentialRoom) {
                        break; // Không có phòng nào trong motel
                    }

                    // Kiểm tra xem phòng có booking với status 'Chấp nhận' hay không
                    $hasAcceptedBooking = $potentialRoom->booking()->where('status', Booking::STATUS_ACCEPTED)->exists();
                    if (!$hasAcceptedBooking) {
                        $room = $potentialRoom;
                        break; // Phòng hợp lệ, thoát vòng lặp
                    }

                    $attempts++;
                }

                if (!$room) {
                    continue 2; // Bỏ qua toàn bộ booking cho user này, chuyển sang user khác
                }

                // Tạo start_date: scheduled_at + random 1-30 ngày
                $startDate = Carbon::parse($schedule->scheduled_at)->addDays(rand(1, 30));

                // Tạo end_date: start_date + random 1-5 năm
                $endDate = $startDate->copy()->addYears(rand(1, 5));

                // Tạo note ngẫu nhiên
                $notes = [
                    'Yêu cầu phòng sạch sẽ',
                    'Cần phòng có ban công',
                    'Mong muốn ký hợp đồng dài hạn',
                    'Cần hỗ trợ chuyển đồ',
                    ''
                ];
                $note = $notes[array_rand($notes)];

                // Logic tạo status
                if ($bookingCount == 1) {
                    // Nếu tạo 1 booking, status random giữa Chấp nhận, Từ chối, Huỷ bỏ
                    $statuses = [
                        Booking::STATUS_ACCEPTED,
                        Booking::STATUS_REFUSED,
                        Booking::STATUS_CANCELED
                    ];
                    $status = $statuses[array_rand($statuses)];
                } else {
                    // Nếu tạo 2 bookings
                    if ($i == 0) {
                        // Booking đầu tiên: status là Từ chối hoặc Huỷ bỏ
                        $statuses = [Booking::STATUS_REFUSED, Booking::STATUS_CANCELED];
                        $status = $statuses[array_rand($statuses)];
                    } else {
                        $status = Booking::STATUS_ACCEPTED;
                    }
                }

                // Tạo rejection_reason nếu status là Từ chối
                $rejectionReason = null;
                if ($status == Booking::STATUS_REFUSED) {
                    $rejectionReasons = [
                        'Rất tiếc, phòng đã được đặt bởi khách hàng khác. Xin vui lòng chọn phòng khác.',
                        'Yêu cầu đặt phòng chưa đáp ứng một số điều kiện của chúng tôi. Vui lòng liên hệ để được hỗ trợ thêm.',
                        'Hiện tại phòng đang trong quá trình bảo trì. Mong bạn thông cảm và chọn thời điểm khác.',
                        'Thông tin đặt phòng cần bổ sung thêm để được xét duyệt. Vui lòng kiểm tra lại.'
                    ];
                    $rejectionReason = $rejectionReasons[array_rand($rejectionReasons)];
                }

                // Tạo created_at và updated_at
                $createdAt = Carbon::createFromTimestamp(rand(
                    $schedule->scheduled_at->timestamp,
                    $startDate->timestamp
                ));

                // Tạo booking
                Booking::create([
                    'user_id' => $user->id,
                    'room_id' => $room->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'note' => $note,
                    'status' => $status,
                    'rejection_reason' => $rejectionReason,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}
