<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CheckoutAutoConfirmedMail;
use App\Models\Checkout;
use App\Models\User;
use App\Models\Room;
use App\Models\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class SendCheckoutAutoConfirmedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $checkout;
    public $user;
    public $room;

    /**
     * Create a new job instance.
     */
    public function __construct(Checkout $checkout, User $user, Room $room)
    {
        $this->checkout = $checkout;
        $this->user = $user;
        $this->room = $room;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Gửi email thông báo tự động xác nhận
            $email = $this->user->email ?? null;
            if ($email) {
                try {
                    Mail::to($email)->send(new CheckoutAutoConfirmedMail($this->checkout, $this->user, $this->room));
                    Log::info('Checkout auto-confirmed email sent successfully', [
                        'email' => $email,
                        'checkout_id' => $this->checkout->id,
                        'user_id' => $this->user->id,
                        'job' => 'SendCheckoutAutoConfirmedNotification'
                    ]);
                } catch (\Exception $emailError) {
                    Log::error('Error sending checkout auto-confirmed email', [
                        'error' => $emailError->getMessage(),
                        'checkout_id' => $this->checkout->id,
                        'email' => $email,
                        'job' => 'SendCheckoutAutoConfirmedNotification'
                    ]);
                }
            } else {
                Log::warning('No email found for user in auto-confirmed checkout', [
                    'checkout_id' => $this->checkout->id,
                    'user_id' => $this->user->id,
                    'job' => 'SendCheckoutAutoConfirmedNotification'
                ]);
            }

            // Gửi thông báo đến người dùng (lưu vào database)
            try {
                $notificationTitle = 'Tự động xác nhận kiểm kê phòng';
                $notificationContent = "Kiểm kê phòng {$this->room->name} đã được tự động xác nhận do quá hạn 7 ngày không phản hồi. Bạn có thể tiến hành hoàn tiền.";

                $notificationData = [
                    'user_id' => $this->user->id,
                    'title' => $notificationTitle,
                    'content' => $notificationContent,
                    'status' => 'Chưa đọc',
                    'type' => 'checkout_auto_confirmed',
                    'data' => json_encode([
                        'checkout_id' => $this->checkout->id,
                        'room_name' => $this->room->name,
                        'room_id' => $this->room->id,
                        'final_refunded_amount' => $this->checkout->final_refunded_amount,
                        'deduction_amount' => $this->checkout->deduction_amount,
                        'deposit_amount' => $this->checkout->contract->deposit_amount ?? 0,
                        'auto_confirmed_reason' => 'Quá hạn 7 ngày không phản hồi',
                        'action_url' => url("/checkouts/{$this->checkout->id}")
                    ])
                ];

                $notification = Notification::create($notificationData);
                Log::info('Notification created for checkout auto-confirmation', [
                    'checkout_id' => $this->checkout->id,
                    'user_id' => $this->user->id,
                    'notification_id' => $notification->id,
                    'job' => 'SendCheckoutAutoConfirmedNotification'
                ]);

                // Gửi FCM notification nếu có token
                if ($this->user->fcm_token) {
                    $messaging = app('firebase.messaging');

                    $fcmMessage = CloudMessage::withTarget('token', $this->user->fcm_token)
                        ->withNotification(
                            FirebaseNotification::create($notificationTitle, $notificationContent)
                        )
                        ->withData([
                            'type' => 'checkout_auto_confirmed',
                            'checkout_id' => (string)$this->checkout->id,
                            'room_name' => $this->room->name,
                            'final_refunded_amount' => (string)$this->checkout->final_refunded_amount,
                            'auto_confirmed_reason' => 'Quá hạn 7 ngày không phản hồi',
                            'action_url' => url("/checkouts/{$this->checkout->id}")
                        ]);

                    $messaging->send($fcmMessage);
                    Log::info('FCM notification sent for checkout auto-confirmation', [
                        'checkout_id' => $this->checkout->id,
                        'user_id' => $this->user->id,
                        'fcm_token' => substr($this->user->fcm_token, 0, 20) . '...',
                        'job' => 'SendCheckoutAutoConfirmedNotification'
                    ]);
                }

            } catch (\Exception $notificationError) {
                Log::error('Error creating notification for checkout auto-confirmation', [
                    'error' => $notificationError->getMessage(),
                    'checkout_id' => $this->checkout->id,
                    'user_id' => $this->user->id,
                    'job' => 'SendCheckoutAutoConfirmedNotification'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in SendCheckoutAutoConfirmedNotification job', [
                'error' => $e->getMessage(),
                'checkout_id' => $this->checkout->id,
                'user_id' => $this->user->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendCheckoutAutoConfirmedNotification job failed', [
            'checkout_id' => $this->checkout->id,
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
