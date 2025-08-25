<?php

namespace App\Mail\Apis;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Lớp Mailable xử lý việc gửi email thông báo liên quan đến đặt phòng.
 */
class BookingEmail extends Mailable
{
    // Sử dụng các trait để hỗ trợ hàng đợi và tuần tự hóa mô hình
    use Queueable, SerializesModels;

    // Thuộc tính công khai lưu trữ thông tin đặt phòng
    public $booking;
    // Thuộc tính công khai lưu trữ loại thông báo (pending, canceled)
    public $type;
    // Thuộc tính công khai lưu trữ tiêu đề email
    public $title;

    /**
     * Khởi tạo đối tượng email với thông tin đặt phòng, loại và tiêu đề.
     *
     * @param mixed $booking Mô hình đặt phòng
     * @param string $type Loại thông báo (pending, canceled)
     * @param string $title Tiêu đề email
     */
    public function __construct($booking, $type, $title)
    {
        $this->booking = $booking;
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
                    ->view('emails.apis.booking_notification') // Sử dụng template email
                    ->with(['type' => $this->type, 'title' => $this->title]); // Truyền dữ liệu vào view
    }
}
