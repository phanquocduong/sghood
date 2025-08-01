<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\Config;
use App\Models\Notification;
use App\Models\Invoice;
use App\Jobs\SendContractExpiryNotification;
use App\Jobs\SendOverdueInvoiceNotification;
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
    protected $description = 'Kiểm tra và gửi thông báo hợp đồng sắp hết hạn và hóa đơn quá hạn';

    public function handle()
    {
        $debug = $this->option('debug');

        $this->info("🔍 Bắt đầu kiểm tra hợp đồng sắp hết hạn và hóa đơn quá hạn...");

        // Kiểm tra hợp đồng sắp hết hạn
        $this->checkContractExpiry($debug);

        // Kiểm tra hóa đơn quá hạn
        $this->checkOverdueInvoices($debug);


        return 0;
    }

    private function checkContractExpiry($debug)
    {
        $this->info("📋 === KIỂM TRA HỢP ĐỒNG SẮP HẾT HẠN ===");

        // Kiểm tra và tạo config nếu cần
        $this->ensureConfigExists();

        $notificationDays = (int) Config::getValue('is_near_expiration', 15);
        $this->info("📅 Số ngày thông báo: {$notificationDays}");

        $today = Carbon::today();
        $threshold = $today->copy()->addDays($notificationDays);

        $this->info("🗓️ Khoảng thời gian: {$today->format('d/m/Y')} - {$threshold->format('d/m/Y')}");

        // Query hợp đồng
        $query = Contract::with(['user', 'room.motel'])
            ->where('status', 'Hoạt động') // Chỉ hợp đồng đang hoạt động
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

        // Nếu hạn thanh toán là ngày 5, thì chỉ kiểm tra từ ngày 6 trở đi
        if ($currentDay <= 5) {
            $this->info("📅 Hiện tại đang trong thời hạn thanh toán (ngày 1-5), bỏ qua kiểm tra hóa đơn quá hạn.");
            return;
        }

        // Tính toán ngày 5 của tháng hiện tại làm hạn thanh toán
        $paymentDeadline = Carbon::create($today->year, $today->month, 5);

        $this->info("⏰ Hạn thanh toán: {$paymentDeadline->format('d/m/Y')}");
        $this->info("📆 Hôm nay: {$today->format('d/m/Y')}");

        // Query hóa đơn quá hạn
        $overdueInvoices = Invoice::with(['contract.user', 'contract.room.motel'])
            ->where('status', 'chưa trả') // Hóa đơn chưa thanh toán
            ->where('created_at', '<=', $paymentDeadline) // Được tạo trước ngày 5
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

    private function showOverdueInvoicesDebugInfo($overdueInvoices, $paymentDeadline)
    {
        $this->info("🔧 DEBUG MODE - HÓA ĐƠN QUÁ HẠN:");

        foreach ($overdueInvoices->take(10) as $invoice) {
            // Tính số ngày quá hạn từ deadline đến hôm nay
            $overdueDays = $paymentDeadline->diffInDays(Carbon::today());
            $userName = $invoice->contract->user->name ?? 'N/A';

            $this->info("   - ID: {$invoice->id} | User: {$userName} | Amount: " . number_format($invoice->total_amount) . "đ | Overdue: {$overdueDays} days");
        }
    }

    private function processOverdueInvoices($overdueInvoices, $paymentDeadline)
    {
        $jobsDispatched = 0;

        foreach ($overdueInvoices as $invoice) {
            try {
                // Sửa lại cách tính overdue days
                $overdueDays = $paymentDeadline->diffInDays(Carbon::today());

                // Dispatch job để gửi thông báo hóa đơn quá hạn
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

        // Hiển thị tất cả hợp đồng
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

                // Dispatch job thay vì xử lý trực tiếp
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