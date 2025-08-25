<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Mô hình đại diện cho phòng trọ trong cơ sở dữ liệu.
 */
class Room extends Model
{
    // Sử dụng trait SoftDeletes để hỗ trợ xóa mềm
    use SoftDeletes;

    /**
     * Tên bảng trong cơ sở dữ liệu.
     *
     * @var string
     */
    protected $table = 'rooms';

    /**
     * Các cột có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'name', // Tên phòng
        'price', // Giá thuê phòng
        'area', // Diện tích phòng
        'max_occupants', // Số người tối đa
        'status', // Trạng thái phòng (Trống, Đã thuê, Ẩn)
        'motel_id', // ID nhà trọ
        'description', // Mô tả phòng
        'note' // Ghi chú (nếu có)
    ];

    /**
     * Quan hệ với mô hình Motel.
     * Một phòng thuộc về một nhà trọ.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function motel()
    {
        return $this->belongsTo(Motel::class, 'motel_id');
    }

    /**
     * Quan hệ với mô hình RoomImage.
     * Một phòng có nhiều ảnh.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(RoomImage::class, 'room_id', 'id');
    }

    /**
     * Lấy ảnh chính của phòng.
     *
     * @return \App\Models\RoomImage|null Ảnh chính hoặc ảnh đầu tiên nếu không có ảnh chính
     */
    public function getMainImageAttribute()
    {
        // Tìm ảnh có is_main = 1, nếu không có thì lấy ảnh đầu tiên
        $mainImage = $this->images->firstWhere('is_main', 1);
        if (!$mainImage && $this->images->count() > 0) {
            $mainImage = $this->images->first();
        }
        return $mainImage;
    }

    /**
     * Quan hệ với mô hình Amenity.
     * Một phòng có nhiều tiện ích (quan hệ nhiều-nhiều).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_amenities', 'room_id', 'amenity_id');
    }

    /**
     * Quan hệ với mô hình Booking.
     * Một phòng có nhiều đặt phòng.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function booking()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Quan hệ với mô hình Contract.
     * Một phòng có nhiều hợp đồng.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Quan hệ với mô hình Contract (hợp đồng mới nhất).
     * Một phòng có một hợp đồng mới nhất.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contract()
    {
        return $this->hasOne(Contract::class)->latest('id');
    }

    /**
     * Quan hệ với mô hình Contract (hợp đồng đang hoạt động).
     * Một phòng có một hợp đồng đang hoạt động.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function activeContract()
    {
        return $this->hasOne(Contract::class)->where('status', 'Hoạt động')->latest('id');
    }

    /**
     * Quan hệ với mô hình MeterReading.
     * Một phòng có nhiều bản ghi chỉ số đồng hồ.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function meterReadings()
    {
        return $this->hasMany(MeterReading::class, 'room_id');
    }
}
