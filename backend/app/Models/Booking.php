<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mô hình Booking đại diện cho bảng đặt phòng trong cơ sở dữ liệu.
 */
class Booking extends Model
{
    /**
     * Tên bảng liên kết với mô hình.
     *
     * @var string
     */
    protected $table = 'bookings';

    /**
     * Các trường có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', // ID của người dùng đặt phòng
        'room_id', // ID của phòng được đặt
        'start_date', // Ngày bắt đầu thuê
        'end_date', // Ngày kết thúc thuê
        'note', // Ghi chú của đặt phòng
        'rejection_reason', // Lý do từ chối (nếu có)
        'status', // Trạng thái của đặt phòng
    ];

    /**
     * Ép kiểu dữ liệu cho các trường.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date', // Chuyển đổi thành đối tượng date
        'end_date' => 'date', // Chuyển đổi thành đối tượng date
    ];

    // Hằng số định nghĩa các trạng thái đặt phòng
    const STATUS_PENDING = 'Chờ xác nhận';
    const STATUS_ACCEPTED = 'Chấp nhận';
    const STATUS_REFUSED = 'Từ chối';
    const STATUS_CANCELED = 'Huỷ bỏ';

    /**
     * Quan hệ một-nhiều nghịch đảo với mô hình User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ một-nhiều nghịch đảo với mô hình Room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Quan hệ một-một với mô hình Contract.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contract()
    {
        return $this->hasOne(Contract::class, 'booking_id');
    }
}
