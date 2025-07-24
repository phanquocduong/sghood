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
        $now = Carbon::now();

        // Lấy tất cả các contract có status 'Hoạt động'
        $contracts = Contract::where('status', 'Hoạt động')->get();

        foreach ($contracts as $contract) {
            $room_id = $contract->room_id;
            $start_date = Carbon::parse($contract->start_date);
            $end_date = Carbon::parse($contract->end_date);
            $current_date = $start_date->copy()->addMonth(1); // Bắt đầu từ tháng sau start_date
            $limit_date = $end_date < $now ? $end_date->copy()->addMonth(1) : $now->copy()->subMonth(1);

            $previous_electricity_kwh = 0; // Giá trị electricity_kwh của tháng trước
            $previous_water_m3 = 0; // Giá trị water_m3 của tháng trước
            $is_first_month = true; // Cờ để xác định tháng đầu tiên

            // Lặp qua từng tháng từ start_date + 1 đến giới hạn
            while ($current_date <= $limit_date) {
                $month = $current_date->month;
                $year = $current_date->year;

                // Tính giá trị electricity_kwh và water_m3
                if ($is_first_month) {
                    // Tính tỉ lệ số ngày sử dụng trong tháng đầu
                    $days_in_month = $start_date->copy()->endOfMonth()->diffInDays($start_date->copy()->startOfMonth()) + 1;
                    $days_used = $start_date->copy()->endOfMonth()->diffInDays($start_date) + 1;
                    $ratio = $days_used / $days_in_month;

                    // Tạo giá trị ngẫu nhiên thấp hơn cho tháng đầu, dựa trên tỉ lệ
                    $electricity_kwh = $faker->randomFloat(2, 1, 250) * $ratio;
                    $water_m3 = $faker->randomFloat(2, 1, 20) * $ratio;

                    $is_first_month = false; // Đặt lại cờ sau tháng đầu
                } else {
                    // Các tháng sau tăng ngẫu nhiên dựa trên giá trị tháng trước
                    $electricity_kwh = $previous_electricity_kwh + $faker->randomFloat(2, 1, 250);
                    $water_m3 = $previous_water_m3 + $faker->randomFloat(2, 1, 20);
                }

                // Tạo thời gian created_at, updated_at vào cuối tháng trước
                $created_at = Carbon::create($year, $month, 1)->subMonth(1)->endOfMonth();

                MeterReading::create([
                    'room_id' => $room_id,
                    'month' => $month,
                    'year' => $year,
                    'electricity_kwh' => $electricity_kwh,
                    'water_m3' => $water_m3,
                    'created_at' => $created_at,
                    'updated_at' => $created_at,
                ]);

                // Cập nhật giá trị electricity_kwh và water_m3 cho tháng tiếp theo
                $previous_electricity_kwh = $electricity_kwh;
                $previous_water_m3 = $water_m3;

                // Tăng tháng
                $current_date->addMonth(1);
            }
        }
    }
}
