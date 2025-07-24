<?php

namespace App\Jobs;

use App\Mail\ContractSignNotification;
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

class SendContractSignNotification implements ShouldQueue
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
                    ->send(new ContractSignNotification($this->contract));

                Log::info('Contract sign email sent successfully', [
                    'contract_id' => $this->contract->id,
                    'user_email' => $user->email
                ]);
            }

            // Tạo thông báo trong database
            $notificationData = [
                'user_id' => $user->id,
                'title' => 'Hợp đồng cần ký',
                'content' => 'Hợp đồng của bạn cần ký. Vui lòng kiểm tra email để biết chi tiết.',
                'status' => 'Chưa đọc',
            ];

            $notification = Notification::create($notificationData);

            Log::info('Notification created for contract sign', [
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

                Log::info('FCM sent to user for contract sign', [
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
            Log::error('Error in SendContractSignNotification job', [
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
        Log::error('SendContractSignNotification job failed', [
            'contract_id' => $this->contract->id,
            'user_id' => $this->contract->user_id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}
