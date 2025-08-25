<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mô hình đại diện cho hóa đơn trong cơ sở dữ liệu.
 */
class Invoice extends Model
{
    /**
     * Các cột có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'code', // Mã hóa đơn
        'contract_id', // ID hợp đồng
        'meter_reading_id', // ID chỉ số đồng hồ
        'type', // Loại hóa đơn (Đặt cọc, Hàng tháng)
        'month', // Tháng hóa đơn
        'year', // Năm hóa đơn
        'room_fee', // Phí thuê phòng
        'electricity_fee', // Phí điện
        'water_fee', // Phí nước
        'parking_fee', // Phí đỗ xe
        'junk_fee', // Phí vệ sinh
        'internet_fee', // Phí internet
        'service_fee', // Phí dịch vụ
        'refunded_at', // Thời điểm hoàn tiền (nếu có)
        'total_amount', // Tổng số tiền
        'status' // Trạng thái hóa đơn (Chưa trả, Đã trả)
    ];

    /**
     * Quan hệ với mô hình Contract.
     * Một hóa đơn thuộc về một hợp đồng.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Quan hệ với mô hình MeterReading.
     * Một hóa đơn có thể liên kết với một bản ghi chỉ số đồng hồ.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function meterReading()
    {
        return $this->belongsTo(MeterReading::class);
    }
}
