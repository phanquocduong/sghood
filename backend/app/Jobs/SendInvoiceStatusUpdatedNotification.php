<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\InvoiceStatusUpdatedMail;
use App\Models\Invoice;
use App\Models\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class SendInvoiceStatusUpdatedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $invoice;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new job instance.
     */
    public function __construct(Invoice $invoice, string $oldStatus, string $newStatus)
    {
        $this->invoice = $invoice;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = $this->invoice->contract->user;

            if (!$user) {
                Log::warning('No user found for invoice contract', [
                    'invoice_id' => $this->invoice->id,
                    'contract_id' => $this->invoice->contract_id
                ]);
                return;
            }

            // Gửi email thông báo cập nhật trạng thái hóa đơn
            $email = $user->email ?? null;
            if ($email) {
                try {
                    Mail::to($email)->send(new InvoiceStatusUpdatedMail($this->invoice, $this->oldStatus, $this->newStatus));
                    Log::info('Invoice status update email sent successfully', [
                        'email' => $email,
                        'invoice_code' => $this->invoice->code,
                        'old_status' => $this->oldStatus,
                        'new_status' => $this->newStatus,
                        'job' => 'SendInvoiceStatusUpdatedNotification'
                    ]);
                } catch (\Exception $emailError) {
                    Log::error('Error sending invoice status update email in job', [
                        'error' => $emailError->getMessage(),
                        'invoice_id' => $this->invoice->id,
                        'email' => $email,
                        'job' => 'SendInvoiceStatusUpdatedNotification'
                    ]);
                }
            } else {
                Log::warning('No email found for invoice user', [
                    'invoice_id' => $this->invoice->id,
                    'user_id' => $user->id,
                    'job' => 'SendInvoiceStatusUpdatedNotification'
                ]);
            }

            // Gửi thông báo đến người dùng (lưu vào database)
            try {
                $notificationTitle = 'Cập nhật trạng thái hóa đơn';
                $notificationContent = "Hóa đơn {$this->invoice->code} đã được cập nhật từ '" .
                    ($this->oldStatus ?: 'Chưa xác định') . "' sang '{$this->newStatus}'";

                $notificationData = [
                    'user_id' => $user->id,
                    'title' => $notificationTitle,
                    'content' => $notificationContent,
                    'status' => 'Chưa đọc',
                    'type' => 'invoice_status_updated',
                    'data' => json_encode([
                        'invoice_id' => $this->invoice->id,
                        'invoice_code' => $this->invoice->code,
                        'old_status' => $this->oldStatus,
                        'new_status' => $this->newStatus,
                        'room_name' => $this->invoice->contract->room->name ?? 'N/A',
                        'motel_name' => $this->invoice->contract->room->motel->name ?? 'N/A',
                        'total_amount' => $this->invoice->total_amount,
                        'month' => $this->invoice->month,
                        'year' => $this->invoice->year,
                        'action_url' => url("/invoices/{$this->invoice->id}")
                    ])
                ];

                $notification = Notification::create($notificationData);
                Log::info('Notification created for invoice status update', [
                    'invoice_id' => $this->invoice->id,
                    'notification_id' => $notification->id,
                    'old_status' => $this->oldStatus,
                    'new_status' => $this->newStatus,
                    'job' => 'SendInvoiceStatusUpdatedNotification'
                ]);

                // Gửi FCM notification nếu có token
                if ($user->fcm_token) {
                    $messaging = app('firebase.messaging');

                    $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(
                            FirebaseNotification::create($notificationTitle, $notificationContent)
                        )
                        ->withData([
                            'type' => 'invoice_status_updated',
                            'invoice_id' => (string)$this->invoice->id,
                            'invoice_code' => $this->invoice->code,
                            'old_status' => $this->oldStatus,
                            'new_status' => $this->newStatus,
                            'action_url' => url("/invoices/{$this->invoice->id}")
                        ]);

                    $messaging->send($fcmMessage);
                    Log::info('FCM notification sent for invoice status update', [
                        'invoice_id' => $this->invoice->id,
                        'fcm_token' => substr($user->fcm_token, 0, 20) . '...',
                        'job' => 'SendInvoiceStatusUpdatedNotification'
                    ]);
                }

            } catch (\Exception $notificationError) {
                Log::error('Error creating notification for invoice status update', [
                    'error' => $notificationError->getMessage(),
                    'invoice_id' => $this->invoice->id,
                    'user_id' => $user->id,
                    'job' => 'SendInvoiceStatusUpdatedNotification'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in SendInvoiceStatusUpdatedNotification job', [
                'error' => $e->getMessage(),
                'invoice_id' => $this->invoice->id,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendInvoiceStatusUpdatedNotification job failed', [
            'invoice_id' => $this->invoice->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
