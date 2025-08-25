<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mô hình đại diện cho bản ghi chỉ số đồng hồ trong cơ sở dữ liệu.
 */
class MeterReading extends Model
{
    /**
     * Tên bảng trong cơ sở dữ liệu.
     *
     * @var string
     */
    protected $table = 'meter_readings';

    /**
     * Các cột có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'room_id', // ID phòng
        'month', // Tháng ghi chỉ số
        'year', // Năm ghi chỉ số
        'electricity_kwh', // Chỉ số điện (kWh)
        'water_m3', // Chỉ số nước (m3)
    ];

    /**
     * Ép kiểu dữ liệu cho các cột.
     *
     * @var array
     */
    protected $casts = [
        'electricity_kwh' => 'integer', // Chỉ số điện là số nguyên
        'water_m3' => 'integer', // Chỉ số nước là số nguyên
    ];

    /**
     * Quan hệ với mô hình Room.
     * Một bản ghi chỉ số đồng hồ thuộc về một phòng.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Quan hệ với mô hình Invoice.
     * Một bản ghi chỉ số đồng hồ có thể liên kết với một hóa đơn.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
