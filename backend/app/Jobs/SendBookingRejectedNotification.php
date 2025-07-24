<?php

namespace App\Jobs;

use App\Mail\BookingRejected;
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

class SendBookingRejectedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $booking;
    protected $rejectionReason;

    /**
     * Create a new job instance.
     */
    public function __construct(Booking $booking, $rejectionReason = '')
    {
        $this->booking = $booking;
        $this->rejectionReason = $rejectionReason;
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
                Mail::to($user->email)->send(new BookingRejected($this->booking, $this->rejectionReason));

                Log::info('Booking rejection email sent successfully', [
                    'booking_id' => $this->booking->id,
                    'user_email' => $user->email,
                    'rejection_reason' => $this->rejectionReason
                ]);
            }

            // Tạo thông báo trong database
            $notificationData = [
                'user_id' => $user->id,
                'title' => 'Đặt phòng đã bị từ chối',
                'content' => 'Đặt phòng của bạn tại ' . ($motel->name ?? 'N/A') . ' đã bị từ chối.' .
                           ($this->rejectionReason ? ' Lý do: ' . $this->rejectionReason : ''),
                'status' => 'Chưa đọc',
            ];

            $notification = Notification::create($notificationData);

            Log::info('Notification created for booking rejection', [
                'notification_id' => $notification->getKey(),
                'booking_id' => $this->booking->id,
                'user_id' => $user->id,
            ]);

            // Gửi thông báo đẩy FCM
            if ($user->fcm_token) {
                $messaging = app('firebase.messaging');
                $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                    ->withNotification(FirebaseNotification::create(
                        $notificationData['title'],
                        $notificationData['content']
                    ));

                $messaging->send($fcmMessage);

                Log::info('FCM sent to user for booking rejection', [
                    'user_id' => $user->id,
                    'booking_id' => $this->booking->id,
                ]);
            } else {
                Log::warning('No FCM token found for user', [
                    'user_id' => $user->id,
                    'booking_id' => $this->booking->id,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in SendBookingRejectedNotification job', [
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
        Log::error('SendBookingRejectedNotification job failed', [
            'booking_id' => $this->booking->id,
            'user_id' => $this->booking->user_id,
            'error' => $exception->getMessage(),
        ]);
    }
}
