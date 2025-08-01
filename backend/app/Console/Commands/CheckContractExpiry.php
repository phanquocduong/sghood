<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\Config;
use App\Models\Notification;
use App\Jobs\SendContractExpiryNotification;
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
    protected $description = 'Kiểm tra và gửi thông báo hợp đồng sắp hết hạn';

    public function handle()
    {
        $debug = $this->option('debug');

        $this->info("🔍 Bắt đầu kiểm tra hợp đồng sắp hết hạn...");

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
            return 0;
        }

        $this->processContracts($contracts);

        return 0;
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
        $this->info("🔧 DEBUG MODE:");

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

    $this->info("📈 Kết quả: {$jobsDispatched} jobs đã được dispatch");
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
                'contract_id' => (string)$contract->id,
                'days_remaining' => (string)$daysRemaining,
                'end_date' => $contract->end_date,
                'room_name' => $contract->room->name ?? '',
                'motel_name' => $contract->room->motel->name ?? '',
                'action_url' => url("/contracts/{$contract->id}")
            ]);

        $messaging->send($fcmMessage);

        Log::info('Contract expiry FCM sent', [
            'user_id' => $user->id,
            'contract_id' => $contract->id,
            'fcm_token' => substr($user->fcm_token, 0, 20) . '...' // Log partial token for security
        ]);
    }
}
