<?php

namespace App\Services\Apis;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Mail\BookingPendingEmail;
use App\Models\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class BookingService
{
    public function getBookings(array $filters)
    {
        $query = Booking::query()
            ->where('user_id', Auth::id())
            ->with(['room.motel'])
            ->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

        $bookings = $query->get()->map(function ($booking) {
            return [
                'id' => $booking->id,
                'room_id' => $booking->room->id,
                'room_name' => $booking->room->name,
                'room_image' => $booking->room->main_image->image_url,
                'motel_name' => $booking->room->motel->name,
                'start_date' => $booking->start_date->toIso8601String(),
                'end_date' => $booking->end_date->toIso8601String(),
                'note' => $booking->note,
                'status' => $booking->status,
                'cancellation_reason' => $booking->cancellation_reason,
                'created_at' => $booking->created_at->toIso8601String(),
            ];
        });

        $sortOrder = $this->getSortOrder($filters['sort'] ?? 'default');
        if ($sortOrder === 'asc') {
            $bookings = $bookings->sortBy('created_at');
        } else {
            $bookings = $bookings->sortByDesc('created_at');
        }

        return $bookings->values();
    }

    public function createBooking(array $data)
    {
        $startDate = Carbon::createFromFormat('d/m/Y', $data['start_date']);
        $duration = (int) str_replace(' năm', '', $data['duration']);
        $endDate = $startDate->copy()->addYears($duration);

        $booking = Booking::create([
            'user_id' => $data['user_id'],
            'room_id' => $data['room_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'note' => $data['note'] ?? null,
            'status' => 'Chờ xác nhận'
        ]);

        $this->notifyAdmins($booking);

        return $booking;
    }

    public function rejectBooking($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => Booking::STATUS_CANCELED]);
        return $booking;
    }

    protected function getSortOrder($sort)
    {
        return match ($sort) {
            'oldest' => 'asc',
            'latest', 'default' => 'desc',
            default => 'desc',
        };
    }

    private function notifyAdmins($booking)
    {
        try {
            $admins = User::where('role', 'Quản trị viên')->get();

            if ($admins->isEmpty()) {
                Log::warning('Không tìm thấy admin với role Quản trị viên');
                return;
            }

            $title = 'Đặt phòng mới';
            $body = "Đặt phòng #{$booking->id} từ người dùng {$booking->user->name} đang chờ xác nhận.";

            Mail::to($admins->pluck('email'))->send(new BookingPendingEmail($booking));

            $messaging = app('firebase.messaging');

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $title,
                    'content' => $body,
                ]);

                if ($admin->fcm_token) {
                    $baseUrl = config('app.url');
                    $link = "$baseUrl/bookings";
                    $message = CloudMessage::fromArray([
                        'token' => $admin->fcm_token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data' => [
                            'link' => $link,
                        ],
                    ]);

                    $messaging->send($message);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Lỗi gửi thông báo cho admin', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
