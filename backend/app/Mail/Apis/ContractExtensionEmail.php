<?php

namespace App\Mail\Apis;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Lớp Mailable xử lý việc gửi email thông báo liên quan đến yêu cầu gia hạn hợp đồng.
 */
class ContractExtensionEmail extends Mailable
{
    // Sử dụng các trait để hỗ trợ hàng đợi và tuần tự hóa mô hình
    use Queueable, SerializesModels;

    // Thuộc tính công khai lưu trữ thông tin yêu cầu gia hạn
    public $contractExtension;
    // Thuộc tính công khai lưu trữ loại thông báo (pending, canceled)
    public $type;
    // Thuộc tính công khai lưu trữ tiêu đề email
    public $title;

    /**
     * Khởi tạo đối tượng email với thông tin yêu cầu gia hạn, loại và tiêu đề.
     *
     * @param mixed $contractExtension Mô hình yêu cầu gia hạn
     * @param string $type Loại thông báo
     * @param string $title Tiêu đề email
     */
    public function __construct($contractExtension, $type, $title)
    {
        $this->contractExtension = $contractExtension;
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
                    ->view('emails.apis.contract_extension_notification') // Sử dụng template email
                    ->with(['type' => $this->type, 'title' => $this->title]); // Truyền dữ liệu vào view
    }
}
