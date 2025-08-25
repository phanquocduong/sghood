<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mô hình đại diện cho yêu cầu sửa chữa trong cơ sở dữ liệu.
 */
class RepairRequest extends Model
{
    /**
     * Tên bảng trong cơ sở dữ liệu.
     *
     * @var string
     */
    protected $table = 'repair_requests';

    /**
     * Các cột có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'contract_id', // ID hợp đồng
        'title', // Tiêu đề yêu cầu
        'description', // Mô tả yêu cầu
        'images', // Chuỗi đường dẫn ảnh, cách nhau bởi |
        'status', // Trạng thái yêu cầu (Chờ xác nhận, Đang thực hiện, Hoàn thành, Huỷ bỏ)
        'note', // Ghi chú (nếu có)
        'repaired_at', // Thời điểm hoàn thành sửa chữa
    ];

    /**
     * Ép kiểu dữ liệu cho các cột.
     *
     * @var array
     */
    protected $casts = [
        'repaired_at' => 'datetime', // Ép kiểu thời điểm hoàn thành thành datetime
    ];

    /**
     * Quan hệ với mô hình Contract.
     * Một yêu cầu sửa chữa thuộc về một hợp đồng.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
