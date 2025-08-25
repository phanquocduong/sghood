<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Mô hình Amenity đại diện cho bảng tiện ích trong cơ sở dữ liệu.
 */
class Amenity extends Model
{
    // Sử dụng trait SoftDeletes để hỗ trợ xóa mềm (soft delete)
    use SoftDeletes;

    /**
     * Tên bảng liên kết với mô hình.
     *
     * @var string
     */
    protected $table = 'amenities';

    /**
     * Các trường có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'name', // Tên của tiện ích (ví dụ: Wifi, Điều hòa)
        'order', // Thứ tự sắp xếp của tiện ích
        'status', // Trạng thái của tiện ích (ví dụ: Hoạt động, Không hoạt động)
        'type', // Loại tiện ích (ví dụ: Nhà trọ, Chung cư)
    ];

    /**
     * Quan hệ nhiều-nhiều với mô hình Motel.
     * Liên kết thông qua bảng trung gian motel_amenities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function motels()
    {
        return $this->belongsToMany(
            Motel::class, // Mô hình liên quan
            'motel_amenities', // Tên bảng trung gian
            'amenity_id', // Khóa ngoại của Amenity
            'motel_id' // Khóa ngoại của Motel
        );
    }

    /**
     * Quan hệ nhiều-nhiều với mô hình Room.
     * Liên kết thông qua bảng trung gian room_amenities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function rooms()
    {
        return $this->belongsToMany(
            Room::class, // Mô hình liên quan
            'room_amenities', // Tên bảng trung gian
            'amenity_id', // Khóa ngoại của Amenity
            'room_id' // Khóa ngoại của Room
        );
    }
}
