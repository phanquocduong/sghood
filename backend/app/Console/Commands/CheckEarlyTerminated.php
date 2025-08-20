<?php

namespace App\Console\Commands;

use App\Models\Contract;
use Illuminate\Console\Command;

class CheckEarlyTerminated extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-early-terminated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra hợp đồng đã kết thúc sớm';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('Bắt đầu kiểm tra hợp đồng đã kết thúc sớm...');
        $this->checkEarlyTerminated();
        $this->info('Kiểm tra hợp đồng đã kết thúc sớm hoàn tất.');
        // Get room names from contracts that were updated
        $updatedRooms = Contract::where('status', '=', 'Kết thúc sớm')
            ->where('early_terminated_at', '<', now())
            ->with('room')
            ->get()
            ->pluck('room.name')
            ->implode(', ');
        $this->info("Đã cập nhật trạng thái phòng {$updatedRooms} sang \"Sửa chữa\".");
        return 0;
    }

    private function checkEarlyTerminated()
    {
        $contracts = Contract::where('status', '=', 'Kết thúc sớm')
            ->where('early_terminated_at', '<', now())
            ->get();

        foreach ($contracts as $contract) {
            // Xử lý hợp đồng đã kết thúc sớm
            $this->info("Hợp đồng ID: {$contract->id} đã kết thúc sớm vào {$contract->early_terminated_at}");
            // thay đổi trạng thái phòng trong hợp đồng sang "Sửa chữa"
            $contract->room->status = 'Sửa chữa';
            $contract->room->save();
        }
    }   

}
