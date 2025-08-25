<?php

namespace App\Mail\Apis;

use App\Models\Contract;
use App\Models\ContractTenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Lớp Mailable xử lý việc gửi email thông báo liên quan đến người ở cùng hợp đồng.
 */
class ContractTenantEmail extends Mailable
{
    // Sử dụng các trait để hỗ trợ hàng đợi và tuần tự hóa mô hình
    use Queueable, SerializesModels;

    // Thuộc tính công khai lưu trữ thông tin hợp đồng
    public $contract;
    // Thuộc tính công khai lưu trữ thông tin người ở cùng
    public $tenant;
    // Thuộc tính công khai lưu trữ loại thông báo (tenant_added, tenant_canceled, tenant_confirmed)
    public $type;
    // Thuộc tính công khai lưu trữ tiêu đề email
    public $title;

    /**
     * Khởi tạo đối tượng email với thông tin hợp đồng, người ở cùng, loại và tiêu đề.
     *
     * @param Contract $contract Mô hình hợp đồng
     * @param ContractTenant $tenant Mô hình người ở cùng
     * @param string $type Loại thông báo
     * @param string $title Tiêu đề email
     */
    public function __construct(Contract $contract, ContractTenant $tenant, string $type, string $title)
    {
        $this->contract = $contract;
        $this->tenant = $tenant;
        $this->type = $type;
        $this->title = $title;
    }

    /**
     * Cấu hình nội dung email thông báo.
     *
     * @return $this Đối tượng Mailable đã được cấu hình
     */
    public function build()
    {
        // Thiết lập tiêu đề email và sử dụng view để hiển thị nội dung
        return $this->subject($this->title)
                    ->view('emails.apis.contract_tenant_notification')
                    ->with(['type' => $this->type, 'title' => $this->title]); // Truyền dữ liệu vào view
    }
}
