<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Mô hình ContractTenant đại diện cho bảng thông tin người thuê trong hợp đồng.
 */
class ContractTenant extends Model
{
    // Sử dụng trait HasFactory để hỗ trợ tạo dữ liệu mẫu
    use HasFactory;

    /**
     * Tên bảng liên kết với mô hình.
     *
     * @var string
     */
    protected $table = 'contract_tenants';

    /**
     * Các trường có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'contract_id', // ID của hợp đồng liên quan
        'name', // Tên người thuê
        'phone', // Số điện thoại người thuê
        'email', // Email người thuê
        'gender', // Giới tính người thuê
        'birthdate', // Ngày sinh người thuê
        'address', // Địa chỉ người thuê
        'identity_document', // Giấy tờ tùy thân
        'relation_with_primary', // Quan hệ với người thuê chính
        'status', // Trạng thái người thuê (Đang ở, Đã rời đi, ...)
        'rejection_reason', // Lý do từ chối (nếu có)
    ];

    /**
     * Ép kiểu dữ liệu cho các trường.
     *
     * @var array
     */
    protected $casts = [
        'birthdate' => 'date', // Ngày sinh là date
        'gender' => 'string', // Giới tính là chuỗi
        'status' => 'string', // Trạng thái là chuỗi
    ];

    /**
     * Quan hệ một-nhiều nghịch đảo với mô hình Contract.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
}
