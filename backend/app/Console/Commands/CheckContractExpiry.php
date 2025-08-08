<?php

namespace App\Console\Commands;

use App\Mail\AutoEndContractNotification;
use App\Models\Contract;
use App\Models\Checkout;
use App\Models\Config;
use App\Models\Notification;
use App\Models\Invoice;
use App\Jobs\SendContractExpiryNotification;
use App\Jobs\SendOverdueInvoiceNotification;
use App\Jobs\SendCheckoutAutoConfirmedNotification;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Mail;
use App\Mail\ContractExpiryNotification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class CheckContractExpiry extends Command
{
    protected $signature = 'app:check-contract-expiry {--debug : Enable debug mode}';
    protected $description = 'Kiểm tra và gửi thông báo hợp đồng sắp hết hạn, hóa đơn quá hạn và kiểm kê tự động xác nhận';

    /**
     * Phương thức chính của command
     * Điều phối các chức năng kiểm tra và xử lý tự động
     */
    public function handle()
    {
        $debug = $this->option('debug');

        $this->info("🔍 Bắt đầu kiểm tra hợp đồng sắp hết hạn, hóa đơn quá hạn và kiểm kê tự động...");

        // Kiểm tra hợp đồng sắp hết hạn
        $this->checkContractExpiry($debug);

        // Kiểm tra hóa đơn quá hạn
        $this->checkOverdueInvoices($debug);

        // Kiểm tra và xử lý kiểm kê tự động xác nhận
        $this->processAutoConfirmedCheckouts($debug);

        // Kiểm tra và kết thúc hợp đồng đã hoàn tất checkout
        $this->checkCompletedCheckouts($debug);

        // Kiểm tra và kết thúc hợp đồng đã hết hạn
        $this->checkEndDateContracts($debug);

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

    /**
     * Kiểm tra và thông báo hóa đơn quá hạn thanh toán
     */
    private function checkOverdueInvoices($debug)
    {
        $this->info("💰 === KIỂM TRA HÓA ĐƠN QUÁ HẠN ===");

        $today = Carbon::today();
        $currentDay = $today->day;

        if ($currentDay <= 5) {
            $this->info("📅 Hiện tại đang trong thời hạn thanh toán (ngày 1-5), bỏ qua kiểm tra hóa đơn quá hạn.");
            return;
        }

        $paymentDeadline = Carbon::create($today->year, $today->month, 5);

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
     * Kiểm tra và tự động xác nhận kiểm kê quá 7 ngày
     */
    private function processAutoConfirmedCheckouts($debug)
    {
        $this->info("🔍 === KIỂM TRA KIỂM KÊ TỰ ĐỘNG XÁC NHẬN ===");

        $today = Carbon::today();
        $sevenDaysAgo = $today->copy()->subDays(7);

        $pendingCheckouts = Checkout::with(['contract.user', 'contract.room'])
            ->where('inventory_status', 'Đã kiểm kê')
            ->where('user_confirmation_status', 'Chưa xác nhận')
            ->where('updated_at', '<=', $sevenDaysAgo)
            ->get();

        $this->info("📊 Tìm thấy {$pendingCheckouts->count()} kiểm kê chưa xác nhận quá 7 ngày");

        if ($debug) {
            $this->showCheckoutDebugInfo($pendingCheckouts, $today); // Thêm debug cho checkout
        }

        if ($pendingCheckouts->isEmpty()) {
            $this->info('ℹ️ Không có kiểm kê nào cần xác nhận tự động.');
        } else {
            $jobsDispatched = 0;

            foreach ($pendingCheckouts as $checkout) {
                try {
                    SendCheckoutAutoConfirmedNotification::dispatch($checkout, $checkout->contract->user, $checkout->contract->room);

                    $jobsDispatched++;
                    $this->info("📤 Job dispatched for auto-confirmed checkout #{$checkout->id} (User: " . ($checkout->contract->user->name ?? 'N/A') . ")");

                    $checkout->update(['user_confirmation_status' => 'Đồng ý']);

                } catch (\Exception $e) {
                    $this->error("❌ Error dispatching job for checkout #{$checkout->id}: " . $e->getMessage());
                    Log::error("Error in processAutoConfirmedCheckouts for checkout #{$checkout->id}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            $this->info("📈 Kết quả: {$jobsDispatched} jobs đã được dispatch cho kiểm kê tự động");
        }
    }

    /**
     * Kiểm tra và kết thúc hợp đồng đã hoàn tất checkout
     */
    private function checkCompletedCheckouts($debug)
    {
        $this->info("✅ === KIỂM TRA CHECKOUT HOÀN TẤT ===");

        // Tìm các checkout đã hoàn tất tất cả điều kiện
        $completedCheckouts = Checkout::with(['contract.user', 'contract.room.motel'])
            ->where('inventory_status', 'Đã kiểm kê')
            ->where('user_confirmation_status', 'Đồng ý')
            ->where('refund_status', 'Đã xử lí')
            ->whereHas('contract', function ($query) {
                $query->where('status', '!=', 'Kết thúc'); // Chỉ lấy hợp đồng chưa kết thúc
            })
            ->get();

        $this->info("📊 Tìm thấy {$completedCheckouts->count()} checkout hoàn tất");

        if ($debug) {
            $this->showCompletedCheckoutsDebugInfo($completedCheckouts);
        }

        if ($completedCheckouts->isEmpty()) {
            $this->info('ℹ️ Không có checkout hoàn tất nào cần xử lý.');
            return;
        }

        $contractsEnded = 0;

        foreach ($completedCheckouts as $checkout) {
            try {
                $contract = $checkout->contract;

                if (!$contract) {
                    $this->warn("⚠️ Checkout #{$checkout->id} không có hợp đồng liên kết");
                    continue;
                }

                // Kết thúc hợp đồng
                $contract->update(['status' => 'Kết thúc']);
                $contractsEnded++;

                $userName = $contract->user->name ?? 'N/A';
                $roomName = $contract->room->name ?? 'N/A';

                $this->info("✅ Kết thúc hợp đồng #{$contract->id} từ checkout #{$checkout->id} (User: {$userName}, Room: {$roomName})");

                // Tạo thông báo cho user
                $this->createContractEndNotification($contract, $checkout);

                // Gửi email thông báo
                $this->sendCheckoutCompletedEmail($contract, $checkout);

                // Gửi FCM notification
                if ($contract->user && $contract->user->fcm_token) {
                    $this->sendContractEndFcmNotification($contract->user, $contract, $checkout);
                }

            } catch (\Exception $e) {
                $this->error("❌ Lỗi khi kết thúc hợp đồng từ checkout #{$checkout->id}: " . $e->getMessage());
                Log::error("Error ending contract from completed checkout", [
                    'checkout_id' => $checkout->id,
                    'contract_id' => $checkout->contract_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("📈 Kết quả: Đã kết thúc {$contractsEnded} hợp đồng từ checkout hoàn tất");
    }

    /**
     * Gửi email thông báo khi hợp đồng kết thúc do hoàn tất checkout
     */
    private function sendCheckoutCompletedEmail($contract, $checkout)
    {
        try {
            if (!$contract->user || !$contract->user->email) {
                $this->warn("⚠️ User #{$contract->user_id} không có email, bỏ qua gửi mail");
                return;
            }

            // Tạo data cho email
            $emailData = [
                'contract' => $contract,
                'user_name' => $contract->user->name,
                'room_name' => $contract->room->name ?? 'N/A',
                'motel_name' => $contract->room->motel->name ?? 'N/A',
                'end_date' => Carbon::now()->format('d/m/Y'),
                'end_reason' => 'Hoàn tất quá trình checkout',
                'notification_type' => 'checkout_completed',
                'checkout_id' => $checkout->id
            ];

            // Gửi email
            Mail::to($contract->user->email)->send(new ContractExpiryNotification($emailData));

            $this->info("📧 Đã gửi email thông báo kết thúc hợp đồng (checkout hoàn tất) cho {$contract->user->email}");

            Log::info('Checkout completed contract end email sent', [
                'contract_id' => $contract->id,
                'checkout_id' => $checkout->id,
                'user_id' => $contract->user_id,
                'email' => $contract->user->email
            ]);

        } catch (\Exception $e) {
            $this->error("❌ Lỗi gửi email cho checkout #{$checkout->id}: " . $e->getMessage());

            Log::error("Error sending checkout completed email", [
                'contract_id' => $contract->id,
                'checkout_id' => $checkout->id,
                'user_id' => $contract->user_id,
                'email' => $contract->user->email ?? 'N/A',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Kiểm tra và tự động kết thúc hợp đồng đã hết hạn
     */
    private function checkEndDateContracts($debug)
    {
        $this->info("🔒 === TỰ ĐỘNG KẾT THÚC HỢP ĐỒNG ===");

        $today = Carbon::today();
        // Thêm with() để load relationships
        $expiredContracts = Contract::with(['user', 'room.motel'])
            ->where('status', 'Hoạt động')
            ->where('end_date', '<=', $today)
            ->get();

        if ($expiredContracts->isEmpty()) {
            $this->info('ℹ️ Không có hợp đồng nào cần kết thúc.');
            return;
        }

        $this->info("📊 Tìm thấy {$expiredContracts->count()} hợp đồng đã hết hạn");

        if ($debug) {
            $this->info("🔧 DEBUG MODE - HỢP ĐỒNG ĐÃ HẾT HẠN:");
            foreach ($expiredContracts->take(10) as $contract) {
                $this->info("   - ID: {$contract->id} | End: " . Carbon::parse($contract->end_date)->format('d/m/Y') . " | User: " . ($contract->user->name ?? 'N/A'));
            }
        }

        foreach ($expiredContracts as $contract) {
            $this->autoEndContract($contract);
        }

        $this->info("📈 Kết quả: Đã kết thúc " . $expiredContracts->count() . " hợp đồng hết hạn.");
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
     * Tự động kết thúc hợp đồng hết hạn
     */
    private function autoEndContract($contract)
    {
        $this->info("🔒 Tự động kết thúc hợp đồng #{$contract->id} (User: " . ($contract->user->name ?? 'N/A') . ")");

        $contract->status = 'Kết thúc';
        $contract->save();
        $checkout = Checkout::where('contract_id', $contract->id)->first();

        Room::where('id', $checkout->contract->room_id)->update([
                        'status' => 'Sửa chữa',
                    ]);

                    // Cập nhật vai trò người dùng thành "Người đăng ký"
                    $user = $checkout->contract->user;
                    if ($user) {
                        // Xóa identity_document nếu tồn tại
                        if ($user->identity_document && Storage::disk('private')->exists($user->identity_document)) {
                            Storage::disk('private')->delete($user->identity_document);
                            Log::info('Identity document deleted', [
                                'user_id' => $user->id,
                                'document_path' => $user->identity_document,
                            ]);
                        }

                        User::where('id', $user->id)->update([
                            'role' => 'Người đăng ký',
                            'identity_document' => null,
                        ]);

                        Log::info('User role updated to Người đăng ký and identity_document cleared', [
                            'user_id' => $user->id,
                            'checkout_id' => $checkout->id,
                            'contract_id' => $checkout->contract->id,
                        ]);
                    } else {
                        Log::warning('User not found for role update', [
                            'checkout_id' => $checkout->id,
                            'contract_id' => $checkout->contract->id,
                        ]);
                    }

        $this->info("✅ Hợp đồng #{$contract->id} đã được kết thúc");

        // Tạo thông báo trong database
        $this->createAutoEndContractNotification($contract);

        // Gửi email thông báo
        $this->sendAutoEndContractEmail($contract);

        // Gửi thông báo FCM nếu user có FCM token
        if ($contract->user && $contract->user->fcm_token) {
            $notificationData = [
                'title' => 'Hợp đồng đã kết thúc',
                'body' => "Hợp đồng #{$contract->id} đã được kết thúc tự động."
            ];
            $this->sendFcmNotification($contract->user, $notificationData, $contract, 0);
        } else {
            $this->warn("⚠️ User #{$contract->user_id} không có FCM token, bỏ qua gửi FCM");
        }
    }

    /**
     * Gửi email thông báo khi hợp đồng kết thúc tự động
     */
    private function sendAutoEndContractEmail($contract)
    {
        try {
            if (!$contract->user || !$contract->user->email) {
                $this->warn("⚠️ User #{$contract->user_id} không có email, bỏ qua gửi mail");
                return;
            }

            // Load relationships nếu chưa có
            if (!$contract->relationLoaded('room')) {
                $contract->load('room.motel.user');
            }

            // ✅ TRUYỀN OBJECT CONTRACT đã load đầy đủ relationships
            Mail::to($contract->user->email)->send(new AutoEndContractNotification($contract));

            $this->info("📧 Đã gửi email thông báo kết thúc hợp đồng tự động cho {$contract->user->email}");

            Log::info('Auto contract end email sent', [
                'contract_id' => $contract->id,
                'user_id' => $contract->user_id,
                'email' => $contract->user->email,
                'end_date' => $contract->end_date,
                'room_id' => $contract->room_id,
                'motel_id' => $contract->room->motel_id ?? null
            ]);

        } catch (\Exception $e) {
            $this->error("❌ Lỗi gửi email tự động kết thúc hợp đồng #{$contract->id}: " . $e->getMessage());
            Log::error("Error sending auto contract end email", [
                'contract_id' => $contract->id,
                'user_id' => $contract->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    //-------------------------------------------------------------------
    // PHƯƠNG THỨC TẠO VÀ GỬI THÔNG BÁO
    //-------------------------------------------------------------------

    /**
     * Tạo thông báo khi hợp đồng kết thúc do hoàn tất checkout
     */
    private function createContractEndNotification($contract, $checkout)
    {
        try {
            Notification::create([
                'user_id' => $contract->user_id,
                'title' => 'Hợp đồng đã kết thúc',
                'content' => "Hợp đồng #{$contract->id} đã được kết thúc sau khi hoàn tất quá trình checkout. Phòng: " . ($contract->room->name ?? 'N/A'),
                'type' => 'contract_ended',
                'is_read' => false,
                'data' => json_encode([
                    'contract_id' => $contract->id,
                    'checkout_id' => $checkout->id,
                    'room_name' => $contract->room->name ?? 'N/A',
                    'motel_name' => $contract->room->motel->name ?? 'N/A',
                    'end_reason' => 'checkout_completed'
                ])
            ]);

            $this->info("📢 Đã tạo thông báo cho user #{$contract->user_id}");

        } catch (\Exception $e) {
            $this->warn("⚠️ Không thể tạo thông báo cho hợp đồng #{$contract->id}: " . $e->getMessage());
        }
    }

    /**
     * Tạo thông báo khi hợp đồng kết thúc tự động do hết hạn
     */
    private function createAutoEndContractNotification($contract)
    {
        try {
            Notification::create([
                'user_id' => $contract->user_id,
                'title' => 'Hợp đồng đã kết thúc',
                'content' => "Hợp đồng #{$contract->id} đã được kết thúc tự động do hết hạn. Phòng: " . ($contract->room->name ?? 'N/A'),
                'type' => 'contract_ended',
                'is_read' => false,
                'data' => json_encode([
                    'contract_id' => $contract->id,
                    'room_name' => $contract->room->name ?? 'N/A',
                    'motel_name' => $contract->room->motel->name ?? 'N/A',
                    'end_reason' => 'auto_expired'
                ])
            ]);

            $this->info("📢 Đã tạo thông báo cho user #{$contract->user_id}");

        } catch (\Exception $e) {
            $this->warn("⚠️ Không thể tạo thông báo cho hợp đồng #{$contract->id}: " . $e->getMessage());
        }
    }

    /**
     * Gửi thông báo FCM khi hợp đồng kết thúc do hoàn tất checkout
     */
    private function sendContractEndFcmNotification($user, $contract, $checkout)
    {
        try {
            $messaging = app('firebase.messaging');

            $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(FirebaseNotification::create(
                    'Hợp đồng đã kết thúc',
                    "Hợp đồng #{$contract->id} đã được kết thúc sau khi hoàn tất checkout"
                ))
                ->withData([
                    'type' => 'contract_ended',
                    'contract_id' => (string) $contract->id,
                    'checkout_id' => (string) $checkout->id,
                    'room_name' => $contract->room->name ?? '',
                    'motel_name' => $contract->room->motel->name ?? '',
                    'end_reason' => 'checkout_completed',
                    'action_url' => url("/contracts/{$contract->id}")
                ]);

            $messaging->send($fcmMessage);

            $this->info("📱 Đã gửi FCM notification cho user #{$user->id}");

            Log::info('Contract end FCM sent from completed checkout', [
                'user_id' => $user->id,
                'contract_id' => $contract->id,
                'checkout_id' => $checkout->id,
                'fcm_token' => substr($user->fcm_token, 0, 20) . '...'
            ]);

        } catch (\Exception $e) {
            $this->warn("⚠️ Không thể gửi FCM cho user #{$user->id}: " . $e->getMessage());
            Log::error("Error sending contract end FCM", [
                'user_id' => $user->id,
                'contract_id' => $contract->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Gửi thông báo FCM chung
     */
    private function sendFcmNotification($user, $notificationData, $contract, $daysRemaining)
    {
        try {
            // Kiểm tra FCM token
            if (!$user->fcm_token) {
                $this->warn("⚠️ User #{$user->id} không có FCM token");
                return;
            }

            $messaging = app('firebase.messaging');

            // Tạo message content dựa trên số ngày còn lại
            $messageBody = $daysRemaining > 0
                ? "Hợp đồng #{$contract->id} sẽ hết hạn sau {$daysRemaining} ngày"
                : "Hợp đồng #{$contract->id} đã được kết thúc tự động";

            $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(FirebaseNotification::create(
                    $notificationData['title'],
                    $messageBody
                ))
                ->withData([
                    'type' => $daysRemaining > 0 ? 'contract_expiry' : 'contract_ended',
                    'contract_id' => (string) $contract->id,
                    'days_remaining' => (string) $daysRemaining,
                    'end_date' => $contract->end_date->format('Y-m-d'),
                    'room_name' => $contract->room->name ?? '',
                    'motel_name' => $contract->room->motel->name ?? '',
                    'action_url' => url("/contracts/{$contract->id}")
                ]);

            $messaging->send($fcmMessage);

            $this->info("📱 Đã gửi FCM notification cho user #{$user->id}");

            Log::info('Contract FCM sent', [
                'user_id' => $user->id,
                'contract_id' => $contract->id,
                'days_remaining' => $daysRemaining,
                'fcm_token' => substr($user->fcm_token, 0, 20) . '...'
            ]);

        } catch (\Exception $e) {
            $this->warn("⚠️ Không thể gửi FCM cho user #{$user->id}: " . $e->getMessage());
            Log::error("Error sending FCM notification", [
                'user_id' => $user->id,
                'contract_id' => $contract->id,
                'error' => $e->getMessage()
            ]);
        }
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

    /**
     * Hiển thị thông tin debug cho kiểm kê
     */
    private function showCheckoutDebugInfo($pendingCheckouts, $today)
    {
        $this->info("🔧 DEBUG MODE - KIỂM KÊ CHƯA XÁC NHẬN:");

        foreach ($pendingCheckouts->take(10) as $checkout) {
            $updatedAt = Carbon::parse($checkout->updated_at);
            $daysSinceUpdated = $today->diffInDays($updatedAt);
            $this->info("   - Checkout ID: {$checkout->id} | Update: {$checkout->updated_at} | Days: {$daysSinceUpdated}");
        }
    }

    /**
     * Hiển thị thông tin debug cho checkout hoàn tất
     */
    private function showCompletedCheckoutsDebugInfo($completedCheckouts)
    {
        $this->info("🔧 DEBUG MODE - CHECKOUT HOÀN TẤT:");

        foreach ($completedCheckouts->take(10) as $checkout) {
            $contract = $checkout->contract;
            $userName = $contract->user->name ?? 'N/A';
            $roomName = $contract->room->name ?? 'N/A';
            $contractStatus = $contract->status ?? 'N/A';

            $this->info("   - Checkout ID: {$checkout->id} | Contract ID: {$checkout->contract_id} | Status: {$contractStatus}");
            $this->info("     User: {$userName} | Room: {$roomName}");
            $this->info("     Inventory: {$checkout->inventory_status} | Confirmation: {$checkout->user_confirmation_status} | Refund: {$checkout->refund_status}");
        }
    }
}
