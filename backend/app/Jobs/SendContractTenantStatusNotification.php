<?php

namespace App\Jobs;

use App\Models\ContractTenant;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContractTenantStatusChanged;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class SendContractTenantStatusNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contractTenant;
    protected $status;
    protected $rejectionReason;

    /**
     * Create a new job instance.
     */
    public function __construct(ContractTenant $contractTenant, string $status, ?string $rejectionReason = null)
    {
        $this->contractTenant = $contractTenant;
        $this->status = $status;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = $this->contractTenant->contract->user;
            $room = $this->contractTenant->contract->room;

            // Prepare notification title and content based on status
            $notificationTitle = '';
            $notificationContent = '';

            switch ($this->status) {
                case 'Chờ duyệt':
                    $notificationTitle = 'Yêu cầu người ở chung đang chờ duyệt';
                    $notificationContent = "Yêu cầu người ở chung {$this->contractTenant->name} cho phòng {$room->name} đang chờ duyệt.";
                    break;
                case 'Đã duyệt':
                    $notificationTitle = 'Yêu cầu người ở chung đã được duyệt';
                    $notificationContent = "Yêu cầu người ở chung {$this->contractTenant->name} cho phòng {$room->name} đã được duyệt.";
                    break;
                case 'Từ chối':
                    $notificationTitle = 'Yêu cầu người ở chung bị từ chối';
                    $notificationContent = "Yêu cầu người ở chung {$this->contractTenant->name} cho phòng {$room->name} đã bị từ chối. Lý do: {$this->rejectionReason}";
                    break;
                case 'Đang ở':
                    $notificationTitle = 'Người ở chung đang ở';
                    $notificationContent = "Người ở chung {$this->contractTenant->name} đã chuyển sang trạng thái Đang ở tại phòng {$room->name}.";
                    break;
                case 'Đã rời đi':
                    $notificationTitle = 'Người ở chung đã rời đi';
                    $notificationContent = "Người ở chung {$this->contractTenant->name} đã rời khỏi phòng {$room->name}.";
                    break;
                default:
                    $notificationTitle = 'Cập nhật trạng thái người ở chung';
                    $notificationContent = "Trạng thái của {$this->contractTenant->name} tại phòng {$room->name} đã được cập nhật thành {$this->status}.";
            }

            // Send email
            if ($user->email) {
                Mail::to($user->email)->send(new ContractTenantStatusChanged(
                    $this->contractTenant,
                    $this->status,
                    $this->rejectionReason,
                    $room->name
                ));

                Log::info('Contract tenant status email sent successfully', [
                    'email' => $user->email,
                    'tenant_id' => $this->contractTenant->id,
                    'status' => $this->status,
                ]);
            }

            // Create database notification
            $notificationData = [
                'user_id' => $user->id,
                'title' => $notificationTitle,
                'content' => $notificationContent,
                'status' => 'Chưa đọc',
            ];

            $notification = Notification::create($notificationData);

            Log::info('Notification created for contract tenant status', [
                'notification_id' => $notification->getKey(),
                'tenant_id' => $this->contractTenant->id,
                'user_id' => $user->id,
                'status' => $this->status,
            ]);

            // Send FCM push notification
            if ($user->fcm_token) {
                $messaging = app('firebase.messaging');
                $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                    ->withNotification(FirebaseNotification::create(
                        $notificationTitle,
                        $notificationContent
                    ))
                    ->withData(['url' => 'https://sghood.com.vn/quan-ly/nguoi-o-chung']);

                $messaging->send($fcmMessage);

                Log::info('FCM sent to user for contract tenant status', [
                    'user_id' => $user->id,
                    'tenant_id' => $this->contractTenant->id,
                    'status' => $this->status,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in SendContractTenantStatusNotification job', [
                'tenant_id' => $this->contractTenant->id,
                'status' => $this->status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to allow job retry
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendContractTenantStatusNotification job failed', [
            'tenant_id' => $this->contractTenant->id,
            'status' => $this->status,
            'error' => $exception->getMessage(),
        ]);
    }
}
