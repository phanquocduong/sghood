<?php

namespace App\Console\Commands;

use App\Jobs\SendContractTenantStatusNotification;
use App\Models\Checkout;
use App\Models\Contract;
use App\Models\ContractTenant;
use App\Models\User;
use Illuminate\Console\Command;
use App\Mail\AutoEndContractNotification;
use App\Models\Notification;
use App\Models\Room;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Mail;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class CheckEndDateContract extends Command
{
    protected $signature = 'app:check-end-date-contract {--debug : Enable debug mode}';
    protected $description = 'Kiểm tra và kết thúc hợp đồng đã hoàn tất checkout hoặc hết hạn';

    public function handle()
    {
        $debug = $this->option('debug');

        $this->info("🔍 Bắt đầu kiểm tra các trạng thái kiểm kê và tự động kết thúc hợp đồng...");

        // B1: Xử lý các checkout đã hoàn tất (kết thúc hợp đồng từ checkout)
        $this->processCompletedCheckouts($debug);

        // B2: Chỉ auto end nếu KHÔNG có checkout "blocking"
        if (!$this->hasBlockingCheckouts()) {
            $this->checkEndDateContracts($debug);
        } else {
            $blocking = $this->countBlockingCheckouts();
            $this->info("ℹ️ Có {$blocking} checkout chưa đủ điều kiện ⇒ bỏ qua auto end theo ngày.");
        }
    }

    /**
     * Kiểm tra và kết thúc hợp đồng đã hoàn tất checkout
     * @return bool True nếu có checkout hợp lệ được xử lý
     */
    private function checkCompletedCheckouts($debug)
    {
        $this->info("✅ === KIỂM TRA CHECKOUT HOÀN TẤT ===");

        // Tìm các checkout đã hoàn tất tất cả điều kiện
        $completedCheckouts = Checkout::with(['contract.user', 'contract.room.motel'])
            ->where('inventory_status', 'Đã kiểm kê')
            ->where('user_confirmation_status', 'Đồng ý')
            ->where('refund_status', 'Đã xử lý')
            ->whereHas('contract', function ($query) {
                $query->where('status', '=', 'Hoạt động')
                    ->where('end_date', '<=', Carbon::today());
            })
            ->get();

        $this->info("📊 Tìm thấy {$completedCheckouts->count()} checkout hoàn tất");
        // Chỉ tiếp tục nếu có checkout hoàn tất
        if ($completedCheckouts->count() === 0) {
            $this->info('ℹ️ Không có checkout hoàn tất nào.');
            return false;
        }

        if ($debug) {
            $this->showCompletedCheckoutsDebugInfo($completedCheckouts);
        }

        if ($completedCheckouts->isEmpty()) {
            $this->info('ℹ️ Không có checkout hoàn tất nào.');
            return false;
        }

        $contractsEnded = 0;
        $validCheckoutsProcessed = 0;

        foreach ($completedCheckouts as $checkout) {
            try {
                $contract = $checkout->contract;

                if (!$contract) {
                    $this->warn("⚠️ Checkout #{$checkout->id} không có hợp đồng liên kết");
                    continue;
                }

                // Kiểm tra lại tính hợp lệ của checkout trước khi kết thúc hợp đồng
                if (!$this->isCheckoutValid($checkout)) {
                    $this->warn("⚠️ Checkout #{$checkout->id} không hợp lệ, bỏ qua");
                    continue;
                }

                $validCheckoutsProcessed++;

                // Kết thúc hợp đồng và xử lý các tác vụ liên quan
                $this->endContractFromCheckout($contract, $checkout);
                $contractsEnded++;

                $userName = $contract->user->name ?? 'N/A';
                $roomName = $contract->room->name ?? 'N/A';

                $this->info("✅ Kết thúc hợp đồng #{$contract->id} từ checkout #{$checkout->id} (User: {$userName}, Room: {$roomName})");

                // Tạo thông báo cho user
                $this->createContractEndNotification($contract, $checkout);

                // Gửi email thông báo
                $this->sendCheckoutCompletedEmail($contract, $checkout);

                // Gửi FCM notification
                if ($contract->user?->fcm_token) {
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

        $this->info("📈 Kết quả: Đã kết thúc {$contractsEnded} hợp đồng từ {$validCheckoutsProcessed} checkout hợp lệ");

        return $validCheckoutsProcessed > 0;
    }


    /**
     * Cập nhật trạng thái & xoá identity_document cho tất cả người ở cùng của hợp đồng.
     * Có xử lý xoá file trên disk, và log số dòng cập nhật.
     */
    private function updateAndClearCoTenants(Contract $contract): void
    {
        // Nếu có global scope/soft delete, cân nhắc dùng withoutGlobalScopes()/withTrashed()
        $tenants = ContractTenant::where('contract_id', $contract->id)->get();

        if ($tenants->isEmpty()) {
            Log::info('Co-tenant: không có người ở cùng cho hợp đồng', ['contract_id' => $contract->id]);
            return;
        }

        // Xoá file của từng người ở cùng (nếu có)
        foreach ($tenants as $t) {
            try {
                if (!empty($t->identity_document) && Storage::disk('private')->exists($t->identity_document)) {
                    Storage::disk('private')->delete($t->identity_document);
                    Log::info('Co-tenant: đã xoá file identity_document', [
                        'contract_id' => $contract->id,
                        'tenant_id' => $t->id,
                        'path' => $t->identity_document,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Co-tenant: lỗi xoá file identity_document', [
                    'contract_id' => $contract->id,
                    'tenant_id' => $t->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Bulk update trạng thái + xoá reference trong DB
        $affected = ContractTenant::where('contract_id', $contract->id)
            ->update([
                'status' => 'Đã rời đi',
                'identity_document' => null,
                'updated_at' => now(),
            ]);

        Log::info('Co-tenant: cập nhật trạng thái & xoá identity_document', [
            'contract_id' => $contract->id,
            'tenant_ids' => $tenants->pluck('id')->all(),
            'affected_rows' => $affected,
        ]);
    }


    private function hasBlockingCheckouts(): bool
    {
        $today = Carbon::today();

        $count = Checkout::whereHas('contract', function ($q) use ($today) {
            $q->where('status', 'Hoạt động')
                ->where('end_date', '<=', $today);
        })
            ->where(function ($q) {
                $q->whereNull('inventory_status')
                    ->orWhere('inventory_status', '!=', 'Đã kiểm kê')
                    ->orWhereNull('user_confirmation_status')
                    ->orWhere('user_confirmation_status', '!=', 'Đồng ý')
                    ->orWhereNull('refund_status')
                    ->orWhere('refund_status', '!=', 'Đã xử lý');
            })
            ->count();

        return $count > 0;
    }

    private function countBlockingCheckouts(): int
    {
        $today = Carbon::today();

        return Checkout::whereHas('contract', function ($q) use ($today) {
            $q->where('status', 'Hoạt động')
                ->where('end_date', '<=', $today);
        })
            ->where(function ($q) {
                $q->whereNull('inventory_status')
                    ->orWhere('inventory_status', '!=', 'Đã kiểm kê')
                    ->orWhereNull('user_confirmation_status')
                    ->orWhere('user_confirmation_status', '!=', 'Đồng ý')
                    ->orWhereNull('refund_status')
                    ->orWhere('refund_status', '!=', 'Đã xử lý');
            })
            ->count();
    }


    /**
     * Kết thúc hợp đồng từ checkout và xử lý các tác vụ liên quan
     */
    private function endContractFromCheckout($contract, $checkout)
    {
        // Kết thúc hợp đồng
        $contract->update(['status' => 'Kết thúc']);

        // Cập nhật trạng thái phòng thành "Sửa chữa"
        if ($contract->room_id) {
            Room::where('id', $contract->room_id)->update([
                'status' => 'Sửa chữa',
            ]);

            Log::info('Room status updated to repair', [
                'room_id' => $contract->room_id,
                'contract_id' => $contract->id,
                'checkout_id' => $checkout->id,
            ]);
        }

        $this->updateAndClearCoTenants($contract);

        // Xóa identity document của user
        $this->clearUserIdentityDocument($contract->user, $contract->id, $checkout->id);
    }




    private function processCompletedCheckouts($debug): void
    {
        $this->info("✅ === KIỂM TRA CHECKOUT HOÀN TẤT ===");

        $completedCheckouts = Checkout::with(['contract.user', 'contract.room.motel'])
            ->where('inventory_status', 'Đã kiểm kê')
            ->where('user_confirmation_status', 'Đồng ý')
            ->where('refund_status', 'Đã xử lý')
            ->whereHas('contract', function ($query) {
                $query->where('status', 'Hoạt động')
                    ->where('end_date', '<=', Carbon::today());
            })
            ->get();

        $this->info("📊 Tìm thấy {$completedCheckouts->count()} checkout hoàn tất");

        if ($debug && $completedCheckouts->isNotEmpty()) {
            $this->showCompletedCheckoutsDebugInfo($completedCheckouts);
        }

        foreach ($completedCheckouts as $checkout) {
            try {
                $contract = $checkout->contract;
                if (!$contract) {
                    $this->warn("⚠️ Checkout #{$checkout->id} không có hợp đồng liên kết");
                    continue;
                }
                if (!$this->isCheckoutValid($checkout)) {
                    $this->warn("⚠️ Checkout #{$checkout->id} không hợp lệ, bỏ qua");
                    continue;
                }

                // ✅ Get co-tenants before ending contract
                $coTenants = ContractTenant::where('contract_id', $contract->id)->get();

                $this->endContractFromCheckout($contract, $checkout);
                $this->createContractEndNotification($contract, $checkout);
                $this->sendCheckoutCompletedEmail($contract, $checkout);

                // ✅ Send notifications to co-tenants
                foreach ($coTenants as $tenant) {
                    $this->sendMailForCoTenant($tenant);
                }

                if ($contract->user?->fcm_token) {
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
    }

    /**
     * Kiểm tra tính hợp lệ của checkout
     */
    private function isCheckoutValid($checkout)
    {
        // Kiểm tra các status theo đúng yêu cầu
        $isInventoryValid = $checkout->inventory_status === 'Đã kiểm kê';
        $isConfirmationValid = $checkout->user_confirmation_status === 'Đồng ý';
        $isRefundValid = $checkout->refund_status === 'Đã xử lý';

        // Kiểm tra hợp đồng còn hoạt động
        $contract = $checkout->contract;
        $isContractActive = $contract && $contract->status === 'Hoạt động';
        $isContractExpired = $contract && $contract->end_date <= Carbon::today();

        $isValid = $isInventoryValid && $isConfirmationValid && $isRefundValid && $isContractActive && $isContractExpired;

        if (!$isValid) {
            $this->warn("⚠️ Checkout #{$checkout->id} validation failed:");
            $this->warn("   - Inventory: {$checkout->inventory_status} (Expected: Đã kiểm kê) - " . ($isInventoryValid ? 'OK' : 'FAIL'));
            $this->warn("   - Confirmation: {$checkout->user_confirmation_status} (Expected: Đồng ý) - " . ($isConfirmationValid ? 'OK' : 'FAIL'));
            $this->warn("   - Refund: {$checkout->refund_status} (Expected: Đã xử lý) - " . ($isRefundValid ? 'OK' : 'FAIL'));
            $this->warn("   - Contract Active: " . ($isContractActive ? 'OK' : 'FAIL'));
            $this->warn("   - Contract Expired: " . ($isContractExpired ? 'OK' : 'FAIL'));
        }

        return $isValid;
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

            // Load relationships if not already loaded
            if (!$contract->relationLoaded('room')) {
                $contract->load('room.motel');
            }

            Mail::to($contract->user->email)->send(new AutoEndContractNotification($contract));

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
                $endDateStr = $contract->end_date ? $contract->end_date->format('d/m/Y') : 'N/A';
                $this->info("   - ID: {$contract->id} | End: {$endDateStr} | User: " . ($contract->user->name ?? 'N/A'));
            }
        }

        foreach ($expiredContracts as $contract) {
            $this->autoEndContract($contract);
        }

        $this->info("📈 Kết quả: Đã kết thúc " . $expiredContracts->count() . " hợp đồng hết hạn.");
    }

    /**
     * Tự động kết thúc hợp đồng hết hạn
     */
    private function autoEndContract($contract)
    {
        $this->info("🔒 Tự động kết thúc hợp đồng #{$contract->id} (User: " . ($contract->user->name ?? 'N/A') . ")");

        // 1) Kết thúc hợp đồng
        $contract->update(['status' => 'Kết thúc']);

        // 2) Đổi trạng thái phòng
        if ($contract->room_id) {
            Room::where('id', $contract->room_id)->update(['status' => 'Sửa chữa']);
            Log::info('Room status updated to repair (auto end)', [
                'room_id' => $contract->room_id,
                'contract_id' => $contract->id,
            ]);
        }

        // 3) ✅ Get all co-tenants before updating their status
        $coTenants = ContractTenant::where('contract_id', $contract->id)->get();

        // Log the co-tenants found
        Log::info('Co-tenants found for contract', [
            'contract_id' => $contract->id,
            'co_tenants_count' => $coTenants->count(),
            'co_tenants' => $coTenants->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'status' => $tenant->status
                ];
            })->toArray()
        ]);

        // 4) Update and clear co-tenants (this will change their status to 'Đã rời đi')
        $this->updateAndClearCoTenants($contract);

        // 5) Xoá identity của chủ hợp đồng
        $this->clearUserIdentityDocument($contract->user, $contract->id, null);

        // 6) Thông báo & email & FCM cho chủ hợp đồng
        $this->info("✅ Hợp đồng #{$contract->id} đã được kết thúc");
        $this->createAutoEndContractNotification($contract);
        $this->sendAutoEndContractEmail($contract);

        // 7) ✅ Send notifications to co-tenants AFTER updating their status
        foreach ($coTenants as $tenant) {
            $this->sendMailForCoTenant($tenant);
        }

        // 8) FCM cho chủ hợp đồng
        if ($contract->user?->fcm_token) {
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
     * Xóa identity document của user
     */
    private function clearUserIdentityDocument($user, $contractId, $checkoutId = null)
    {
        if (!$user) {
            Log::warning('User not found for identity document clearing', [
                'contract_id' => $contractId,
                'checkout_id' => $checkoutId,
            ]);
            return;
        }

        try {
            // Xóa identity_document file nếu tồn tại
            if ($user->identity_document && Storage::disk('private')->exists($user->identity_document)) {
                Storage::disk('private')->delete($user->identity_document);
                Log::info('Identity document file deleted', [
                    'user_id' => $user->id,
                    'document_path' => $user->identity_document,
                    'contract_id' => $contractId,
                    'checkout_id' => $checkoutId,
                ]);
            }

            // Xóa reference trong database
            User::where('id', $user->id)->update([
                'identity_document' => null,
            ]);

            Log::info('User identity_document cleared', [
                'user_id' => $user->id,
                'contract_id' => $contractId,
                'checkout_id' => $checkoutId,
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing user identity document', [
                'user_id' => $user->id,
                'contract_id' => $contractId,
                'checkout_id' => $checkoutId,
                'error' => $e->getMessage(),
            ]);
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


    // gửi mail cho người ở cùng
    private function sendMailForCoTenant($tenant)
    {
        try {
            // ✅ Validate tenant object first
            if (!$tenant || !is_object($tenant)) {
                $this->warn("⚠️ Invalid tenant object");
                return;
            }

            if (!$tenant->email) {
                $this->warn("⚠️ Tenant #{$tenant->name} (ID: {$tenant->id}) không có email, bỏ qua gửi mail");
                return;
            }

            // ✅ Load contract relationship if not already loaded
            if (!$tenant->relationLoaded('contract')) {
                $tenant->load('contract.room.motel');
            }

            // ✅ Verify contract exists
            if (!$tenant->contract) {
                $this->warn("⚠️ Tenant #{$tenant->id} không có hợp đồng liên kết");
                return;
            }

            // ✅ Dispatch the job with proper parameters for "Đã rời đi" status
            SendContractTenantStatusNotification::dispatch(
                $tenant,
                'Đã rời đi',
                'Hợp đồng đã kết thúc tự động'
            );

            $this->info("📧 Đã dispatch job gửi email thông báo cho người ở cùng: {$tenant->email}");

            Log::info('Contract tenant notification job dispatched for auto-end', [
                'contract_id' => $tenant->contract_id,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'tenant_email' => $tenant->email,
                'new_status' => 'Đã rời đi',
                'reason' => 'Hợp đồng đã kết thúc tự động',
                'room_id' => $tenant->contract->room_id ?? null,
                'motel_id' => $tenant->contract->room->motel_id ?? null
            ]);

        } catch (\Exception $e) {
            $this->error("❌ Lỗi gửi thông báo cho người ở cùng #{$tenant->id}: " . $e->getMessage());
            Log::error("Error dispatching tenant notification job", [
                'tenant_id' => $tenant->id ?? 'unknown',
                'tenant_name' => $tenant->name ?? 'unknown',
                'contract_id' => $tenant->contract_id ?? 'unknown',
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
            if (!$user?->fcm_token) {
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
                    'end_date' => $contract->end_date ? $contract->end_date->format('Y-m-d') : '',
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
