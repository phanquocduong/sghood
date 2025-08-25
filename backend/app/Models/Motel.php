<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Mô hình đại diện cho nhà trọ trong cơ sở dữ liệu.
 */
class Motel extends Model
{
    // Sử dụng trait SoftDeletes để hỗ trợ xóa mềm
    use SoftDeletes;

    /**
     * Tên bảng trong cơ sở dữ liệu.
     *
     * @var string
     */
    protected $table = 'motels';

    /**
     * Các cột có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'slug', // Slug của nhà trọ
        'name', // Tên nhà trọ
        'address', // Địa chỉ nhà trọ
        'district_id', // ID quận/huyện
        'map_embed_url', // URL bản đồ nhúng
        'description', // Mô tả nhà trọ
        'electricity_fee', // Phí điện
        'water_fee', // Phí nước
        'parking_fee', // Phí đỗ xe
        'junk_fee', // Phí vệ sinh
        'internet_fee', // Phí internet
        'service_fee', // Phí dịch vụ
        'status', // Trạng thái nhà trọ
    ];

    /**
     * Quan hệ với mô hình District.
     * Một nhà trọ thuộc về một quận/huyện.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    /**
     * Quan hệ với mô hình MotelImage.
     * Một nhà trọ có nhiều ảnh.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(MotelImage::class, 'motel_id', 'id');
    }

    /**
     * Lấy ảnh chính của nhà trọ.
     *
     * @return \App\Models\MotelImage|null Ảnh chính hoặc ảnh đầu tiên nếu không có ảnh chính
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
     * Một nhà trọ có nhiều tiện ích (quan hệ nhiều-nhiều).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'motel_amenities', 'motel_id', 'amenity_id');
    }

    /**
     * Quan hệ với mô hình Room.
     * Một nhà trọ có nhiều phòng.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
