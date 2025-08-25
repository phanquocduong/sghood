<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mô hình đại diện cho giao dịch thanh toán trong cơ sở dữ liệu.
 */
class Transaction extends Model
{
    /**
     * Các cột có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_id', // ID hóa đơn
        'transaction_date', // Ngày giao dịch
        'content', // Nội dung giao dịch
        'transfer_type', // Loại chuyển khoản
        'transfer_amount', // Số tiền chuyển khoản
        'reference_code' // Mã tham chiếu
    ];

    /**
     * Quan hệ với mô hình Invoice.
     * Một giao dịch thuộc về một hóa đơn.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
