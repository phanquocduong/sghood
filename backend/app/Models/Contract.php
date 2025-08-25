<?php

namespace App\Models;

use App\Services\ContractService;
use Illuminate\Database\Eloquent\Model;

/**
 * Mô hình Contract đại diện cho bảng hợp đồng trong cơ sở dữ liệu.
 */
class Contract extends Model
{
    /**
     * Tên bảng liên kết với mô hình.
     *
     * @var string
     */
    protected $table = 'contracts';

    /**
     * Các trường có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'room_id', // ID của phòng liên quan đến hợp đồng
        'user_id', // ID của người dùng ký hợp đồng
        'booking_id', // ID của đặt phòng liên quan
        'start_date', // Ngày bắt đầu hợp đồng
        'end_date', // Ngày kết thúc hợp đồng
        'rental_price', // Giá thuê hàng tháng
        'deposit_amount', // Số tiền cọc
        'content', // Nội dung hợp đồng
        'signature', // Chữ ký của hợp đồng
        'status', // Trạng thái hợp đồng (Hoạt động, Kết thúc, ...)
        'file', // Đường dẫn file hợp đồng
        'signed_at', // Thời gian ký hợp đồng
        'early_terminated_at' // Thời gian kết thúc sớm (nếu có)
    ];

    /**
     * Ép kiểu dữ liệu cho các trường.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date', // Chuyển đổi thành đối tượng date
        'end_date' => 'date', // Chuyển đổi thành đối tượng date
        'rental_price' => 'integer', // Giá thuê là số nguyên
        'deposit_amount' => 'integer', // Tiền cọc là số nguyên
        'signed_at' => 'datetime', // Thời gian ký là datetime
        'early_terminated_at' => 'datetime', // Thời gian kết thúc sớm là datetime
        'created_at' => 'datetime', // Thời gian tạo là datetime
        'updated_at' => 'datetime', // Thời gian cập nhật là datetime
    ];

    /**
     * Quan hệ một-nhiều nghịch đảo với mô hình Room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Quan hệ một-nhiều nghịch đảo với mô hình User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Quan hệ một-nhiều nghịch đảo với mô hình Booking.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /**
     * Quan hệ một-nhiều với mô hình Invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'contract_id');
    }

    /**
     * Quan hệ một-nhiều với mô hình ContractExtension.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function extensions()
    {
        return $this->hasMany(ContractExtension::class, 'contract_id');
    }

    /**
     * Quan hệ một-nhiều với mô hình Checkout.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function checkouts()
    {
        return $this->hasMany(Checkout::class, 'contract_id');
    }

    /**
     * Kiểm tra xem hợp đồng có hóa đơn quá hạn hay không.
     *
     * @return bool
     */
    public function checkOverdueInvoices(): bool
    {
        return app(ContractService::class)->checkOverdueInvoices($this->id);
    }

    /**
     * Quan hệ một-nhiều với mô hình ContractTenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contractTenants()
    {
        return $this->hasMany(ContractTenant::class, 'contract_id');
    }
}
