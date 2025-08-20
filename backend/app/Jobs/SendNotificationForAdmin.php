<?php

namespace App\Jobs;

use App\Mail\ContractExpiryNotificationForMeterReading;
use App\Models\Contract;
use App\Models\Notification;
use App\Models\User;
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

class SendNotificationForAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 phút
    public $tries = 3;

    protected $contract;
    protected $daysRemaining;

    public function __construct(Contract $contract, int $daysRemaining)
    {
        $this->contract = $contract;
        $this->daysRemaining = $daysRemaining;
        
        // ✅ Debug constructor
        Log::info('SendNotificationForAdmin constructor called', [
            'contract_id' => $contract->id,
            'contract_status' => $contract->status,
            'contract_end_date' => $contract->end_date,
            'days_remaining' => $daysRemaining,
            'timestamp' => now()
        ]);
    }

    public function handle(): void
    {
        Log::info('=== SendNotificationForAdmin job HANDLE started ===', [
            'job_id' => $this->job->getJobId(),
            'contract_id' => $this->contract->id,
            'days_remaining' => $this->daysRemaining,
            'queue_name' => $this->job->getQueue(),
            'attempts' => $this->attempts(),
            'max_tries' => $this->tries
        ]);

        try {
            // ✅ Debug contract relationship loading
            $room = $this->contract->room;
            Log::info('Contract room loaded', [
                'contract_id' => $this->contract->id,
                'room_exists' => !is_null($room),
                'room_id' => $room ? $room->id : null,
                'room_number' => $room ? $room->room_number : null
            ]);

            if (!$room) {
                Log::warning('Contract has no room - JOB TERMINATING', ['contract_id' => $this->contract->id]);
                return;
            }

            $motel = $room->motel ?? null;
            $tenant = $this->contract->user;

            Log::info('Contract relationships loaded', [
                'contract_id' => $this->contract->id,
                'motel_exists' => !is_null($motel),
                'motel_id' => $motel ? $motel->id : null,
                'motel_name' => $motel ? $motel->name : null,
                'tenant_exists' => !is_null($tenant),
                'tenant_id' => $tenant ? $tenant->id : null,
                'tenant_name' => $tenant ? $tenant->name : null
            ]);

            $endDate = Carbon::parse($this->contract->end_date);
            $roomNumber = $room->room_number ?? $room->name ?? 'N/A';
            $motelName = $motel->name ?? 'N/A';
            $tenantName = $tenant->name ?? 'N/A';

            Log::info('Processed contract details', [
                'contract_id' => $this->contract->id,
                'room_number' => $roomNumber,
                'motel_name' => $motelName,
                'tenant_name' => $tenantName,
                'end_date' => $endDate->format('Y-m-d H:i:s'),
                'end_date_formatted' => $endDate->format('d/m/Y')
            ]);

            // ✅ Debug admin loading
            $admins = User::whereIn('role', ['Quản trị viên', 'Super admin'])->get();
            
            Log::info('Admin query executed', [
                'admins_found' => $admins->count(),
                'query_sql' => User::whereIn('role', ['Quản trị viên', 'Super admin'])->toSql(),
                'query_bindings' => ['Quản trị viên', 'Super admin']
            ]);

            Log::info('Found admins detailed', [
                'count' => $admins->count(),
                'admins' => $admins->map(function($admin) {
                    return [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => $admin->email,
                        'role' => $admin->role,
                        'has_fcm_token' => !empty($admin->fcm_token),
                        'fcm_token_length' => strlen($admin->fcm_token ?? '')
                    ];
                })->toArray()
            ]);

            if ($admins->isEmpty()) {
                Log::warning('No admin users found - JOB TERMINATING');
                return;
            }

            $processedAdmins = 0;
            $emailsSent = 0;
            $fcmsSent = 0;

            foreach ($admins as $admin) {
                Log::info("=== Processing admin: {$admin->name} (ID: {$admin->id}) ===");
                
                // ✅ Gửi email
                $emailResult = $this->sendEmailToAdmin($admin, $roomNumber, $motelName, $tenantName, $endDate);
                if ($emailResult) $emailsSent++;
                
                // ✅ Gửi FCM
                $fcmResult = $this->sendFCMToAdmin($admin, $roomNumber, $motelName, $tenantName);
                if ($fcmResult) $fcmsSent++;

                $processedAdmins++;

                Log::info("Admin notification results", [
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->name,
                    'email_sent' => $emailResult,
                    'fcm_sent' => $fcmResult
                ]);
            }

            Log::info('=== SendNotificationForAdmin job completed successfully ===', [
                'contract_id' => $this->contract->id,
                'admins_processed' => $processedAdmins,
                'emails_sent' => $emailsSent,
                'fcms_sent' => $fcmsSent,
                'job_duration' => microtime(true) - LARAVEL_START
            ]);

        } catch (\Exception $e) {
            Log::error('=== ERROR in SendNotificationForAdmin job ===', [
                'contract_id' => $this->contract->id,
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * ✅ Gửi email cho admin với debugging chi tiết
     */
    private function sendEmailToAdmin($admin, $roomNumber, $motelName, $tenantName, $endDate): bool
    {
        Log::info("=== EMAIL SENDING START ===", [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'has_email' => !empty($admin->email)
        ]);

        if (!$admin->email) {
            Log::info('Admin has no email - SKIPPING EMAIL', ['admin_id' => $admin->id]);
            return false;
        }

        try {
            $emailData = [
                'contract_id' => $this->contract->id,
                'room_number' => $roomNumber,
                'motel_name' => $motelName,
                'tenant_name' => $tenantName,
                'end_date' => $endDate->format('d/m/Y'),
                'days_remaining' => $this->daysRemaining,
                'admin_name' => $admin->name,
                'action_url' => url("/meter-readings/create?room_id={$this->contract->room_id}")
            ];

            Log::info("Email data prepared", [
                'admin_id' => $admin->id,
                'email_data' => $emailData
            ]);

            // ✅ Test mail configuration
            $this->testMailConfig();

            Log::info("About to send email", [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'mailable_class' => ContractExpiryNotificationForMeterReading::class
            ]);

            Mail::to($admin->email)->send(new ContractExpiryNotificationForMeterReading($emailData));

            Log::info('=== EMAIL SENT SUCCESSFULLY ===', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'contract_id' => $this->contract->id
            ]);

            return true;

        } catch (\Exception $emailError) {
            Log::error('=== EMAIL SENDING FAILED ===', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'contract_id' => $this->contract->id,
                'error_message' => $emailError->getMessage(),
                'error_line' => $emailError->getLine(),
                'error_file' => $emailError->getFile(),
                'trace' => $emailError->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * ✅ Test mail configuration với debugging
     */
    private function testMailConfig()
    {
        $mailConfig = [
            'driver' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'username' => config('mail.mailers.smtp.username'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'encryption' => config('mail.mailers.smtp.encryption')
        ];

        Log::info('=== MAIL CONFIGURATION ===', $mailConfig);

        // ✅ Check if mail configuration is valid
        $isValid = !empty($mailConfig['host']) && !empty($mailConfig['username']);
        Log::info('Mail configuration valid: ' . ($isValid ? 'YES' : 'NO'));
    }

    /**
     * ✅ Gửi FCM notification cho admin với debugging chi tiết
     */
    private function sendFCMToAdmin($admin, $roomNumber, $motelName, $tenantName): bool
    {
        Log::info("=== FCM SENDING START ===", [
            'admin_id' => $admin->id,
            'has_fcm_token' => !empty($admin->fcm_token),
            'fcm_token_length' => strlen($admin->fcm_token ?? '')
        ]);

        if (!$admin->fcm_token) {
            Log::info('Admin has no FCM token - SKIPPING FCM', ['admin_id' => $admin->id]);
            return false;
        }

        try {
            // ✅ Check if Firebase is configured
            $firebaseConfigured = app()->bound('firebase.messaging');
            Log::info("Firebase configuration check", [
                'firebase_bound' => $firebaseConfigured,
                'config_file_exists' => file_exists(storage_path('app/firebase-credentials.json'))
            ]);

            if (!$firebaseConfigured) {
                Log::warning('Firebase messaging not configured - SKIPPING FCM');
                return false;
            }

            $messaging = app('firebase.messaging');

            $title = "🏠 Hợp đồng sắp hết hạn";
            $body = "Phòng {$roomNumber} ({$motelName}) - {$tenantName} - Còn {$this->daysRemaining} ngày. Cần nhập chỉ số điện nước.";

            Log::info("FCM message prepared", [
                'admin_id' => $admin->id,
                'title' => $title,
                'body' => $body,
                'fcm_token_preview' => substr($admin->fcm_token, 0, 20) . '...'
            ]);

            $fcmMessage = CloudMessage::withTarget('token', $admin->fcm_token)
                ->withNotification(FirebaseNotification::create($title, $body))
                ->withData([
                    'type' => 'contract_expiring_admin',
                    'contract_id' => (string)$this->contract->id,
                    'room_id' => (string)$this->contract->room_id,
                    'room_number' => $roomNumber,
                    'motel_name' => $motelName,
                    'tenant_name' => $tenantName,
                    'days_remaining' => (string)$this->daysRemaining,
                    'end_date' => $this->contract->end_date,
                    'action_required' => 'input_meter_reading',
                    'action_url' => url("/meter-readings/create?room_id={$this->contract->room_id}"),
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ]);

            Log::info("About to send FCM", [
                'admin_id' => $admin->id,
                'message_class' => get_class($fcmMessage)
            ]);

            $result = $messaging->send($fcmMessage);

            Log::info('=== FCM SENT SUCCESSFULLY ===', [
                'admin_id' => $admin->id,
                'contract_id' => $this->contract->id,
                'fcm_result' => $result
            ]);

            return true;

        } catch (\Exception $fcmError) {
            Log::error('=== FCM SENDING FAILED ===', [
                'admin_id' => $admin->id,
                'contract_id' => $this->contract->id,
                'error_message' => $fcmError->getMessage(),
                'error_line' => $fcmError->getLine(),
                'error_file' => $fcmError->getFile(),
                'trace' => $fcmError->getTraceAsString()
            ]);
            return false;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('=== SendNotificationForAdmin job FAILED COMPLETELY ===', [
            'contract_id' => $this->contract->id,
            'error_message' => $exception->getMessage(),
            'error_line' => $exception->getLine(),
            'error_file' => $exception->getFile(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}