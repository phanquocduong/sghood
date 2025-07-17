<?php

namespace App\Services\Apis;

use App\Models\Checkout;
use App\Models\Contract;
use App\Models\RefundRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function requestReturn(int $id, array $bankInfo, string $checkOutDate): array
    {
        try {
            $contract = Contract::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'Hoạt động')
                ->first();

            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền trả phòng',
                    'status' => 404,
                ];
            }

            // Kiểm tra yêu cầu gia hạn
            $latestExtension = $contract->extensions()->where('status', 'Chờ duyệt')->first();
            if ($latestExtension) {
                return [
                    'error' => 'Hợp đồng đang có yêu cầu gia hạn chờ duyệt, không thể trả phòng',
                    'status' => 400,
                ];
            }

            // Kiểm tra tiền cọc
            if ($contract->deposit_amount <= 0) {
                return [
                    'error' => 'Hợp đồng không có tiền cọc để hoàn',
                    'status' => 400,
                ];
            }

            $existingCheckout = $contract->checkouts()
                ->where('status', '!=', 'Huỷ bỏ')
                ->first();

            if ($existingCheckout) {
                return [
                    'error' => 'Hợp đồng đã có yêu cầu trả phòng',
                    'status' => 400,
                ];
            }

            // Tạo bản ghi kiểm kê
            $checkout = Checkout::create([
                'contract_id' => $contract->id,
                'check_out_date' => $checkOutDate,
                'deposit_refunded' => false,
                'has_left' => false,
            ]);

             // Tạo URL mã QR theo định dạng Sepay
            $qrUrl = sprintf(
                'https://qr.sepay.vn/img?acc=%s&bank=%s&amount=&des=&template=qronly',
                urlencode($bankInfo['account_number']),
                urlencode($bankInfo['bank_name']),
            );

            // Tạo yêu cầu hoàn tiền
            $refundRequest = RefundRequest::create([
                'checkout_id' => $checkout->id,
                'deposit_amount' => $contract->deposit_amount,
                'status' => 'Chờ xử lý',
                'bank_info' => $bankInfo,
                'qr_code_path' => $qrUrl,
            ]);

            // Gửi thông báo cho admin
            $this->notificationService->notifyContractForAdmins($contract, 'Trả phòng');

            return [
                'data' => [
                    'contract' => $contract->fresh(),
                    'checkout' => $checkout,
                    'refund_request' => $refundRequest,
                ],
                'status' => 200,
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi yêu cầu trả phòng', [
                'contract_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getCheckouts(array $filters)
    {
        $query = Checkout::query()
            ->whereHas('contract', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->with(['contract.room.motel', 'contract.room.images']);

        if (!empty($filters['inventory_status'])) {
            $query->where('inventory_status', $filters['inventory_status']);
        }

        $query->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

        $checkouts = $query->get()->map(function ($checkout) {
            return [
                'id' => $checkout->id,
                'contract_id' => $checkout->contract_id,
                'check_out_date' => $checkout->check_out_date,
                'inventory_details' => $checkout->inventory_details,
                'deduction_amount' => $checkout->deduction_amount,
                'final_refunded_amount' => $checkout->final_refunded_amount,
                'inventory_status' => $checkout->inventory_status,
                'user_confirmation_status' => $checkout->user_confirmation_status,
                'user_rejection_reason' => $checkout->user_rejection_reason,
                'has_left' => $checkout->has_left,
                'images' => $checkout->images,
                'note' => $checkout->note,
                'room_name' => $checkout->contract->room->name,
                'motel_name' => $checkout->contract->room->motel->name,
                'room_image' => $checkout->contract->room->main_image->image_url,
            ];
        });

        return $checkouts->values();
    }

    public function rejectCheckout($id)
    {
        $checkout = Checkout::findOrFail($id);

        // Cập nhật trạng thái của Checkout thành 'Huỷ bỏ'
        $checkout->update(['inventory_status' => 'Huỷ bỏ']);

        // Cập nhật trạng thái của RefundRequest liên kết (nếu có) thành 'Huỷ bỏ'
        if ($checkout->refund_request) {
            $checkout->refund_request->update(['inventory_status' => 'Huỷ bỏ']);
        }

        return $checkout;
    }

    protected function getSortOrder($sort)
    {
        return match ($sort) {
            'oldest' => 'asc',
            'latest', 'default' => 'desc',
            default => 'desc',
        };
    }
}
