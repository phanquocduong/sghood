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

/**
 * Dịch vụ xử lý logic nghiệp vụ liên quan đến đặt phòng.
 */
class BookingService
{
    /**
     * Lấy danh sách đặt phòng của người dùng với các bộ lọc.
     *
     * @param array $filters Bộ lọc (sắp xếp, trạng thái)
     * @return Collection Danh sách đặt phòng đã được định dạng
     */
    public function getBookings(array $filters): Collection
    {
        // Tạo query lấy danh sách đặt phòng của người dùng hiện tại
        $query = Booking::query()
            ->where('user_id', Auth::id())
            ->with(['room.motel', 'contract']); // Nạp quan hệ room, motel và contract

        // Áp dụng bộ lọc trạng thái nếu có
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Sắp xếp theo ngày tạo (created_at) dựa trên bộ lọc sort
        $query->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

        // Lấy và định dạng dữ liệu đặt phòng
        return $query->get()->map(function ($booking) {
            return [
                'id' => $booking->id,
                'room_id' => $booking->room->id,
                'room_name' => $booking->room->name, // Tên phòng
                'room_image' => $booking->room->main_image->image_url, // URL hình ảnh chính của phòng
                'motel_slug' => $booking->room->motel->slug, // Slug của nhà trọ
                'motel_name' => $booking->room->motel->name, // Tên nhà trọ
                'start_date' => $booking->start_date, // Ngày bắt đầu thuê
                'end_date' => $booking->end_date, // Ngày kết thúc thuê
                'note' => $booking->note, // Ghi chú của đặt phòng
                'status' => $booking->status, // Trạng thái đặt phòng
                'rejection_reason' => $booking->rejection_reason, // Lý do từ chối (nếu có)
                'contract_id' => $booking->contract ? $booking->contract->id : null, // ID hợp đồng (nếu có)
            ];
        })->values();
    }

    /**
     * Tạo mới một đặt phòng.
     *
     * @param array $data Dữ liệu đặt phòng đã xác thực
     * @return Booking Mô hình đặt phòng đã được tạo
     */
    public function createBooking(array $data): Booking
    {
        // Sử dụng giao dịch để đảm bảo tính toàn vẹn dữ liệu
        $booking = DB::transaction(function () use ($data) {
            // Kiểm tra định dạng ngày bắt đầu
            if (!Carbon::hasFormat($data['start_date'], 'd/m/Y')) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Định dạng ngày bắt đầu không hợp lệ. Vui lòng sử dụng định dạng DD/MM/YYYY.'
                ], 422));
            }

            // Chuyển đổi ngày bắt đầu thành định dạng Carbon
            $startDate = Carbon::createFromFormat('d/m/Y', $data['start_date']);
            // Tính ngày kết thúc dựa trên thời gian thuê
            $duration = (int) str_replace(' năm', '', $data['duration']);
            $endDate = $startDate->copy()->addYears($duration);

            // Kiểm tra thời gian thuê hợp lệ
            if ($duration <= 0) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Thời gian thuê không hợp lệ.'
                ], 422));
            }

            // Kiểm tra sự tồn tại của phòng
            $room = Room::find($data['room_id']);
            if (!$room) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Phòng không tồn tại.'
                ], 404));
            }

            // Kiểm tra xem người dùng có hợp đồng còn hiệu lực không
            $existingActiveContract = Booking::where('user_id', $data['user_id'])
                ->whereHas('contract', function ($query) {
                    $query->where('status', 'Hoạt động')
                          ->where('end_date', '>=', Carbon::now());
                })
                ->first();

            if ($existingActiveContract) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Bạn đã có một hợp đồng thuê phòng còn hiệu lực. Vui lòng chờ đến khi hợp đồng hết hạn trước khi đặt phòng mới.'
                ], 422));
            }

            // Kiểm tra xem người dùng có đặt phòng đang chờ xử lý không
            $existingBooking = Booking::where('user_id', $data['user_id'])
                ->where('status', Booking::STATUS_PENDING)
                ->first();

            if ($existingBooking) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Bạn đã có một đặt phòng chưa hoàn thành. Vui lòng chờ admin xử lý hoặc hủy trước khi đặt phòng mới.'
                ], 422));
            }

            // Tạo đặt phòng mới
            return Booking::create([
                'user_id' => $data['user_id'],
                'room_id' => $data['room_id'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'note' => $data['note'] ?? null,
                'status' => Booking::STATUS_PENDING
            ]);
        });

        // Gửi thông báo đặt phòng mới đến quản trị viên
        SendBookingNotification::dispatch(
            $booking,
            'pending',
            'Đặt phòng mới #' . $booking->id,
            "Người dùng {$booking->user->name} đã đặt {$booking->room->name} tại {$booking->room->motel->name} từ {$booking->start_date->format('d/m/Y')}."
        );

        return $booking;
    }

    /**
     * Hủy một đặt phòng theo ID.
     *
     * @param int $id ID của đặt phòng cần hủy
     * @return Booking Mô hình đặt phòng đã được hủy
     */
    public function cancelBooking($id): Booking
    {
        // Sử dụng giao dịch để đảm bảo tính toàn vẹn dữ liệu
        $booking = DB::transaction(function () use ($id) {
            // Tìm đặt phòng theo ID, ném lỗi nếu không tồn tại
            $booking = Booking::findOrFail($id);
            // Cập nhật trạng thái thành "Hủy bỏ"
            $booking->update(['status' => Booking::STATUS_CANCELED]);
            return $booking;
        });

        // Gửi thông báo hủy đặt phòng đến quản trị viên
        SendBookingNotification::dispatch(
            $booking,
            'canceled',
            'Đặt phòng #' . $booking->id . ' đã bị hủy',
            "Người dùng {$booking->user->name} đã hủy đặt {$booking->room->name} tại {$booking->room->motel->name}."
        );

        return $booking;
    }

    /**
     * Xác định thứ tự sắp xếp dựa trên tham số sort.
     *
     * @param string $sort Tham số sắp xếp (oldest, latest, default)
     * @return string Kiểu sắp xếp (asc hoặc desc)
     */
    protected function getSortOrder($sort): string
    {
        return match ($sort) {
            'oldest' => 'asc',
            'latest', 'default' => 'desc',
            default => 'desc',
        };
    }
}
