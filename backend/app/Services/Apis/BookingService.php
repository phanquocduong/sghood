<?php

namespace App\Services\Apis;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Mail\BookingPendingEmail;
use App\Models\Notification;
use App\Models\Room;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class BookingService
{
    public function getBookings(array $filters)
    {
        $query = Booking::query()
            ->where('user_id', Auth::id())
            ->with(['room.motel']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

        $bookings = $query->get()->map(function ($booking) {
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
                'cancellation_reason' => $booking->cancellation_reason,
            ];
        });

        return $bookings->values();
    }

    public function createBooking(array $data)
    {
        try {
            // Kiểm tra định dạng start_date
            if (!Carbon::hasFormat($data['start_date'], 'd/m/Y')) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Định dạng ngày bắt đầu không hợp lệ. Vui lòng sử dụng định dạng DD/MM/YYYY.'
                ], 422));
            }

            $startDate = Carbon::createFromFormat('d/m/Y', $data['start_date']);
            $duration = (int) str_replace(' năm', '', $data['duration']);
            $endDate = $startDate->copy()->addYears($duration);

            // Kiểm tra duration hợp lệ
            if ($duration <= 0) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Thời gian thuê không hợp lệ.'
                ], 422));
            }

            // Lấy thông tin phòng để tìm motel_id
            $room = Room::find($data['room_id']);
            if (!$room) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Phòng không tồn tại.'
                ], 404));
            }
            $motelId = $room->motel_id;

            // Kiểm tra xem người dùng đã có đặt phòng nào trong cùng nhà trọ chưa
            $existingBooking = Booking::where('user_id', $data['user_id'])
                ->whereHas('room', function ($query) use ($motelId) {
                    $query->where('motel_id', $motelId);
                })
                ->whereNotIn('status', [Booking::STATUS_REFUSED, Booking::STATUS_CANCELED])
                ->first();

            if ($existingBooking) {
                // Nếu có đặt phòng với trạng thái 'Chấp nhận', kiểm tra hợp đồng
                if ($existingBooking->status === Booking::STATUS_ACCEPTED) {
                    $contract = $existingBooking->contract;
                    if ($contract && Carbon::now()->lte($contract->end_date)) {
                        throw new HttpResponseException(response()->json([
                            'error' => 'Bạn đã có một hợp đồng thuê phòng còn hiệu lực trong nhà trọ này. Vui lòng chờ đến khi hợp đồng hết hạn hoặc hủy trước khi đặt phòng mới.'
                        ], 422));
                    }
                } else {
                    // Nếu trạng thái là 'Chờ xác nhận' hoặc bất kỳ trạng thái nào khác ngoài 'Từ chối' và 'Huỷ bỏ'
                    throw new HttpResponseException(response()->json([
                        'error' => 'Bạn đã có một đặt phòng chưa hoàn thành trong nhà trọ này. Vui lòng hoàn thành hoặc hủy trước khi đặt phòng mới.'
                    ], 422));
                }
            }

            $booking = Booking::create([
                'user_id' => $data['user_id'],
                'room_id' => $data['room_id'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'note' => $data['note'] ?? null,
                'status' => Booking::STATUS_PENDING
            ]);

            $this->notifyAdmins($booking);

            return $booking;
        } catch (\Exception $e) {
            // Ghi log chi tiết lỗi trong service
            Log::error('Lỗi khi tạo booking', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $data['user_id'],
                'data' => $data
            ]);
            throw $e; // Ném lại lỗi để controller xử lý
        }
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
