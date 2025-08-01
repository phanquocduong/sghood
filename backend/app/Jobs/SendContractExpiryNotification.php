<?php
// filepath: app/Jobs/SendContractExpiryNotification.php

namespace App\Jobs;

use App\Models\Contract;
use App\Models\Notification;
use App\Mail\ContractExpiryNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class SendContractExpiryNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contract;
    protected $daysRemaining;

    public function __construct(Contract $contract, int $daysRemaining)
    {
        $this->contract = $contract;
        $this->daysRemaining = $daysRemaining;
    }

    public function handle(): void
    {
        try {
            $user = $this->contract->user;
            $room = $this->contract->room;
            $motel = $room->motel ?? null;

            if (!$user) {
                Log::warning('Contract has no user', ['contract_id' => $this->contract->id]);
                return;
            }

            $endDate = Carbon::parse($this->contract->end_date);
            
            // Tạo thông báo trong database
            $notificationData = [
                'user_id' => $user->id,
                'title' => 'Hợp đồng sắp hết hạn',
                'content' => "Hợp đồng #{$this->contract->id} tại {$room->name} (" . ($motel->name ?? 'N/A') . ") sẽ hết hạn sau {$this->daysRemaining} ngày (ngày {$endDate->format('d/m/Y')}). Để đảm bảo quyền lợi của bạn, vui lòng thực hiện gia hạn hoặc trả phòng trong {$this->daysRemaining} còn lại của hợp đồng.",
                'status' => 'Chưa đọc',
            ];

            $notification = Notification::create($notificationData);

            Log::info('Contract expiry notification created', [
                'notification_id' => $notification->id,
                'contract_id' => $this->contract->id,
                'user_id' => $user->id,
            ]);

            // Gửi email
            if ($user->email) {
                try {
                    Mail::to($user->email)->send(new ContractExpiryNotification($this->contract));
                    
                    Log::info('Contract expiry email sent', [
                        'contract_id' => $this->contract->id,
                        'user_email' => $user->email,
                    ]);
                } catch (\Exception $emailError) {
                    Log::error('Contract expiry email error', [
                        'contract_id' => $this->contract->id,
                        'user_email' => $user->email,
                        'error' => $emailError->getMessage()
                    ]);
                }
            }

            // Gửi FCM notification
            if ($user->fcm_token) {
                try {
                    $messaging = app('firebase.messaging');
                    
                    $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(FirebaseNotification::create(
                            $notificationData['title'],
                            "Hợp đồng #{$this->contract->id} sẽ hết hạn sau {$this->daysRemaining} ngày"
                        ))
                        ->withData([
                            'type' => 'contract_expiry',
                            'contract_id' => (string)$this->contract->id,
                            'days_remaining' => (string)$this->daysRemaining,
                            'end_date' => $this->contract->end_date,
                            'room_name' => $room->name ?? '',
                            'motel_name' => $motel->name ?? '',
                            'action_url' => url("/contracts/{$this->contract->id}")
                        ]);

                    $messaging->send($fcmMessage);
                    
                    Log::info('Contract expiry FCM sent', [
                        'user_id' => $user->id,
                        'contract_id' => $this->contract->id,
                    ]);
                } catch (\Exception $fcmError) {
                    Log::error('Contract expiry FCM error', [
                        'contract_id' => $this->contract->id,
                        'user_id' => $user->id,
                        'error' => $fcmError->getMessage()
                    ]);
                }
            } else {
                Log::info('No FCM token for user', [
                    'user_id' => $user->id,
                    'contract_id' => $this->contract->id,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in SendContractExpiryNotification job', [
                'contract_id' => $this->contract->id,
                'user_id' => $this->contract->user_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendContractExpiryNotification job failed', [
            'contract_id' => $this->contract->id,
            'user_id' => $this->contract->user_id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}