<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;

class Kernel extends ConsoleKernel
{


    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:check-contract-expiry')->everyMinute()->withoutOverlapping();
        // log để kiểm tra xem có lỗi gì không
        $schedule->call(function () {
            \Log::info('Simple closure schedule is running.');
        })->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
