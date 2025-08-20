<?php

namespace App\Console\Commands;

use App\Jobs\SendNotificationForAdmin;
use Illuminate\Console\Command;
use App\Models\Contract;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class CheckContractExpiryForMeterReading extends Command
{
    protected $signature = 'contracts:check-expiring {--days=3 : Số ngày trước khi hết hạn} {--force : Force send even if notifications exist}';
    protected $description = 'Kiểm tra và thông báo các hợp đồng sắp hết hạn để nhập chỉ số điện nước';

    public function handle()
    {
        $days = (int) $this->option('days');
        $force = $this->option('force');
        
        $this->info("Đang kiểm tra hợp đồng sắp hết hạn trong {$days} ngày...");
        
        // ✅ Check queue configuration
        $this->checkQueueConfiguration();

        try {
            $expiringContracts = $this->getExpiringContracts($days);
            
            if ($expiringContracts->isEmpty()) {
                $this->info('Không có hợp đồng nào sắp hết hạn.');
                return Command::SUCCESS;
            }

            $this->info("Tìm thấy {$expiringContracts->count()} hợp đồng sắp hết hạn:");

            $notificationCount = $this->createNotifications($expiringContracts, $days, $force);
            $jobCount = $this->sendNotificationsToAdmin($expiringContracts, $days);

            $this->info("Đã tạo {$notificationCount} thông báo và dispatch {$jobCount} jobs gửi FCM + Email cho admin.");

            
            return Command::SUCCESS;


        } catch (\Exception $e) {
            $this->error('Lỗi khi kiểm tra hợp đồng sắp hết hạn: ' . $e->getMessage());
            Log::error('Error in CheckExpiringContracts command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * ✅ Check queue configuration
     */
    private function checkQueueConfiguration()
    {
        $this->info("=== Queue Configuration ===");
        $this->line("Queue Connection: " . config('queue.default'));
        $this->line("Queue Driver: " . config('queue.connections.' . config('queue.default') . '.driver'));
        
        // Check if using database queue
        if (config('queue.default') === 'database') {
            $pendingJobs = \DB::table('jobs')->count();
            $this->line("Pending jobs in database: {$pendingJobs}");
        }
        
        $this->line("=== End Queue Configuration ===\n");
    }

    private function getExpiringContracts(int $days)
    {
        $fromDate = Carbon::now()->addDays($days)->startOfDay();
        $toDate = Carbon::now()->addDays($days)->endOfDay();

        return Contract::with(['room.motel', 'user'])
            ->where('status', 'Hoạt động')
            ->whereBetween('end_date', [$fromDate, $toDate])
            ->get();
    }

    private function createNotifications($expiringContracts, int $days, bool $force = false)
    {
        $admins = User::whereIn('role', ['Quản trị viên', 'Super admin'])->get();
        $notificationCount = 0;

        foreach ($expiringContracts as $contract) {
            $this->line("- Hợp đồng #{$contract->id}: Phòng {$contract->room->room_number} - Hết hạn: {$contract->end_date}");

            foreach ($admins as $admin) {
                $existingNotification = $this->checkExistingNotification($admin->id, $contract->id);

                if (!$existingNotification || $force) {
                    if ($existingNotification && $force) {
                        $this->line("  → Force creating notification (existing one found)");
                    }
                    
                    $notification = $this->createNotificationForContract($admin, $contract, $days);
                    if ($notification) {
                        $notificationCount++;
                    }
                } else {
                    $this->line("  → Notification already exists for admin {$admin->name}");
                }
            }
        }

        return $notificationCount;
    }

    private function checkExistingNotification($adminId, $contractId)
    {
        return Notification::where('user_id', $adminId)
            ->where('title', 'Hợp đồng sắp hết hạn - Cần nhập chỉ số điện nước')
            ->where('content', 'LIKE', "%Hợp đồng #{$contractId}%")
            ->whereDate('created_at', Carbon::today())
            ->first();
    }

    private function createNotificationForContract(User $admin, Contract $contract, int $days)
    {
        try {
            $roomNumber = $contract->room->room_number ?? 'N/A';
            $motelName = $contract->room->motel->name ?? 'N/A';
            $tenantName = $contract->user->name ?? 'N/A';
            $endDate = Carbon::parse($contract->end_date)->format('d/m/Y');

            $title = "Hợp đồng sắp hết hạn - Cần nhập chỉ số điện nước";
            
            $content = "Hợp đồng #{$contract->id} - Phòng {$roomNumber} ({$motelName}) của khách {$tenantName} sẽ hết hạn vào {$endDate}. Vui lòng nhập chỉ số điện nước cuối kỳ.\n\n";
            $content .= "Chi tiết:\n";
            $content .= "- Mã hợp đồng: {$contract->id}\n";
            $content .= "- Phòng: {$roomNumber}\n";
            $content .= "- Nhà trọ: {$motelName}\n";
            $content .= "- Khách thuê: {$tenantName}\n";
            $content .= "- Ngày hết hạn: {$endDate}\n";
            $content .= "- Còn lại: {$days} ngày\n";
            $content .= "- Hành động cần thực hiện: Nhập chỉ số điện nước";

            return Notification::create([
                'user_id' => $admin->id,
                'title' => $title,
                'content' => $content,
                'status' => 'Chưa đọc',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating notification for contract', [
                'contract_id' => $contract->id,
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * ✅ Gửi thông báo cho admin (FCM + Email) với debugging
     */
    private function sendNotificationsToAdmin($expiringContracts, int $days)
    {
        $jobCount = 0;
        
        try {
            foreach ($expiringContracts as $contract) {
                $this->line("  → Dispatching job for contract #{$contract->id}...");
                
                // ✅ Try different dispatch methods based on queue configuration
                $queueConnection = config('queue.default');
                
                if ($queueConnection === 'sync') {
                    // ✅ For sync queue, job runs immediately
                    $this->warn("Warning: Using sync queue - job will run immediately");
                    SendNotificationForAdmin::dispatch($contract, $days);
                } else {
                    // ✅ For other queues, add to queue
                    SendNotificationForAdmin::dispatch($contract, $days)
                        // ->onQueue('notifications')
                        ->delay(now()->addSeconds(5));
                }
                
                $jobCount++;
                
                // ✅ Log the dispatch
                Log::info("Job dispatched for contract expiry notification", [
                    'contract_id' => $contract->id,
                    'days_remaining' => $days,
                    'queue_connection' => $queueConnection,
                    'job_class' => SendNotificationForAdmin::class
                ]);
                
                $this->info("    ✓ Job dispatched successfully");
            }

            $this->info("Đã dispatch {$jobCount} notification jobs cho admin.");
            
            // ✅ If using database queue, show pending jobs
            if (config('queue.default') === 'database') {
                $pendingJobs = \DB::table('jobs')->count();
                $this->info("Total pending jobs in database: {$pendingJobs}");
            }

        } catch (\Exception $e) {
            $this->error('Error dispatching admin notifications: ' . $e->getMessage());
            Log::error('Error dispatching admin notifications', [
                'error' => $e->getMessage(),
                'contracts_count' => $expiringContracts->count(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return $jobCount;
    }
}