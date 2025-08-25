<?php

namespace App\Mail\Apis;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Lớp Mailable xử lý gửi email thông báo về lịch xem nhà trọ.
 */
class ScheduleEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var mixed Lịch xem nhà trọ
     */
    public $schedule;

    /**
     * @var string Loại thông báo (pending/updated/canceled)
     */
    public $type;

    /**
     * @var string Tiêu đề email
     */
    public $title;

    /**
     * Khởi tạo email với dữ liệu lịch xem nhà trọ.
     *
     * @param mixed $schedule Lịch xem nhà trọ
     * @param string $type Loại thông báo
     * @param string $title Tiêu đề email
     */
    public function __construct($schedule, $type, $title)
    {
        $this->schedule = $schedule;
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
                    ->view('emails.apis.schedule_notification')
                    ->with(['type' => $this->type, 'title' => $this->title]);
    }
}
