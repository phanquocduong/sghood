<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\MeterReading;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InvoiceMonthlySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Lặp qua tất cả bản ghi MeterReading
        MeterReading::all()->each(function ($meterReading) {
            // Lấy thông tin contract và motel liên quan
            $contract = $meterReading->room->contract;
            $motel = $meterReading->room->motel;

            // Tính toán month và year cho invoice
            $month = $meterReading->month;
            $year = $meterReading->year;

            // Tính tỷ lệ cho tháng đầu hoặc tháng cuối
            $startDate = Carbon::parse($contract->start_date);
            $endDate = Carbon::parse($contract->end_date);
            $isFirstMonth = $meterReading->month === $startDate->month && $meterReading->year === $startDate->year;
            $isLastMonth = $meterReading->month === $endDate->month && $meterReading->year === $endDate->year && $endDate->lt(Carbon::now()->startOfMonth());

            $ratio = 1;
            if ($isFirstMonth) {
                $daysInMonth = $startDate->daysInMonth;
                $remainingDays = $daysInMonth - $startDate->day + 1;
                $ratio = $remainingDays / $daysInMonth;
            } elseif ($isLastMonth) {
                $daysInMonth = $endDate->daysInMonth;
                $remainingDays = $endDate->day;
                $ratio = $remainingDays / $daysInMonth;
            }

            // Tính room_fee
            $roomFee = $contract->rental_price * $ratio;

            // Tính electricity_fee
            $previousMonth = $meterReading->month - 1;
            $previousYear = $meterReading->year;
            if ($previousMonth == 0) {
                $previousMonth = 12;
                $previousYear -= 1;
            }
            $previousMeterReading = MeterReading::where('room_id', $meterReading->room_id)
                ->where('month', $previousMonth)
                ->where('year', $previousYear)
                ->first();
            $electricityKwh = $previousMeterReading
                ? ($meterReading->electricity_kwh - $previousMeterReading->electricity_kwh) * $ratio
                : $meterReading->electricity_kwh * $ratio;
            $electricityFee = $electricityKwh * $motel->electricity_fee;

            // Tính water_fee
            $waterM3 = $previousMeterReading
                ? ($meterReading->water_m3 - $previousMeterReading->water_m3) * $ratio
                : $meterReading->water_m3 * $ratio;
            $waterFee = $waterM3 * $motel->water_fee;

            // Tính các phí khác với tỷ lệ
            $parkingFee = $motel->parking_fee * $ratio;
            $junkFee = $motel->junk_fee * $ratio;
            $internetFee = $motel->internet_fee * $ratio;
            $serviceFee = $motel->service_fee * $ratio;

            // Tính total_amount
            $totalAmount = $roomFee + $electricityFee + $waterFee + $parkingFee + $junkFee + $internetFee + $serviceFee;

            // Tạo mã invoice duy nhất
            $code = 'INV' . $contract->id . $meterReading->created_at->format('YmdHis');

            // Gán trạng thái ngẫu nhiên cho tháng 7/2025
            $status = 'Đã trả';
            if ($month == 7 && $year == 2025) {
                $status = (mt_rand(1, 100) <= 80) ? 'Đã trả' : 'Chưa trả';
            }

            // Tạo bản ghi Invoice
            Invoice::create([
                'code' => $code,
                'contract_id' => $contract->id,
                'meter_reading_id' => $meterReading->id,
                'type' => 'Hàng tháng',
                'month' => $month,
                'year' => $year,
                'room_fee' => $roomFee,
                'electricity_fee' => $electricityFee,
                'water_fee' => $waterFee,
                'parking_fee' => $parkingFee,
                'junk_fee' => $junkFee,
                'internet_fee' => $internetFee,
                'service_fee' => $serviceFee,
                'total_amount' => $totalAmount,
                'status' => $status,
                'refunded_at' => null,
                'created_at' => $meterReading->created_at,
                'updated_at' => $meterReading->updated_at,
            ]);
        });
    }
}
