<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Invoice;
use App\Jobs\SendOverdueInvoiceNotification;

class CheckOverdueInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-overdue-invoice {--debug : Enable debug mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra hoá đơn quá hạn thanh toán';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $debug = $this->option('debug');

        $this->info("🔍 Bắt đầu kiểm tra hoá đơn quá hạn thanh toán...");
        // Kiểm tra hóa đơn quá hạn
        $this->checkOverdueInvoices($debug);
    }

    /**
     * Kiểm tra và thông báo hóa đơn quá hạn thanh toán
     */
    private function checkOverdueInvoices($debug)
    {
        $this->info("💰 === KIỂM TRA HÓA ĐƠN QUÁ HẠN ===");

        $today = Carbon::today();
        $currentDay = $today->day;

        if ($currentDay <= 10) {
            $this->info("📅 Hiện tại đang trong thời hạn thanh toán (ngày 1-10), bỏ qua kiểm tra hóa đơn quá hạn.");
            return;
        }

        $paymentDeadline = Carbon::create($today->year, $today->month, 10);

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

    /**
     * Xử lý danh sách hóa đơn quá hạn
     */
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

    /**
     * Hiển thị thông tin debug cho hóa đơn quá hạn
     */
    private function showOverdueInvoicesDebugInfo($overdueInvoices, $paymentDeadline)
    {
        $this->info("🔧 DEBUG MODE - HÓA ĐƠN QUÁ HẠN:");

        foreach ($overdueInvoices->take(10) as $invoice) {
            $overdueDays = $paymentDeadline->diffInDays(Carbon::today());
            $userName = $invoice->contract->user->name ?? 'N/A';

            $this->info("   - ID: {$invoice->id} | User: {$userName} | Amount: " . number_format($invoice->total_amount) . "đ | Overdue: {$overdueDays} days");
        }
    }
}
