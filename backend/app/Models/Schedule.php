<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Mô hình đại diện cho lịch xem nhà trọ trong cơ sở dữ liệu.
 */
class Schedule extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu.
     *
     * @var string
     */
    protected $table = 'viewing_schedules';

    /**
     * Khóa chính của bảng.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Các cột có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', // ID người dùng
        'motel_id', // ID nhà trọ
        'scheduled_at', // Thời gian xem
        'message', // Lời nhắn
        'status', // Trạng thái lịch (Chờ xác nhận, Đã xác nhận, Từ chối, Hoàn thành, Huỷ bỏ)
        'rejection_reason' // Lý do từ chối (nếu có)
    ];

    /**
     * Ép kiểu dữ liệu cho các cột.
     *
     * @var array
     */
    protected $casts = [
        'scheduled_at' => 'datetime', // Ép kiểu thời gian xem thành datetime
    ];

    /**
     * Bật hỗ trợ tự động quản lý thời gian tạo/cập nhật.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Hằng số trạng thái lịch xem.
     */
    const STATUS_PENDING = 'Chờ xác nhận';
    const STATUS_CONFIRMED = 'Đã xác nhận';
    const STATUS_REFUSED = 'Từ chối';
    const STATUS_COMPLETED = 'Hoàn thành';
    const STATUS_CANCELED = 'Huỷ bỏ';

    /**
     * Quan hệ với mô hình User.
     * Một lịch xem thuộc về một người dùng.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Quan hệ với mô hình Motel.
     * Một lịch xem thuộc về một nhà trọ.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function motel()
    {
        return $this->belongsTo(Motel::class, 'motel_id');
    }
}
