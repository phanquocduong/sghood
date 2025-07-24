<?php

namespace App\Jobs;

use App\Mail\ContractConfirmNotification;
use App\Models\Contract;
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

class SendContractConfirmNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contract;

    /**
     * Create a new job instance.
     */
    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = $this->contract->user;

            if (!$user) {
                Log::warning('User not found for contract', [
                    'contract_id' => $this->contract->id
                ]);
                return;
            }

            // Gửi email
            if ($user->email) {
                Mail::to($user->email, $user->name)
                    ->send(new ContractConfirmNotification($this->contract));

                Log::info('Contract confirm email sent successfully', [
                    'contract_id' => $this->contract->id,
                    'user_email' => $user->email
                ]);
            }

            // Tạo thông báo trong database
            $notificationData = [
                'user_id' => $user->id,
                'title' => 'Hợp đồng đã được xác nhận',
                'content' => 'Hợp đồng của bạn đã được xác nhận và đang hoạt động.',
                'status' => 'Chưa đọc',
            ];

            $notification = Notification::create($notificationData);

            Log::info('Notification created for contract confirmation', [
                'notification_id' => $notification->getKey(),
                'contract_id' => $this->contract->id,
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

                Log::info('FCM sent to user for contract confirmation', [
                    'user_id' => $user->id,
                    'contract_id' => $this->contract->id,
                ]);
            } else {
                Log::info('No FCM token found for user', [
                    'user_id' => $user->id,
                    'contract_id' => $this->contract->id,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in SendContractConfirmNotification job', [
                'contract_id' => $this->contract->id,
                'user_id' => $this->contract->user_id ?? null,
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
        Log::error('SendContractConfirmNotification job failed', [
            'contract_id' => $this->contract->id,
            'user_id' => $this->contract->user_id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}
