<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\MeterReading;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class MeterReadingSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $now = Carbon::now()->startOfMonth();

        $contracts = Contract::all();

        foreach ($contracts as $contract) {
            $room_id = $contract->room_id;
            $start_date = Carbon::parse($contract->start_date)->startOfMonth();
            $end_date = Carbon::parse($contract->end_date)->startOfMonth();
            $current_date = $start_date->copy();
            $limit_date = $end_date < $now ? $end_date : $now->copy()->subMonth(1);

            $previous_electricity_kwh = 0;
            $previous_water_m3 = 0;
            $is_first_month = true;

            // Lặp qua từng tháng từ start_date đến limit_date
            while ($current_date->year < $limit_date->year || ($current_date->year == $limit_date->year && $current_date->month <= $limit_date->month)) {
                $month = $current_date->month;
                $year = $current_date->year;

                // Kiểm tra xem đây có phải là tháng cuối cùng không
                $is_last_month = ($current_date->year == $end_date->year && $current_date->month == $end_date->month && $end_date < $now);

                // Tính giá trị electricity_kwh và water_m3
                if ($is_first_month || $is_last_month) {
                    // Tính tỉ lệ số ngày sử dụng
                    if ($is_first_month) {
                        $days_in_month = $start_date->copy()->endOfMonth()->diffInDays($start_date->copy()->startOfMonth()) + 1;
                        $days_used = $start_date->copy()->endOfMonth()->diffInDays($start_date) + 1;
                    } else {
                        // Tháng cuối cùng
                        $days_in_month = $end_date->copy()->endOfMonth()->diffInDays($end_date->copy()->startOfMonth()) + 1;
                        $days_used = $end_date->copy()->diffInDays($end_date->copy()->startOfMonth()) + 1;
                    }
                    $ratio = $days_used / $days_in_month;

                    // Tính giá trị ngẫu nhiên đầy đủ trước, sau đó nhân tỉ lệ
                    $electricity_increment = $faker->randomFloat(2, 1, 250);
                    $water_increment = $faker->randomFloat(2, 1, 20);

                    $electricity_kwh = $previous_electricity_kwh + ($electricity_increment * $ratio);
                    $water_m3 = $previous_water_m3 + ($water_increment * $ratio);

                    if ($is_first_month) {
                        $is_first_month = false;
                    }
                } else {
                    $electricity_kwh = $previous_electricity_kwh + $faker->randomFloat(2, 1, 250);
                    $water_m3 = $previous_water_m3 + $faker->randomFloat(2, 1, 20);
                }

                // Đảm bảo chỉ số không nhỏ hơn tháng trước
                $electricity_kwh = max($electricity_kwh, $previous_electricity_kwh);
                $water_m3 = max($water_m3, $previous_water_m3);

                // Tạo thời gian created_at, updated_at vào cuối tháng
                $created_at = Carbon::create($year, $month, 1)->endOfMonth();

                MeterReading::create([
                    'room_id' => $room_id,
                    'month' => $month,
                    'year' => $year,
                    'electricity_kwh' => $electricity_kwh,
                    'water_m3' => $water_m3,
                    'created_at' => $created_at,
                    'updated_at' => $created_at,
                ]);

                // Cập nhật giá trị cho tháng tiếp theo
                $previous_electricity_kwh = $electricity_kwh;
                $previous_water_m3 = $water_m3;

                // Tăng tháng
                $current_date->addMonth(1);
            }
        }
    }
}
