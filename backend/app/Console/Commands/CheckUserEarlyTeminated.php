<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Console\Command;

class CheckUserEarlyTeminated extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-user-early-teminated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra người dùng đã kết thúc hợp đồng sớm';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu kiểm tra người dùng đã kết thúc hợp đồng sớm...');
        $this->CheckUserEarlyTerminated();
        $this->info('Kiểm tra người dùng đã kết thúc hợp đồng sớm hoàn tất.');
        return 0;
    }

    private function CheckUserEarlyTerminated(){
        // Lấy tất cả hợp đồng đã kết thúc sớm 10 ngày trước
        $contract = Contract::where('early_terminated_at', '<', now()->subDays(10))
            ->where('status', 'Kết thúc sớm')
            ->get();

        if ($contract->isEmpty()) {
            $this->info('Không có hợp đồng nào đã kết thúc sớm trong 10 ngày qua.');
            return;
        }
        $this->info('Đang kiểm tra người dùng đã kết thúc hợp đồng sớm...');
        // Lấy danh sách người dùng từ hợp đồng
        $users = $contract->pluck('user_id')->unique();
        if ($users->isEmpty()) {
            $this->info('Không có người dùng nào đã kết thúc hợp đồng sớm.');
            return;
        }
        $this->info('Danh sách người dùng đã kết thúc hợp đồng sớm:');
        foreach ($users as $userId) {
            $this->info("Người dùng ID: {$userId}");
        }
        $this->info('Kiểm tra người dùng đã kết thúc hợp đồng sớm hoàn tất.');
        // Xoá cột indentity_document của người dùng và xoá file đó trên máy
        foreach ($users as $userId) {
            $user = User::find($userId);
            if ($user && $user->identity_document) {
                // Xoá file trên máy
                if (file_exists($user->identity_document)) {
                    unlink($user->identity_document);
                }
                // Xoá cột indentity_document
                $user->identity_document = null;
                $user->save();
                $this->info("Đã xoá cột indentity_document của người dùng ID: {$userId}");
            }
        }
    }
}
