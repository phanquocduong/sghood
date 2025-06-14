<?php

namespace App\Mail;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScheduleStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $schedule;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new message instance.
     *
     * @param Schedule $schedule
     * @param string $oldStatus
     * @param string $newStatus
     * @return void
     */
    public function __construct(Schedule $schedule, string $oldStatus, string $newStatus)
    {
        $this->schedule = $schedule;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "Cập nhật trạng thái lịch xem phòng - " . $this->schedule->room->name;
        
        return $this->subject($subject)
                    ->view('emails.schedule-status-updated');
    }
}