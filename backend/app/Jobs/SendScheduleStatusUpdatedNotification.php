<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ScheduleStatusMail;
use App\Models\Schedule;
use App\Models\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class SendScheduleStatusUpdatedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $schedule;
    protected $oldStatus;
    protected $newStatus;


    /**
     * Create a new job instance.
     */
    public function __construct(Schedule $schedule, string $oldStatus, string $newStatus)
    {
        $this->schedule = $schedule;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = $this->schedule->user;

            if (!$user) {
                Log::warning('No user found for schedule', ['schedule_id' => $this->schedule->id]);
                return;
            }

            // Gửi email thông báo lịch hẹn được cập nhật
            if (!empty($user->email)) {
                try {
                    Mail::to($user->email)->send(new ScheduleStatusMail(
                        $this->schedule,
                        $this->oldStatus,
                        $this->newStatus
                    ));

                    Log::info('Schedule status update email sent', [
                        'email' => $user->email,
                        'schedule_id' => $this->schedule->id,
                        'job' => 'SendScheduleAcceptedNotification'
                    ]);
                } catch (\Exception $emailError) {
                    Log::error('Error sending schedule status email', [
                        'error' => $emailError->getMessage(),
                        'schedule_id' => $this->schedule->id,
                        'email' => $user->email,
                        'job' => 'SendScheduleAcceptedNotification'
                    ]);
                }
            } else {
                Log::warning('User has no email for schedule notification', [
                    'user_id' => $user->id,
                    'schedule_id' => $this->schedule->id
                ]);
            }

            // Gửi thông báo đến người dùng
            try {
                $title = 'Cập nhật trạng thái lịch hẹn';
                $content = "Trạng thái lịch hẹn của bạn đã được cập nhật từ '{$this->oldStatus}' sang '{$this->newStatus}'.";

                $notification = Notification::create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'content' => $content,
                    'status' => 'Chưa đọc'
                ]);

                Log::info('Notification created for schedule status update', [
                    'notification_id' => $notification->id,
                    'user_id' => $user->id,
                    'schedule_id' => $this->schedule->id
                ]);

                // Gửi FCM nếu có token
                if (!empty($user->fcm_token)) {
                    $messaging = app('firebase.messaging');

                    $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(FirebaseNotification::create($title, $content));

                    try {
                        $messaging->send($fcmMessage);
                        Log::info('FCM notification sent for schedule update', [
                            'user_id' => $user->id,
                            'schedule_id' => $this->schedule->id
                        ]);
                    } catch (\Exception $fcmError) {
                        Log::error('Error sending FCM for schedule update', [
                            'error' => $fcmError->getMessage(),
                            'user_id' => $user->id,
                            'schedule_id' => $this->schedule->id
                        ]);
                    }
                } else {
                    Log::info('No FCM token for user when sending schedule update', [
                        'user_id' => $user->id
                    ]);
                }

            } catch (\Exception $notifyError) {
                Log::error('Error creating notification for schedule update', [
                    'error' => $notifyError->getMessage(),
                    'schedule_id' => $this->schedule->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Unhandled exception in SendScheduleAcceptedNotification job', [
                'error' => $e->getMessage(),
                'schedule_id' => $this->schedule->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

}
