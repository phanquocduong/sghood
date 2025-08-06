<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Notification;
use App\Mail\OverdueInvoiceNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class SendOverdueInvoiceNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoice;
    protected $overdueDays;

    public function __construct(Invoice $invoice, int $overdueDays)
    {
        $this->invoice = $invoice;
        $this->overdueDays = $overdueDays;
    }

    public function handle()
    {
        $user = $this->invoice->contract->user;

        if (!$user) {
            Log::warning('User not found for invoice', ['invoice_id' => $this->invoice->id]);
            return;
        }

        $notificationData = [
            'title' => '🚨 Hóa đơn quá hạn thanh toán',
            'message' => "Hóa đơn #{$this->invoice->id} đã quá hạn thanh toán {$this->overdueDays} ngày. Vui lòng thanh toán ngay để tránh phát sinh phí.",
            'content' => "Kính gửi {$user->name},\n\nHóa đơn #{$this->invoice->id} với số tiền " . number_format($this->invoice->total_amount) . "đ đã quá hạn thanh toán {$this->overdueDays} ngày.\n\nPhòng: " . ($this->invoice->contract->room->name ?? 'N/A') . "\nNhà trọ: " . ($this->invoice->contract->room->motel->name ?? 'N/A') . "\n\nVui lòng thanh toán sớm nhất để tránh phát sinh thêm phí phạt.",
            'type' => 'overdue_invoice',
            'data' => [
                'invoice_id' => $this->invoice->id,
                'overdue_days' => $this->overdueDays,
                'total_amount' => $this->invoice->total_amount,
                'room_name' => $this->invoice->contract->room->name ?? '',
                'motel_name' => $this->invoice->contract->room->motel->name ?? '',
            ]
        ];

        // Lưu thông báo vào database
        $this->saveNotification($user, $notificationData);

        // Gửi email
        $this->sendEmail($user);

        // Gửi FCM notification nếu có token
        if ($user->fcm_token) {
            $this->sendFcmNotification($user, $notificationData);
        }

        Log::info('Overdue invoice notification sent', [
            'user_id' => $user->id,
            'invoice_id' => $this->invoice->id,
            'overdue_days' => $this->overdueDays
        ]);
    }

    private function saveNotification($user, $notificationData)
    {
        try {
            // Đảm bảo content không empty
            $content = $notificationData['content'] ?? 'Nội dung thông báo hóa đơn quá hạn';

            if (empty($content)) {
                $content = "Hóa đơn #{$this->invoice->id} đã quá hạn thanh toán.";
            }

            DB::table('notifications')->insert([
                'user_id' => $user->id,
                'title' => $notificationData['title'] ?? 'Thông báo hóa đơn',
                'content' => $content,
                'status' => 'Chưa đọc',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Notification saved successfully', [
                'user_id' => $user->id,
                'invoice_id' => $this->invoice->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save notification via DB', [
                'user_id' => $user->id,
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage(),
                'content_length' => strlen($notificationData['content'] ?? ''),
                'content_preview' => substr($notificationData['content'] ?? '', 0, 100)
            ]);
        }
    }

    private function sendEmail($user)
    {
        try {
            Mail::to($user->email)->send(new OverdueInvoiceNotification($this->invoice, $this->overdueDays));
        } catch (\Exception $e) {
            Log::error('Failed to send overdue invoice email', [
                'user_id' => $user->id,
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendFcmNotification($user, $notificationData)
    {
        try {
            $messaging = app('firebase.messaging');

            $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(FirebaseNotification::create(
                    $notificationData['title'],
                    "Hóa đơn #{$this->invoice->id} quá hạn {$this->overdueDays} ngày. Số tiền: " . number_format($this->invoice->total_amount) . "đ"
                ))
                ->withData([
                    'type' => 'overdue_invoice',
                    'invoice_id' => (string) $this->invoice->id,
                    'overdue_days' => (string) $this->overdueDays,
                    'total_amount' => (string) $this->invoice->total_amount,
                    'room_name' => $this->invoice->contract->room->name ?? '',
                    'motel_name' => $this->invoice->contract->room->motel->name ?? '',
                    'action_url' => 'https://sghood.com.vn/quan-ly/hoa-don'
                ]);

            $messaging->send($fcmMessage);

        } catch (\Exception $e) {
            Log::error('Failed to send overdue invoice FCM', [
                'user_id' => $user->id,
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
