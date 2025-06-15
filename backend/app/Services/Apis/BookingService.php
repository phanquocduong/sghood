<?php

namespace App\Services\Apis;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingService
{
    public function createBooking(array $data): Booking
    {
        $startDate = Carbon::parse($data['start_date']);
        $duration = (int) str_replace(' năm', '', $data['duration']);
        $endDate = $startDate->copy()->addYears($duration);

        return Booking::create([
            'user_id' =>  Auth::id(),
            'room_id' => $data['room_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'note' => $data['note'] ?? null,
            'status' => 'Chờ xác nhận'
        ]);
    }
}
