<?php

namespace App\Services;

use App\Models\MeterReading;
use App\Models\Room;
class MeterReadingService
{

    public function getAllMeterReadings(?string $search = null, int $perPage = 10)
    {
        return MeterReading::when($search, function ($query, $search) {
            // $search = request('search');
            return $query->where('room_id', 'like', "%{$search}%")
                ->orWhere('month', 'like', "%{$search}%")
                ->orWhere('year', 'like', "%{$search}%")
                ->orWhere('electricity_kwh', 'like', "%{$search}%")
                ->orWhere('water_m3', 'like', "%{$search}%")
                ->orWhereHas('room', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        })->paginate($perPage);
    }

    public function getRooms()
    {
        $today = now();

        // Khoảng thời gian cần loại trừ: từ ngày 28 tháng trước đến ngày 5 tháng sau
        $startDate = $today->copy()->subMonthNoOverflow()->day(28)->startOfDay();
        $endDate = $today->copy()->addMonthNoOverflow()->day(5)->endOfDay();

        // Lấy phòng đã thuê mà không có meter_readings nào trong khoảng trên
        $rooms = Room::where('status', 'Đã Thuê')
            ->whereDoesntHave('meterReadings', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        return $rooms;
    }


    public function createMeterReading(array $data)
    {
        // Validate and create a new meter reading
        $meterReading = MeterReading::create($data);
        return $meterReading;
    }

    // Add more methods as needed for your application
}