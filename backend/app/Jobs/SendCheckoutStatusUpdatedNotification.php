<?php

namespace App\Jobs;

use App\Mail\CheckoutStatusUpdated;
use App\Models\Checkout;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class SendCheckoutStatusUpdatedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $checkout;
    protected $user;
    protected $room;
    protected $checkOutDate;

    /**
     * Create a new job instance.
     */
    public function __construct(Checkout $checkout, $user, $room, $checkOutDate)
    {
        $this->checkout = $checkout;
        $this->user = $user;
        $this->room = $room;
        $this->checkOutDate = $checkOutDate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $finalRefundedAmount = $this->checkout->final_refunded_amount;

            // Gửi email
            if ($this->user->email) {
                Mail::to($this->user->email)->send(new CheckoutStatusUpdated(
                    $this->checkout,
                    $this->user->name,
                    $this->room->name,
                    $this->checkOutDate,
                    $finalRefundedAmount
                ));

                Log::info('Checkout status email sent successfully', [
                    'email' => $this->user->email,
                    'checkout_id' => $this->checkout->id,
                    'final_refunded_amount' => $finalRefundedAmount,
                ]);
            }

            // Tạo thông báo trong database
            $notificationData = [
                'user_id' => $this->user->id,
                'title' => 'Trạng thái kiểm kê đã được cập nhật',
                'content' => 'Quá trình kiểm kê cho phòng ' . $this->room->name . ' đã hoàn tất. Số tiền hoàn trả: ' . number_format($finalRefundedAmount, 0, ',', '.') . ' VNĐ. Vui lòng xem chi tiết.',
                'status' => 'Chưa đọc',
            ];

            $notification = Notification::create($notificationData);

            Log::info('Notification created for checkout', [
                'notification_id' => $notification->getKey(),
                'checkout_id' => $this->checkout->id,
                'user_id' => $this->user->id,
            ]);

            // Gửi thông báo đẩy FCM
            if ($this->user->fcm_token) {
                $messaging = app('firebase.messaging');
                $fcmMessage = CloudMessage::withTarget('token', $this->user->fcm_token)
                    ->withNotification(FirebaseNotification::create(
                        $notificationData['title'],
                        $notificationData['content']
                    ))
                    ->withData(['url' => 'http://127.0.0.1:3000/quan-ly/kiem-ke']);

                $messaging->send($fcmMessage);

                Log::info('FCM sent to user', [
                    'user_id' => $this->user->id,
                    'checkout_id' => $this->checkout->id,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in SendCheckoutStatusUpdatedNotification job', [
                'checkout_id' => $this->checkout->id,
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw exception để job có thể được retry
            throw $e;
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendCheckoutStatusUpdatedNotification job failed', [
            'checkout_id' => $this->checkout->id,
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
