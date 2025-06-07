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

    public function getBookings(array $filters)
    {
        $query = Booking::query();
        $query->where('user_id', Auth::id());

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['sort'] === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($filters['sort'] === 'latest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($filters['sort'] === 'default') {
            $query->orderBy('created_at', 'desc');
        }

        // Eager load quan hệ 'room' và 'motel' của room
        return $query->with(['room.motel'])->get()->map(function ($booking) {
            return [
                'id' => $booking->id,
                'room_id' => $booking->room->id,
                'room_name' => $booking->room->name,
                'room_image' => $booking->room->main_image->image_url,
                'motel_name' => $booking->room->motel->name,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'note' => $booking->note,
                'status' => $booking->status,
            ];
        });
    }
}
