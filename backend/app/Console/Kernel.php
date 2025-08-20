<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ✅ Kiểm tra hợp đồng sắp hết hạn (3 ngày trước)
        $schedule->command('contracts:check-expiring --days=3')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'))
            ->emailOutputOnFailure(config('mail.admin_email', 'sghoodvn@gmail.com'));

        // ✅ Kiểm tra hợp đồng sắp hết hạn (1 ngày trước) - Khẩn cấp
        $schedule->command('contracts:check-expiring --days=1')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // ✅ Kiểm tra hợp đồng hết hạn hôm nay
        $schedule->command('contracts:check-expiring --days=0')
            ->dailyAt('07:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // ✅ Existing commands với improvements


        // Kiểm tra hợp đồng sắp hết hạn 15 ngày
        $schedule->command('app:check-contract-expiry')
            ->dailyAt('08:30')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // ✅ Kiểm tra hóa đơn quá hạn
        $schedule->command('app:check-overdue-invoice')
            ->dailyAt('09:30')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // ✅ Tự động đồng ý checkout
        $schedule->command('app:process-auto-confirmed-checkout')
            ->dailyAt('10:30')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // ✅ Kiểm tra hợp đồng đã kết thúc
        $schedule->command('app:check-end-date-contract')
            ->dailyAt('11:30')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // ✅ Kiểm tra hợp đồng đã kết thúc sớm
        $schedule->command('app:check-early-terminated')
            ->dailyAt('12:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // ✅ Kiểm tra người dùng đã kết thúc hợp đồng sớm sau 10 ngày và xoá cột identity_document
        $schedule->command('app:check-user-early-teminated')
            ->dailyAt('15:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'));


        // ✅ Kiểm tra thêm vào buổi chiều
        $schedule->command('contracts:check-expiring --days=3')
            ->dailyAt('14:00')
            ->withoutOverlapping()
            ->runInBackground();




        // ✅ Clean up old notifications (mỗi tuần)
        $schedule->command('notifications:cleanup')
            ->weekly()
            ->sundays()
            ->at('02:00')
            ->withoutOverlapping()
            ->runInBackground();

        // ✅ Backup database (mỗi ngày lúc 2:00 sáng)
        $schedule->command('backup:database')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();

        // ✅ Clear old logs (mỗi tuần)
        $schedule->command('log:clear')
            ->weekly()
            ->mondays()
            ->at('03:00')
            ->withoutOverlapping();

        // ✅ Queue work để xử lý jobs (nếu không dùng supervisor)
        $schedule->command('queue:work --daemon --timeout=60 --tries=3')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        // ✅ Health check log (mỗi phút trong development, mỗi 5 phút trong production)
        if (app()->environment('local', 'development')) {
            $schedule->call(function () {
                Log::info('Scheduler health check - Development mode', [
                    'timestamp' => now()->toDateTimeString(),
                    'memory_usage' => memory_get_usage(true),
                    'environment' => app()->environment()
                ]);
            })->everyMinute();
        } else {
            $schedule->call(function () {
                Log::info('Scheduler health check - Production mode', [
                    'timestamp' => now()->toDateTimeString(),
                    'memory_usage' => memory_get_usage(true),
                    'environment' => app()->environment()
                ]);
            })->everyFiveMinutes();
        }

        // ✅ Maintenance tasks
        $schedule->command('cache:clear')
            ->weekly()
            ->sundays()
            ->at('01:00');

        $schedule->command('view:clear')
            ->weekly()
            ->sundays()
            ->at('01:15');

        $schedule->command('route:clear')
            ->weekly()
            ->sundays()
            ->at('01:30');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}