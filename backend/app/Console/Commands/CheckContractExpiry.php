<?php

namespace App\Console\Commands;

use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;

class CheckContractExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-contract-expiry';

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
        echo "Checking for contracts nearing expiry...\n";
        $today = Carbon::today();
        $threshold = $today->copy()->addDays(3); // Thông báo trước 3 ngày

        // Lấy hợp đồng sắp hết hạn
        $contracts = Contract::whereBetween('end_date', [$today, $threshold])->get();

        foreach ($contracts as $contract) {
            // Gửi email
            Mail::raw(
                "Kính gửi,\n\nHợp đồng #{$contract->id} của bạn sẽ hết hạn vào ngày {$contract->end_date}.\nVui lòng gia hạn hoặc liên hệ để biết thêm chi tiết.\n\nTrân trọng,\nYour Company",
                function ($message) use ($contract) {
                    $message->to($contract->user->email)
                            ->subject("Thông báo: Hợp đồng #{$contract->id} sắp hết hạn");
                }
            );

            $this->info("Đã gửi email tới {$contract->user->email} cho hợp đồng #{$contract->id}");
        }

        if ($contracts->isEmpty()) {
            $this->info('Không có hợp đồng nào sắp hết hạn.');
        }
    }
}