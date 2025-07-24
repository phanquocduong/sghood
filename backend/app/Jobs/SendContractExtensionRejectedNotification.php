<?php

namespace App\Jobs;

use App\Mail\ContractExtensionRejectedNotification;
use App\Models\ContractExtension;
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

class SendContractExtensionRejectedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contractExtension;
    protected $rejectionReason;

    /**
     * Create a new job instance.
     */
    public function __construct(ContractExtension $contractExtension, $rejectionReason = null)
    {
        $this->contractExtension = $contractExtension;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = $this->contractExtension->contract->user;
            $room = $this->contractExtension->contract->room;

            if (!$user) {
                Log::warning('User not found for contract extension', [
                    'contract_extension_id' => $this->contractExtension->id
                ]);
                return;
            }

            // Gửi email
            if ($user->email) {
                Mail::to($user->email, $user->name)
                    ->send(new ContractExtensionRejectedNotification($this->contractExtension));

                Log::info('Contract extension rejection email sent successfully', [
                    'contract_extension_id' => $this->contractExtension->id,
                    'user_email' => $user->email,
                    'rejection_reason' => $this->rejectionReason
                ]);
            }

            // Tạo thông báo trong database
            $notificationData = [
                'user_id' => $user->id,
                'title' => 'Gia hạn hợp đồng bị từ chối',
                'content' => 'Yêu cầu gia hạn hợp đồng của bạn đã bị từ chối.' .
                           ($this->rejectionReason ? ' Lý do: ' . $this->rejectionReason : ' Lý do: Không có lý do cụ thể'),
                'status' => 'Chưa đọc',
            ];

            $notification = Notification::create($notificationData);

            Log::info('Notification created for contract extension rejection', [
                'notification_id' => $notification->getKey(),
                'contract_extension_id' => $this->contractExtension->id,
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

                Log::info('FCM sent to user for contract extension rejection', [
                    'user_id' => $user->id,
                    'contract_extension_id' => $this->contractExtension->id,
                ]);
            } else {
                Log::info('No FCM token found for user', [
                    'user_id' => $user->id,
                    'contract_extension_id' => $this->contractExtension->id,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in SendContractExtensionRejectedNotification job', [
                'contract_extension_id' => $this->contractExtension->id,
                'user_id' => $this->contractExtension->contract->user_id ?? null,
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
        Log::error('SendContractExtensionRejectedNotification job failed', [
            'contract_extension_id' => $this->contractExtension->id,
            'user_id' => $this->contractExtension->contract->user_id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}
