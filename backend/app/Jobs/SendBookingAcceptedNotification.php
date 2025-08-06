<?php

namespace App\Jobs;

use App\Mail\BookingAccepted;
use App\Models\Booking;
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

class SendBookingAcceptedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $booking;
    protected $contractUrl;

    /**
     * Create a new job instance.
     */
    public function __construct(Booking $booking, $contractUrl)
    {
        $this->booking = $booking;
        $this->contractUrl = $contractUrl;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = $this->booking->user;
            $room = $this->booking->room;
            $motel = $room->motel ?? null;

            if (!$user) {
                Log::warning('User not found for booking', ['booking_id' => $this->booking->id]);
                return;
            }

            // Gửi email
            if ($user->email) {
                Mail::to($user->email)->send(new BookingAccepted($this->booking, $this->contractUrl));

                Log::info('Booking acceptance email sent successfully', [
                    'booking_id' => $this->booking->id,
                    'user_email' => $user->email,
                    'contract_url' => $this->contractUrl
                ]);
            }

            // Tạo thông báo trong database
            $notificationData = [
                'user_id' => $user->id,
                'title' => 'Đặt phòng đã được chấp nhận',
                'content' => 'Đặt phòng của bạn tại ' . ($motel->name ?? 'N/A') . ' đã được chấp nhận. Vui lòng kiểm tra hợp đồng.',
                'status' => 'Chưa đọc',
            ];

            $notification = Notification::create($notificationData);

            Log::info('Notification created for booking acceptance', [
                'notification_id' => $notification->getKey(),
                'booking_id' => $this->booking->id,
                'user_id' => $user->id,
            ]);

            // Gửi thông báo đẩy FCM
            if ($user->fcm_token) {
                Log::info('⏳ Chuẩn bị gửi FCM', ['user_id' => $user->id, 'token' => $user->fcm_token]);

                $messaging = app('firebase.messaging');
                $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                    ->withNotification(FirebaseNotification::create(
                        $notificationData['title'],
                        $notificationData['content']
                    ))
                    ->withData(['url' => 'https://sghood.com.vn/quan-ly/dat-phong']);


                $messaging->send($fcmMessage);

                Log::info('✅ FCM sent to user for booking acceptance', [
                    'user_id' => $user->id,
                    'booking_id' => $this->booking->id,
                ]);
            } else {
                Log::warning('⚠️ Không tìm thấy user hoặc user chưa có fcm_token', [
                    'user_id' => $user->id,
                    'fcm_token' => $user->fcm_token ?? null
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in SendBookingAcceptedNotification job', [
                'booking_id' => $this->booking->id,
                'user_id' => $this->booking->user_id,
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
        Log::error('SendBookingAcceptedNotification job failed', [
            'booking_id' => $this->booking->id,
            'user_id' => $this->booking->user_id,
            'error' => $exception->getMessage(),
        ]);
    }
}
