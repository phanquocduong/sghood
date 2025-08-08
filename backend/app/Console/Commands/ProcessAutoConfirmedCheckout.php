<?php

namespace App\Console\Commands;

use App\Models\Checkout;
use App\Models\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Jobs\SendCheckoutAutoConfirmedNotification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class ProcessAutoConfirmedCheckout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-auto-confirmed-checkout {--debug : Enable debug mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $debug = $this->option('debug');

        $this->info("🔍 Bắt đầu kiểm tra và xử lý kiểm kê tự động xác nhận...");
        // Kiểm tra và xử lý kiểm kê tự động xác nhận
        $this->processAutoConfirmedCheckouts($debug);
    }

    /**
     * Kiểm tra và tự động xác nhận kiểm kê quá 7 ngày
     */
    private function processAutoConfirmedCheckouts($debug)
    {
        $this->info("🔍 === KIỂM TRA KIỂM KÊ TỰ ĐỘNG XÁC NHẬN ===");
        $notificationDays = (int) Config::getValue('date_confirm_checkout');
        $today = Carbon::today();
        $sevenDaysAgo = $today->copy()->subDays($notificationDays); 

        $pendingCheckouts = Checkout::with(['contract.user', 'contract.room'])
            ->where('inventory_status', 'Đã kiểm kê')
            ->where('user_confirmation_status', 'Chưa xác nhận')
            ->where('updated_at', '<=', $sevenDaysAgo)
            ->get();

        $this->info("📊 Tìm thấy {$pendingCheckouts->count()} kiểm kê chưa xác nhận quá {$notificationDays}");

        if ($debug) {
            $this->showCheckoutDebugInfo($pendingCheckouts, $today); // Thêm debug cho checkout
        }

        if ($pendingCheckouts->isEmpty()) {
            $this->info('ℹ️ Không có kiểm kê nào cần xác nhận tự động.');
        } else {
            $jobsDispatched = 0;

            foreach ($pendingCheckouts as $checkout) {
                try {
                    SendCheckoutAutoConfirmedNotification::dispatch($checkout, $checkout->contract->user, $checkout->contract->room);

                    $jobsDispatched++;
                    $this->info("📤 Job dispatched for auto-confirmed checkout #{$checkout->id} (User: " . ($checkout->contract->user->name ?? 'N/A') . ")");

                    $checkout->update(['user_confirmation_status' => 'Đồng ý']);

                } catch (\Exception $e) {
                    $this->error("❌ Error dispatching job for checkout #{$checkout->id}: " . $e->getMessage());
                    Log::error("Error in processAutoConfirmedCheckouts for checkout #{$checkout->id}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            $this->info("📈 Kết quả: {$jobsDispatched} jobs đã được dispatch cho kiểm kê tự động");
        }
    }

    /**
     * Gửi thông báo FCM chung
     */
    private function sendFcmNotification($user, $notificationData, $contract, $daysRemaining)
    {
        try {
            // Kiểm tra FCM token
            if (!$user->fcm_token) {
                $this->warn("⚠️ User #{$user->id} không có FCM token");
                return;
            }

            $messaging = app('firebase.messaging');

            // Tạo message content dựa trên số ngày còn lại
            $messageBody = $daysRemaining > 0
                ? "Hợp đồng #{$contract->id} sẽ hết hạn sau {$daysRemaining} ngày"
                : "Hợp đồng #{$contract->id} đã được kết thúc tự động";

            $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(FirebaseNotification::create(
                    $notificationData['title'],
                    $messageBody
                ))
                ->withData([
                    'type' => $daysRemaining > 0 ? 'contract_expiry' : 'contract_ended',
                    'contract_id' => (string) $contract->id,
                    'days_remaining' => (string) $daysRemaining,
                    'end_date' => $contract->end_date->format('Y-m-d'),
                    'room_name' => $contract->room->name ?? '',
                    'motel_name' => $contract->room->motel->name ?? '',
                    'action_url' => url("/contracts/{$contract->id}")
                ]);

            $messaging->send($fcmMessage);

            $this->info("📱 Đã gửi FCM notification cho user #{$user->id}");

            Log::info('Contract FCM sent', [
                'user_id' => $user->id,
                'contract_id' => $contract->id,
                'days_remaining' => $daysRemaining,
                'fcm_token' => substr($user->fcm_token, 0, 20) . '...'
            ]);

        } catch (\Exception $e) {
            $this->warn("⚠️ Không thể gửi FCM cho user #{$user->id}: " . $e->getMessage());
            Log::error("Error sending FCM notification", [
                'user_id' => $user->id,
                'contract_id' => $contract->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Hiển thị thông tin debug cho kiểm kê
     */
    private function showCheckoutDebugInfo($pendingCheckouts, $today)
    {
        $this->info("🔧 DEBUG MODE - KIỂM KÊ CHƯA XÁC NHẬN:");

        foreach ($pendingCheckouts->take(10) as $checkout) {
            $updatedAt = Carbon::parse($checkout->updated_at);
            $daysSinceUpdated = $today->diffInDays($updatedAt);
            $this->info("   - Checkout ID: {$checkout->id} | Update: {$checkout->updated_at} | Days: {$daysSinceUpdated}");
        }
    }
}
