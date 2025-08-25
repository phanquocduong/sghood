<?php

namespace App\Mail\Apis;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Lớp Mailable xử lý gửi email thông báo về yêu cầu sửa chữa.
 */
class RepairRequestEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var mixed Yêu cầu sửa chữa
     */
    public $repairRequest;

    /**
     * @var string Loại thông báo (pending/canceled)
     */
    public $type;

    /**
     * @var string Tiêu đề email
     */
    public $title;

    /**
     * Khởi tạo email với dữ liệu yêu cầu sửa chữa.
     *
     * @param mixed $repairRequest Yêu cầu sửa chữa
     * @param string $type Loại thông báo
     * @param string $title Tiêu đề email
     */
    public function __construct($repairRequest, $type, $title)
    {
        $this->repairRequest = $repairRequest;
        $this->type = $type;
        $this->title = $title;
    }

    /**
     * Xây dựng nội dung email.
     *
     * @return $this
     */
    public function build()
    {
        // Cấu hình email với tiêu đề và view
        return $this->subject($this->title)
                    ->view('emails.apis.repair_request_notification')
                    ->with(['type' => $this->type, 'title' => $this->title]);
    }
}
