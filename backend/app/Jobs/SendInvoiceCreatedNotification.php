<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\InvoiceCreated;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\MeterReading;
use App\Models\Contract;
use App\Models\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class SendInvoiceCreatedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $invoice;
    public $room;
    public $meterReading;
    public $contract;

    /**
     * Create a new job instance.
     */
    public function __construct(Invoice $invoice, Room $room, MeterReading $meterReading, Contract $contract)
    {
        $this->invoice = $invoice;
        $this->room = $room;
        $this->meterReading = $meterReading;
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
                Log::warning('No user found for contract', ['contract_id' => $this->contract->id]);
                return;
            }

            // Gửi email thông báo tạo hóa đơn
            $email = $user->email ?? null;
            if ($email) {
                try {
                    Mail::to($email)->send(new InvoiceCreated($this->invoice, $this->room, $this->meterReading, $this->contract));
                    Log::info('Invoice email sent successfully', [
                        'email' => $email,
                        'invoice_code' => $this->invoice->code,
                        'job' => 'SendInvoiceCreatedNotification'
                    ]);
                } catch (\Exception $emailError) {
                    Log::error('Error sending invoice email in job', [
                        'error' => $emailError->getMessage(),
                        'invoice_id' => $this->invoice->id,
                        'email' => $email,
                        'job' => 'SendInvoiceCreatedNotification'
                    ]);
                }
            } else {
                Log::warning('No email found for contract user', [
                    'contract_id' => $this->contract->id,
                    'job' => 'SendInvoiceCreatedNotification'
                ]);
            }

            // Gửi thông báo đến người dùng
            try {
                $notificationData = [
                    'user_id' => $user->id,
                    'title' => 'Hóa đơn của bạn đã được tạo',
                    'content' => 'Hóa đơn của bạn đã được tạo! Vui lòng xem chi tiết và thanh toán.',
                    'status' => 'Chưa đọc'
                ];

                $notification = Notification::create($notificationData);
                Log::info('Notification created for invoice in job', [
                    'contract_id' => $this->contract->id,
                    'notification_id' => $notification->id,
                    'invoice_id' => $this->invoice->id,
                    'job' => 'SendInvoiceCreatedNotification'
                ]);

                // Gửi FCM notification
                if ($user->fcm_token) {
                    $messaging = app('firebase.messaging');

                    $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(FirebaseNotification::create(
                            $notificationData['title'],
                            $notificationData['content']
                        ));

                    try {
                        $messaging->send($fcmMessage);
                        Log::info('FCM sent to user in job', [
                            'user_id' => $user->id,
                            'invoice_id' => $this->invoice->id,
                            'job' => 'SendInvoiceCreatedNotification'
                        ]);
                    } catch (\Exception $e) {
                        Log::error('FCM send error in job', [
                            'error' => $e->getMessage(),
                            'user_id' => $user->id,
                            'job' => 'SendInvoiceCreatedNotification'
                        ]);
                    }
                } else {
                    Log::info('No FCM token found for user in job', [
                        'user_id' => $user->id,
                        'job' => 'SendInvoiceCreatedNotification'
                    ]);
                }
            } catch (\Exception $notificationError) {
                Log::error('Error creating notification in job', [
                    'error' => $notificationError->getMessage(),
                    'invoice_id' => $this->invoice->id,
                    'job' => 'SendInvoiceCreatedNotification'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in SendInvoiceCreatedNotification job', [
                'error' => $e->getMessage(),
                'invoice_id' => $this->invoice->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
