<?php

namespace App\Jobs;

use App\Mail\ContractRevisionNotification;
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

class SendContractRevisionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contract;
    protected $revisionReason;

    public function __construct(Contract $contract, string $revisionReason = null)
    {
        $this->contract = $contract;
        $this->revisionReason = $revisionReason;

        Log::info('SendContractRevisionNotification job constructed', [
            'contract_id' => $contract->id,
            'revision_reason' => $revisionReason
        ]);
    }

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

            // Log trước khi gửi email
            Log::info('Preparing to send contract revision email', [
                'contract_id' => $this->contract->id,
                'user_email' => $user->email,
                'revision_reason' => $this->revisionReason
            ]);

            // Gửi email
            if ($user->email) {
                Mail::to($user->email, $user->name)
                    ->send(new ContractRevisionNotification($this->contract, $this->revisionReason));

                Log::info('Contract revision email sent successfully', [
                    'contract_id' => $this->contract->id,
                    'user_email' => $user->email,
                    'revision_reason' => $this->revisionReason
                ]);
            }

            // Tạo thông báo trong database
            $notificationData = [
                'user_id' => $user->id,
                'title' => 'Hợp đồng cần chỉnh sửa',
                'content' => 'Hợp đồng của bạn cần chỉnh sửa. Vui lòng kiểm tra email để biết chi tiết.',
            ];

            $notification = Notification::create($notificationData);

            Log::info('Notification created for contract revision', [
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
                    ))
                    ->withData(['url' => 'https://sghood.com.vn/quan-ly/hop-dong']);

                $messaging->send($fcmMessage);

                Log::info('FCM sent to user for contract revision', [
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
            Log::error('Error in SendContractRevisionNotification job', [
                'contract_id' => $this->contract->id,
                'user_id' => $this->contract->user_id ?? null,
                'revision_reason' => $this->revisionReason,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendContractRevisionNotification job failed', [
            'contract_id' => $this->contract->id,
            'user_id' => $this->contract->user_id ?? null,
            'revision_reason' => $this->revisionReason,
            'error' => $exception->getMessage(),
        ]);
    }
}
