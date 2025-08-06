<?php

namespace App\Jobs;

use App\Mail\ContractExtensionApprovedNotification;
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

class SendContractExtensionApprovedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contractExtension;

    /**
     * Create a new job instance.
     */
    public function __construct(ContractExtension $contractExtension)
    {
        $this->contractExtension = $contractExtension;
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
                    ->send(new ContractExtensionApprovedNotification($this->contractExtension));

                Log::info('Contract extension approval email sent successfully', [
                    'contract_extension_id' => $this->contractExtension->id,
                    'user_email' => $user->email
                ]);
            }

            // Tạo thông báo trong database
            $notificationData = [
                'user_id' => $user->id,
                'title' => 'Gia hạn hợp đồng đã được phê duyệt',
                'content' => 'Yêu cầu gia hạn hợp đồng của bạn đã được phê duyệt.',
                'status' => 'Chưa đọc',
            ];

            $notification = Notification::create($notificationData);

            Log::info('Notification created for contract extension approval', [
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
                    ))
                    ->withData(['url' => 'https://sghood.com.vn/quan-ly/hop-dong']);


                $messaging->send($fcmMessage);

                Log::info('FCM sent to user for contract extension approval', [
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
            Log::error('Error in SendContractExtensionApprovedNotification job', [
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
        Log::error('SendContractExtensionApprovedNotification job failed', [
            'contract_extension_id' => $this->contractExtension->id,
            'user_id' => $this->contractExtension->contract->user_id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}
