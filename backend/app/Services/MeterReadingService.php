<?php

namespace App\Services;

use App\Models\MeterReading;
use App\Models\Room;
class MeterReadingService
{

    public function getAllMeterReadings()
    {
        // Logic to retrieve all meter readings
        return MeterReading::all();
    }

    public function getRooms()
    {
        // Lấy tháng và năm hiện tại
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Sử dụng leftJoin để tìm các phòng chưa có chỉ số đồng hồ tháng này
        $rooms = Room::where('rooms.status', 'Đã Thuê')
            ->leftJoin('meter_readings', function ($join) use ($currentMonth, $currentYear) {
                $join->on('rooms.id', '=', 'meter_readings.room_id')  // Thay đổi từ name thành id
                    ->where('meter_readings.month', '=', $currentMonth)
                    ->where('meter_readings.year', '=', $currentYear);
            })
            ->whereNull('meter_readings.id') // Lọc phòng chưa có chỉ số
            ->select('rooms.*') // Chỉ lấy thông tin từ bảng rooms
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