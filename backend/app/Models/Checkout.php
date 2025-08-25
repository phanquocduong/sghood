<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Mô hình Checkout đại diện cho bảng yêu cầu trả phòng trong cơ sở dữ liệu.
 */
class Checkout extends Model
{
    // Sử dụng trait HasFactory để hỗ trợ tạo dữ liệu mẫu
    use HasFactory;

    /**
     * Các trường có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'contract_id', // ID của hợp đồng liên quan
        'check_out_date', // Ngày dự kiến trả phòng
        'inventory_details', // Chi tiết kiểm kê
        'deduction_amount', // Số tiền khấu trừ
        'final_refunded_amount', // Số tiền hoàn cuối cùng
        'inventory_status', // Trạng thái kiểm kê
        'user_confirmation_status', // Trạng thái xác nhận của người dùng
        'user_rejection_reason', // Lý do từ chối của người dùng
        'has_left', // Trạng thái đã rời phòng
        'images', // Hình ảnh liên quan
        'note', // Ghi chú
        'bank_info', // Thông tin ngân hàng
        'refund_status', // Trạng thái hoàn tiền
        'canceled_at', // Thời gian hủy yêu cầu
    ];

    /**
     * Ép kiểu dữ liệu cho các trường.
     *
     * @var array
     */
    protected $casts = [
        'inventory_details' => 'array', // Chi tiết kiểm kê là mảng
        'images' => 'array', // Hình ảnh là mảng
        'bank_info' => 'array', // Thông tin ngân hàng là mảng
        'check_out_date' => 'date', // Ngày trả phòng là date
        'has_left' => 'boolean', // Trạng thái rời phòng là boolean
        'canceled_at' => 'datetime', // Thời gian hủy là datetime
    ];

    /**
     * Quan hệ một-nhiều nghịch đảo với mô hình Contract.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
