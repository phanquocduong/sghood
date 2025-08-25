<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mô hình Notification đại diện cho bảng thông báo trong cơ sở dữ liệu.
 */
class Notification extends Model
{
    /**
     * Các trường có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', // ID của người dùng nhận thông báo
        'title', // Tiêu đề thông báo
        'content', // Nội dung thông báo
        'status', // Trạng thái thông báo (ví dụ: đã đọc, chưa đọc)
    ];

    /**
     * Quan hệ một-nhiều nghịch đảo với mô hình User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class); // Mỗi thông báo thuộc về một người dùng
    }
}
