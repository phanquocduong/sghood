<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\Config;
use App\Jobs\SendContractExpiryNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckContractExpiry extends Command
{
    protected $signature = 'app:check-contract-expiry {--debug : Enable debug mode}';
    protected $description = 'Kiểm tra và gửi thông báo hợp đồng sắp hết hạn, kiểm kê tự động xác nhận';

    /**
     * Phương thức chính của command
     * Điều phối các chức năng kiểm tra và xử lý tự động
     */
    public function handle()
    {
        $debug = $this->option('debug');

        $this->info("🔍 Bắt đầu kiểm tra hợp đồng sắp hết hạn, kiểm kê tự động...");

        // Kiểm tra hợp đồng sắp hết hạn
        $this->checkContractExpiry($debug);

        return 0;
    }

    //-------------------------------------------------------------------
    // PHƯƠNG THỨC KIỂM TRA CHÍNH
    //-------------------------------------------------------------------

    /**
     * Kiểm tra và thông báo hợp đồng sắp hết hạn
     */
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

    //-------------------------------------------------------------------
    // PHƯƠNG THỨC XỬ LÝ
    //-------------------------------------------------------------------

    /**
     * Xử lý danh sách hợp đồng sắp hết hạn
     */
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

    //-------------------------------------------------------------------
    // PHƯƠNG THỨC DEBUG VÀ HỖ TRỢ
    //-------------------------------------------------------------------

    /**
     * Đảm bảo config tồn tại trong hệ thống
     */
    private function ensureConfigExists()
    {
        $config = Config::where('config_key', 'is_near_expiration')->first();

        if (!$config) {
            $this->warn("⚠️ Config chưa tồn tại, đang tạo mới...");
            Config::setValue('is_near_expiration', 15, 'integer', 'Số ngày thông báo trước khi hợp đồng hết hạn');
            $this->info("✅ Đã tạo config mới");
        }
    }

    /**
     * Hiển thị thông tin debug cho hợp đồng
     */
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




}
