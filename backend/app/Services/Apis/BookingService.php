<?php

namespace App\Services\Apis;

use App\Jobs\Apis\SendBookingNotification;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class BookingService
{
    public function getBookings(array $filters): Collection
    {
        $query = Booking::query()
            ->where('user_id', Auth::id())
            ->with(['room.motel']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

        return $query->get()->map(function ($booking) {
            return [
                'id' => $booking->id,
                'room_id' => $booking->room->id,
                'room_name' => $booking->room->name,
                'room_image' => $booking->room->main_image->image_url,
                'motel_slug' => $booking->room->motel->slug,
                'motel_name' => $booking->room->motel->name,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'note' => $booking->note,
                'status' => $booking->status,
                'rejection_reason' => $booking->rejection_reason,
            ];
        })->values();
    }

    public function createBooking(array $data): Booking
    {
        $booking = DB::transaction(function () use ($data) {
            if (!Carbon::hasFormat($data['start_date'], 'd/m/Y')) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Định dạng ngày bắt đầu không hợp lệ. Vui lòng sử dụng định dạng DD/MM/YYYY.'
                ], 422));
            }

            $startDate = Carbon::createFromFormat('d/m/Y', $data['start_date']);
            $duration = (int) str_replace(' năm', '', $data['duration']);
            $endDate = $startDate->copy()->addYears($duration);

            if ($duration <= 0) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Thời gian thuê không hợp lệ.'
                ], 422));
            }

            $room = Room::find($data['room_id']);
            if (!$room) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Phòng không tồn tại.'
                ], 404));
            }
            $motelId = $room->motel_id;

            $existingBooking = Booking::where('user_id', $data['user_id'])
                ->whereHas('room', function ($query) use ($motelId) {
                    $query->where('motel_id', $motelId);
                })
                ->whereNotIn('status', [Booking::STATUS_REFUSED, Booking::STATUS_CANCELED])
                ->first();

            if ($existingBooking) {
                if ($existingBooking->status === Booking::STATUS_ACCEPTED) {
                    $contract = $existingBooking->contract;
                    if ($contract && Carbon::now()->lte($contract->end_date)) {
                        throw new HttpResponseException(response()->json([
                            'error' => 'Bạn đã có một hợp đồng thuê phòng còn hiệu lực trong nhà trọ này. Vui lòng chờ đến khi hợp đồng hết hạn hoặc hủy trước khi đặt phòng mới.'
                        ], 422));
                    }
                } else {
                    throw new HttpResponseException(response()->json([
                        'error' => 'Bạn đã có một đặt phòng chưa hoàn thành trong nhà trọ này. Vui lòng hoàn thành hoặc hủy trước khi đặt phòng mới.'
                    ], 422));
                }
            }

            return Booking::create([
                'user_id' => $data['user_id'],
                'room_id' => $data['room_id'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'note' => $data['note'] ?? null,
                'status' => Booking::STATUS_PENDING
            ]);
        });

        SendBookingNotification::dispatch(
            $booking,
            'pending',
            'Đặt phòng mới #' . $booking->id,
            "Người dùng {$booking->user->name} đã đặt {$booking->room->name} tại {$booking->room->motel->name} từ {$booking->start_date->format('d/m/Y')}."
        );

        return $booking;
    }

    public function cancelBooking($id): Booking
    {
        $booking = DB::transaction(function () use ($id) {
            $booking = Booking::findOrFail($id);
            $booking->update(['status' => Booking::STATUS_CANCELED]);
            return $booking;
        });

        SendBookingNotification::dispatch(
            $booking,
            'canceled',
            'Đặt phòng #' . $booking->id . ' đã bị hủy',
            "Người dùng {$booking->user->name} đã hủy đặt {$booking->room->name} tại {$booking->room->motel->name}."
        );

        return $booking;
    }

    protected function getSortOrder($sort): string
    {
        return match ($sort) {
            'oldest' => 'asc',
            'latest', 'default' => 'desc',
            default => 'desc',
        };
    }
}
