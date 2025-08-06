<?php

namespace App\Jobs;

use App\Mail\CheckoutRefund;
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

class SendCheckoutRefundNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $checkout;
    protected $user;
    protected $room;
    protected $checkOutDate;
    protected $referenceCode;

    /**
     * Create a new job instance.
     */
    public function __construct(Checkout $checkout, $user, $room, $checkOutDate, $referenceCode)
    {
        $this->checkout = $checkout;
        $this->user = $user;
        $this->room = $room;
        $this->checkOutDate = $checkOutDate;
        $this->referenceCode = $referenceCode;
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
                Mail::to($this->user->email)->send(new CheckoutRefund(
                    $this->checkout,
                    $this->user->name,
                    $this->room->name,
                    $this->checkOutDate,
                    $finalRefundedAmount
                ));

                Log::info('Checkout refund email sent successfully', [
                    'email' => $this->user->email,
                    'checkout_id' => $this->checkout->id,
                    'final_refunded_amount' => $finalRefundedAmount,
                ]);
            }

            // Tạo thông báo trong database
            $notificationData = [
                'user_id' => $this->user->id,
                'title' => 'Xác nhận hoàn tiền thành công',
                'content' => 'Hoàn tiền cho phòng ' . $this->room->name . ' đã được xử lý. Số tiền: ' . number_format($finalRefundedAmount, 0, ',', '.') . ' VNĐ. Mã tham chiếu: ' . $this->referenceCode,
                'status' => 'Chưa đọc',
            ];

            $notification = Notification::create($notificationData);

            Log::info('Notification created for checkout refund', [
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
                    ->withData(['url' => 'https://sghood.com.vn/quan-ly/kiem-ke']);

                $messaging->send($fcmMessage);

                Log::info('FCM sent to user for refund confirmation', [
                    'user_id' => $this->user->id,
                    'checkout_id' => $this->checkout->id,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in SendCheckoutRefundNotification job', [
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
        Log::error('SendCheckoutRefundNotification job failed', [
            'checkout_id' => $this->checkout->id,
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
