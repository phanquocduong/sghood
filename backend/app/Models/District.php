<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Mô hình đại diện cho quận/huyện trong cơ sở dữ liệu.
 */
class District extends Model
{
    // Sử dụng trait SoftDeletes để hỗ trợ xóa mềm
    use SoftDeletes;

    /**
     * Tên bảng trong cơ sở dữ liệu.
     *
     * @var string
     */
    protected $table = 'districts';

    /**
     * Các cột có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'name', // Tên quận
        'image', // Đường dẫn hình ảnh đại diện quận
    ];

    /**
     * Quan hệ với mô hình Motel.
     * Một quận có thể có nhiều nhà trọ.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function motels()
    {
        return $this->hasMany(Motel::class, 'district_id'); // Quan hệ một-nhiều với nhà trọ
    }
}
