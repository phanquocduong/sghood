<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\Checkout;
use App\Models\Config;
use App\Models\Notification;
use App\Models\Invoice;
use App\Jobs\SendContractExpiryNotification;
use App\Jobs\SendOverdueInvoiceNotification;
use App\Jobs\SendCheckoutAutoConfirmedNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;
use App\Mail\ContractExpiryNotification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class CheckContractExpiry extends Command
{
    protected $signature = 'app:check-contract-expiry {--debug : Enable debug mode}';
    protected $description = 'Kiểm tra và gửi thông báo hợp đồng sắp hết hạn, hóa đơn quá hạn và kiểm kê tự động xác nhận';

    public function handle()
    {
        $debug = $this->option('debug');

        $this->info("🔍 Bắt đầu kiểm tra hợp đồng sắp hết hạn, hóa đơn quá hạn và kiểm kê tự động...");

        // Kiểm tra hợp đồng sắp hết hạn
        $this->checkContractExpiry($debug);

        // Kiểm tra hóa đơn quá hạn
        $this->checkOverdueInvoices($debug);

        // Kiểm tra và xử lý kiểm kê tự động xác nhận
        $this->processAutoConfirmedCheckouts($debug); // Thêm lời gọi hàm mới

        return 0;
    }

    private function checkContractExpiry($debug)
    {
        $this->info("📋 === KIỂM TRA HỢP ĐỒNG SẮP HẾT HẠN ===");

        $this->ensureConfigExists();

        $notificationDays = (int) Config::getValue('is_near_expiration', 15);
        $this->info("📅 Số ngày thông báo: {$notificationDays}");

        $today = Carbon::today();
        $threshold = $today->copy()->addDays($notificationDays);

        $this->info("🗓️ Khoảng thời gian: {$today->format('d/m/Y')} - {$threshold->format('d/m/Y')}");

        $query = Contract::with(['user', 'room.motel'])
            ->where('status', 'Hoạt động')
            ->whereBetween('end_date', [$today, $threshold]);

        $contracts = $query->get();

        $this->info("📊 Tìm thấy {$contracts->count()} hợp đồng sắp hết hạn");

        if ($debug) {
            $this->showDebugInfo($today, $threshold);
        }

        if ($contracts->isEmpty()) {
            $this->info('ℹ️ Không có hợp đồng nào cần thông báo.');
        } else {
            $this->processContracts($contracts);
        }
    }

    private function checkOverdueInvoices($debug)
    {
        $this->info("💰 === KIỂM TRA HÓA ĐƠN QUÁ HẠN ===");

        $today = Carbon::today();
        $currentDay = $today->day;

        if ($currentDay <= 5) {
            $this->info("📅 Hiện tại đang trong thời hạn thanh toán (ngày 1-5), bỏ qua kiểm tra hóa đơn quá hạn.");
            return;
        }

        $paymentDeadline = Carbon::create($today->year, $today->month, 5);

        $this->info("⏰ Hạn thanh toán: {$paymentDeadline->format('d/m/Y')}");
        $this->info("📆 Hôm nay: {$today->format('d/m/Y')}");

        $overdueInvoices = Invoice::with(['contract.user', 'contract.room.motel'])
            ->where('status', 'chưa trả')
            ->where('created_at', '<=', $paymentDeadline)
            ->get();

        $this->info("📊 Tìm thấy {$overdueInvoices->count()} hóa đơn quá hạn");

        if ($debug) {
            $this->showOverdueInvoicesDebugInfo($overdueInvoices, $paymentDeadline);
        }

        if ($overdueInvoices->isEmpty()) {
            $this->info('ℹ️ Không có hóa đơn quá hạn nào.');
        } else {
            $this->processOverdueInvoices($overdueInvoices, $paymentDeadline);
        }
    }

    private function processAutoConfirmedCheckouts($debug)
    {
        $this->info("🔍 === KIỂM TRA KIỂM KÊ TỰ ĐỘNG XÁC NHẬN ===");

        $today = Carbon::today();
        $sevenDaysAgo = $today->copy()->subDays(7);

        $pendingCheckouts = Checkout::with(['contract.user', 'contract.room'])
            ->where('user_confirmation_status', 'Chưa xác nhận')
            ->where('updated_at', '<=', $sevenDaysAgo)
            ->get();

        $this->info("📊 Tìm thấy {$pendingCheckouts->count()} kiểm kê chưa xác nhận quá 7 ngày");

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

    private function showOverdueInvoicesDebugInfo($overdueInvoices, $paymentDeadline)
    {
        $this->info("🔧 DEBUG MODE - HÓA ĐƠN QUÁ HẠN:");

        foreach ($overdueInvoices->take(10) as $invoice) {
            $overdueDays = $paymentDeadline->diffInDays(Carbon::today());
            $userName = $invoice->contract->user->name ?? 'N/A';

            $this->info("   - ID: {$invoice->id} | User: {$userName} | Amount: " . number_format($invoice->total_amount) . "đ | Overdue: {$overdueDays} days");
        }
    }

    private function showCheckoutDebugInfo($pendingCheckouts, $today)
    {
        $this->info("🔧 DEBUG MODE - KIỂM KÊ CHƯA XÁC NHẬN:");

        foreach ($pendingCheckouts->take(10) as $checkout) {
            $updatedAt = Carbon::parse($checkout->updated_at);
            $daysSinceUpdated = $today->diffInDays($updatedAt);
            $this->info("   - Checkout ID: {$checkout->id} | Update: {$checkout->updated_at} | Days: {$daysSinceUpdated}");
        }
    }

    private function processOverdueInvoices($overdueInvoices, $paymentDeadline)
    {
        $jobsDispatched = 0;

        foreach ($overdueInvoices as $invoice) {
            try {
                $overdueDays = $paymentDeadline->diffInDays(Carbon::today());
                SendOverdueInvoiceNotification::dispatch($invoice, $overdueDays);

                $jobsDispatched++;
                $userName = $invoice->contract->user->name ?? 'N/A';
                $this->info("💸 Job dispatched for overdue invoice #{$invoice->id} (User: {$userName})");

            } catch (\Exception $e) {
                $this->error("❌ Error dispatching overdue invoice job for invoice #{$invoice->id}: " . $e->getMessage());
            }
        }

        $this->info("📈 Kết quả hóa đơn quá hạn: {$jobsDispatched} jobs đã được dispatch");
    }

    private function ensureConfigExists()
    {
        $config = Config::where('config_key', 'is_near_expiration')->first();

        if (!$config) {
            $this->warn("⚠️ Config chưa tồn tại, đang tạo mới...");
            Config::setValue('is_near_expiration', 15, 'integer', 'Số ngày thông báo trước khi hợp đồng hết hạn');
            $this->info("✅ Đã tạo config mới");
        }
    }

    private function showDebugInfo($today, $threshold)
    {
        $this->info("🔧 DEBUG MODE - HỢP ĐỒNG:");

        $allContracts = Contract::select('id', 'end_date', 'status')->get();
        $this->info("📋 Tổng hợp đồng: {$allContracts->count()}");

        foreach ($allContracts->take(10) as $contract) {
            $endDate = Carbon::parse($contract->end_date);
            $daysUntilExpiry = $today->diffInDays($endDate, false);

            $this->info("   - ID: {$contract->id} | End: {$contract->end_date} | Days: {$daysUntilExpiry} | Status: {$contract->status}");
        }
    }

    private function processContracts($contracts)
    {
        $jobsDispatched = 0;

        foreach ($contracts as $contract) {
            try {
                $endDate = Carbon::parse($contract->end_date);
                $daysRemaining = Carbon::today()->diffInDays($endDate);

                SendContractExpiryNotification::dispatch($contract, $daysRemaining);

                $jobsDispatched++;
                $this->info("📤 Job dispatched for contract #{$contract->id} (User: " . ($contract->user->name ?? 'N/A') . ")");

            } catch (\Exception $e) {
                $this->error("❌ Error dispatching job for contract #{$contract->id}: " . $e->getMessage());
            }
        }

        $this->info("📈 Kết quả hợp đồng: {$jobsDispatched} jobs đã được dispatch");
    }

    private function sendFcmNotification($user, $notificationData, $contract, $daysRemaining)
    {
        $messaging = app('firebase.messaging');

        $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
            ->withNotification(FirebaseNotification::create(
                $notificationData['title'],
                "Hợp đồng #{$contract->id} sẽ hết hạn sau {$daysRemaining} ngày"
            ))
            ->withData([
                'type' => 'contract_expiry',
                'contract_id' => (string) $contract->id,
                'days_remaining' => (string) $daysRemaining,
                'end_date' => $contract->end_date,
                'room_name' => $contract->room->name ?? '',
                'motel_name' => $contract->room->motel->name ?? '',
                'action_url' => url("/contracts/{$contract->id}")
            ]);

        $messaging->send($fcmMessage);

        Log::info('Contract expiry FCM sent', [
            'user_id' => $user->id,
            'contract_id' => $contract->id,
            'fcm_token' => substr($user->fcm_token, 0, 20) . '...'
        ]);
    }
}
