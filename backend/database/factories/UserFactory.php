<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserFactory extends Factory
{
    protected static ?Carbon $baseDate = null;

    public function definition(): array
    {
        // Thiết lập mốc thời gian bắt đầu nếu chưa có
        if (is_null(self::$baseDate)) {
            self::$baseDate = Carbon::now()->subYears(2);
        }

        // Cộng thêm khoảng ngẫu nhiên để đảm bảo tăng dần
        $increment = rand(1, 6); // ngày cộng thêm
        self::$baseDate->addDays($increment);

        $gender = $this->faker->randomElement(['Nam', 'Nữ', 'Khác']);
        $name = $this->faker->name(); // tiếng Việt
        $emailBase = Str::slug($name, '');

        return [
            'name' => $name,
            'phone' => '+84' . $this->faker->unique()->numerify('9########'),
            'email' => $emailBase . rand(100,999) . '@gmail.com',
            'password' => Hash::make('Baomat000@'),
            'gender' => $gender,
            'birthdate' => $this->faker->dateTimeBetween('-40 years', '-18 years')->format('Y-m-d'),
            'address' => $this->faker->address(),
            'role' => 'Người đăng ký',
            'status' => 'Hoạt động',
            'email_verified_at' => self::$baseDate->toDateTimeString(),
            'created_at' => self::$baseDate->toDateTimeString(),
            'updated_at' => self::$baseDate->toDateTimeString(),
            'avatar' => null,
            'identity_document' => null,
            'fcm_token' => null,
        ];
    }
}
