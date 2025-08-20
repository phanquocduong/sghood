<?php

namespace App\Services\Apis;

use App\Jobs\Apis\SendContractNotification;
use App\Models\Contract;
use App\Services\Apis\ContractPdfService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ContractService
{
    public function __construct(
        private readonly ContractPdfService $contractPdfService
    ) {}

    public function getUserContracts(): array
    {
        try {
            return Contract::query()
                ->where('user_id', Auth::id())
                ->select('id', 'room_id', 'start_date', 'end_date', 'status', 'deposit_amount', 'rental_price', 'signed_at', 'early_terminated_at', 'created_at')
                ->with([
                    'room' => fn($query) => $query->select('id', 'name', 'motel_id', 'price')
                        ->with(['motel' => fn($query) => $query->select('id', 'name', 'slug')]),
                    'invoices' => fn($query) => $query->select('id', 'contract_id')
                        ->where('type', 'Đặt cọc'),
                    'extensions' => fn($query) => $query->select('id', 'contract_id', 'status')
                        ->latest(),
                    'checkouts' => fn($query) => $query->select(
                        'id',
                        'contract_id',
                        'check_out_date',
                        'canceled_at',
                        'has_left'
                    )->latest(),
                ])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn (Contract $contract) => [
                    'id' => $contract->id,
                    'room_name' => $contract->room->name,
                    'room_price' => $contract->room->price,
                    'motel_name' => $contract->room->motel->name,
                    'motel_slug' => $contract->room->motel->slug,
                    'room_image' => $contract->room->main_image->image_url,
                    'start_date' => $contract->start_date->toDateString(),
                    'end_date' => $contract->end_date->toDateString(),
                    'status' => $contract->status,
                    'deposit_amount' => $contract->deposit_amount,
                    'rental_price' => $contract->rental_price,
                    'signed_at' => $contract->signed_at?->toDateTimeString(),
                    'early_terminated_at' => $contract->early_terminated_at?->toDateTimeString(),
                    'invoice_id' => $contract->invoices->first()?->id,
                    'latest_extension_status' => $contract->extensions->first()?->status,
                    'has_checkout' => $contract->checkouts->first()?->id,
                    'latest_checkout_status' => $contract->checkouts->first()?->canceled_at,
                ])
                ->toArray();
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy danh sách hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    public function getContractDetail(int $id): array
    {
        try {
            $contract = Contract::query()
                ->with(['user', 'extensions'])
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền truy cập',
                    'status' => 404,
                ];
            }

            return [
                'id' => $contract->id,
                'room_id' => $contract->room_id,
                'user_id' => $contract->user_id,
                'booking_id' => $contract->booking_id,
                'start_date' => $contract->start_date->toIso8601String(),
                'end_date' => $contract->end_date->toIso8601String(),
                'rental_price' => $contract->rental_price,
                'deposit_amount' => $contract->deposit_amount,
                'content' => $contract->content,
                'signature' => $contract->signature,
                'status' => $contract->status,
                'file' => $contract->file ? url($contract->file) : null,
                'signed_at' => $contract->signed_at?->toDateTimeString(),
                'user_phone' => $contract->user->phone,
                'active_extensions' => $contract->extensions
                    ->filter(fn($ext) => $ext->status === 'Hoạt động')
                    ->map(fn($ext) => [
                        'id' => $ext->id,
                        'new_end_date' => $ext->new_end_date->toIso8601String(),
                        'new_rental_price' => $ext->new_rental_price,
                        'content' => $ext->content,
                        'file' => $ext->file ? url($ext->file) : null,
                        'status' => $ext->status,
                    ])->values()->toArray(),
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy chi tiết hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    public function cancelContract(int $id): array
    {
        try {
            $contract = Contract::query()
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền hủy',
                    'status' => 404,
                ];
            }

            if ($contract->status !== 'Chờ xác nhận') {
                return [
                    'error' => 'Hợp đồng không ở trạng thái có thể hủy',
                    'status' => 400,
                ];
            }

            $contract->update(['status' => 'Huỷ bỏ']);

            SendContractNotification::dispatch(
                $contract,
                'canceled',
                "Hợp đồng #{$contract->id} đã bị hủy",
                "Người dùng {$contract->user->name} đã hủy hợp đồng #{$contract->id}."
            );

            return ['data' => $contract->fresh()];
        } catch (\Throwable $e) {
            Log::error('Lỗi hủy hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    public function saveContract(string $content, int $id, bool $bypassExtract = false): Contract
    {
        try {
            $contract = Contract::query()
                ->where('user_id', Auth::id())
                ->where('id', $id)
                ->firstOrFail();

            $oldStatus = $contract->status;
            $newStatus = $bypassExtract ? 'Chờ duyệt thủ công' : 'Chờ duyệt';

            $contract->update([
                'content' => $content,
                'status' => $newStatus
            ]);

            $type = $bypassExtract ? 'bypass_pending' : 'pending';
            $title = $bypassExtract
                ? "Hợp đồng #{$contract->id} đang chờ duyệt thủ công"
                : "Hợp đồng #{$contract->id} đang chờ duyệt";
            $body = $bypassExtract
                ? "Người dùng {$contract->user->name} đã nhập thông tin CCCD trực tiếp và gửi hợp đồng #{$contract->id} để duyệt thủ công."
                : "Người dùng {$contract->user->name} đã hoàn thiện thông tin cá nhân vào hợp đồng #{$contract->id} và đang chờ duyệt.";

            SendContractNotification::dispatch($contract, $type, $title, $body);

            return $contract->fresh();
        } catch (\Throwable $e) {
            Log::error('Lỗi cập nhật hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    public function signContract(int $contractId, string $signature, string $content): Contract
    {
        try {
            $contract = Contract::query()
                ->where('user_id', Auth::id())
                ->where('id', $contractId)
                ->where('status', 'Chờ ký')
                ->firstOrFail();

            $signaturePath = $this->saveSignature($signature, $contractId);

            $contract->update([
                'signature' => $signaturePath,
                'content' => $content,
                'status' => 'Chờ thanh toán tiền cọc',
                'signed_at' => now(),
            ]);

            SendContractNotification::dispatch(
                $contract,
                'signed',
                "Hợp đồng #{$contract->id} đã được ký",
                "Hợp đồng #{$contract->id} từ người dùng {$contract->user->name} đã được ký và đang chờ thanh toán tiền cọc."
            );

            return $contract->fresh();
        } catch (\Throwable $e) {
            Log::error('Lỗi ký hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    private function saveSignature(string $signature, int $contractId): string
    {
        $signature = preg_replace('#^data:image/\w+;base64,#i', '', $signature);
        $signatureData = base64_decode($signature);

        $path = "images/signatures/contract-{$contractId}-" . time() . '.png';
        Storage::disk('private')->put($path, $signatureData);

        return $path;
    }

    public function earlyTermination(int $id): array
    {
        try {
            $contract = Contract::query()
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'Hoạt động')
                ->with(['invoices'])
                ->first();

            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền kết thúc sớm',
                    'status' => 404,
                ];
            }

            if ($contract->end_date <= now()) {
                return [
                    'error' => 'Hợp đồng đã hết hạn, không thể kết thúc sớm',
                    'status' => 400,
                ];
            }

            $latestExtension = $contract->extensions()->where('status', 'Chờ duyệt')->first();
            if ($latestExtension) {
                return [
                    'error' => 'Hợp đồng đang có yêu cầu gia hạn chờ duyệt, không thể kết thúc sớm',
                    'status' => 400,
                ];
            }

            $existingCheckout = $contract->checkouts()
                ->whereNull('canceled_at')
                ->first();

            if ($existingCheckout) {
                return [
                    'error' => 'Hợp đồng đã có yêu cầu trả phòng, không thể kết thúc sớm',
                    'status' => 400,
                ];
            }

            // Kiểm tra xem tất cả hóa đơn đã được thanh toán hay chưa
            $unpaidInvoices = $contract->invoices()->where('status', '!=', 'Đã trả')->exists();
            if ($unpaidInvoices) {
                return [
                    'error' => 'Hợp đồng có hóa đơn chưa thanh toán. Vui lòng thanh toán tất cả hóa đơn trước khi kết thúc sớm.',
                    'status' => 400,
                ];
            }

            // Kiểm tra hóa đơn cho tháng hiện tại
            $currentDate = Carbon::now();
            $currentMonth = $currentDate->month;
            $currentYear = $currentDate->year;

            if ($currentDate->day > 5) {
                // Nếu ngày hiện tại > 5, kiểm tra hóa đơn tháng hiện tại
                $hasCurrentMonthInvoice = $contract->invoices()
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->exists();
                if (!$hasCurrentMonthInvoice) {
                    return [
                        'error' => 'Hóa đơn cho tháng hiện tại chưa được tạo. Vui lòng chờ đến khi hóa đơn được tạo và thanh toán thành công.',
                        'status' => 400,
                    ];
                }
            } else {
                // Nếu ngày hiện tại từ 1-5, kiểm tra hóa đơn tháng trước
                $previousMonth = $currentDate->copy()->subMonth();
                $hasPreviousMonthInvoice = $contract->invoices()
                    ->where('month', $previousMonth->month)
                    ->where('year', $previousMonth->year)
                    ->exists();
                if (!$hasPreviousMonthInvoice) {
                    return [
                        'error' => 'Hóa đơn cho tháng trước chưa được tạo. Vui lòng chờ đến khi hóa đơn được tạo và thanh toán thành công.',
                        'status' => 400,
                    ];
                }
            }

            $contract->update([
                'status' => 'Kết thúc sớm',
                'early_terminated_at' => now(),
            ]);

            SendContractNotification::dispatch(
                $contract,
                'early_terminated',
                "Hợp đồng #{$contract->id} đã được kết thúc sớm",
                "Người dùng {$contract->user->name} đã kết thúc sớm hợp đồng #{$contract->id}."
            );

            return ['data' => $contract->fresh()];
        } catch (\Throwable $e) {
            Log::error('Lỗi kết thúc hợp đồng sớm:' . $e->getMessage());
            throw $e;
        }
    }

    public function generateAndSaveContractPdf(int $contractId): array
    {
        return $this->contractPdfService->generateAndSaveContractPdf($contractId);
    }
}
