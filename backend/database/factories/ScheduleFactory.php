<?php

namespace Database\Factories;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $rawScheduledAt = $this->faker->boolean(50)
            ? $this->faker->dateTimeBetween('-2 years', 'now')
            : $this->faker->dateTimeBetween('now', '+2 months');

        $hour = $this->faker->numberBetween(8, 17);
        $scheduledAt = Carbon::instance($rawScheduledAt)->setTime($hour, 0, 0);

        if ($scheduledAt < now()) {
            $statusList = [
                Schedule::STATUS_REFUSED,
                Schedule::STATUS_COMPLETED,
                Schedule::STATUS_COMPLETED,
                Schedule::STATUS_CANCELED,
                Schedule::STATUS_COMPLETED,
                Schedule::STATUS_COMPLETED,
            ];
        } else {
            $statusList = [
                Schedule::STATUS_PENDING,
                Schedule::STATUS_CONFIRMED,
                Schedule::STATUS_REFUSED,
                Schedule::STATUS_CANCELED,
            ];
        }

        $status = $this->faker->randomElement($statusList);

        // created_at: lùi trong khoảng 0–7 ngày trước scheduled_at
        $createdAt = (clone $scheduledAt)->subDays(rand(0, 7))->setTime(
            $this->faker->numberBetween(8, 17),
            $this->faker->randomElement([0, 15, 30, 45])
        );

        // updated_at: bằng hoặc sau created_at một chút
        $updatedAt = (clone $createdAt)->addDays(rand(0, 2));

        return [
            'user_id' => $this->faker->numberBetween(6, 105),
            'motel_id' => $this->faker->numberBetween(1, 13),
            'scheduled_at' => $scheduledAt,

            'message' => $this->faker->optional()->randomElement([
                'Tôi muốn xem phòng vào chiều mai.',
                'Xin đặt lịch xem phòng vào cuối tuần.',
                'Có thể cho tôi xem phòng lúc 9h sáng không?',
                'Quan tâm đến phòng này, mong được hẹn lịch xem.',
                'Tôi rảnh vào thứ 5 tuần này, cho tôi lịch nhé.',
                'Tôi muốn xem trước khi quyết định thuê.',
            ]),

            'status' => $status,

            'rejection_reason' => $status === Schedule::STATUS_REFUSED
                ? $this->faker->randomElement([
                    'Thời gian bạn chọn hiện đã có lịch hẹn khác, mong bạn thông cảm.',
                    'Rất tiếc, phòng đã được ưu tiên cho khách đặt trước.',
                    'Hiện nhà trọ đang tạm ngưng tiếp khách xem.',
                    'Xin lỗi, chúng tôi chưa thể sắp xếp lịch vào thời điểm mong muốn.',
                ])
                : null,

            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }
}
