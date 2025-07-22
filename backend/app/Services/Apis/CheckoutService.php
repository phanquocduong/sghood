<?php

namespace App\Services\Apis;

use App\Jobs\Apis\SendCheckoutNotification;
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
                ->where('inventory_status', '!=', 'Huỷ bỏ')
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

            // Gửi thông báo qua job queue
            SendCheckoutNotification::dispatch(
                $checkout,
                'pending',
                'Yêu cầu trả phòng #' . $checkout->id,
                "Người dùng {$contract->user->name} đã tạo yêu cầu trả phòng #{$checkout->id} cho hợp đồng #{$contract->id} vào ngày {$checkOutDate}."
            );

            return [
                'data' => [
                    'contract' => $contract->fresh(),
                    'checkout' => $checkout,
                    'refund_request' => $refundRequest,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi yêu cầu trả phòng:' . $e->getMessage());
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

    public function cancelCheckout($id)
    {
        $checkout = Checkout::findOrFail($id);

        // Cập nhật trạng thái của Checkout thành 'Huỷ bỏ'
        $checkout->update(['inventory_status' => 'Huỷ bỏ']);

        // Cập nhật trạng thái của RefundRequest liên kết (nếu có) thành 'Huỷ bỏ'
        if ($checkout->refund_request) {
            $checkout->refund_request->update(['inventory_status' => 'Huỷ bỏ']);
        }

        // Gửi thông báo qua job queue
        SendCheckoutNotification::dispatch(
            $checkout,
            'canceled',
            'Yêu cầu trả phòng #' . $checkout->id . ' đã bị hủy',
            "Người dùng {$checkout->contract->user->name} đã hủy yêu cầu trả phòng #{$checkout->id} cho hợp đồng #{$checkout->contract->id}."
        );

        return $checkout;
    }

    public function confirmCheckout($id, string $status, ?string $userRejectionReason = null)
    {
        $checkout = Checkout::findOrFail($id);

        // Cập nhật trạng thái xác nhận của người dùng
        $checkout->update([
            'user_confirmation_status' => $status,
            'user_rejection_reason' => $userRejectionReason,
        ]);

        // Gửi thông báo qua job queue
        $action = $status === 'Đồng ý' ? 'confirm' : 'reject';
        $title = $action === 'confirm'
            ? "Kết quả kiểm kê trả phòng #{$checkout->id} đã được người dùng đồng ý"
            : "Kết quả kiểm kê #{$checkout->id} bị người dùng từ chối";
        $body = $action === 'confirm'
            ? "Kết quả kiểm kê trả phòng #{$checkout->id} (Hợp đồng: #{$checkout->contract->id}) đã được người dùng {$checkout->contract->user->name} xác nhận đồng ý."
            : "Kết quả kiểm kê trả phòng #{$checkout->id} (Hợp đồng: #{$checkout->contract_id}) bị người dùng {$checkout->contract->user->name} từ chối. Lý do: " . ($userRejectionReason ?? 'Không cung cấp') . ".";

        SendCheckoutNotification::dispatch($checkout, $action, $title, $body);

        return $checkout;
    }

    public function confirmLeftRoom($id)
    {
        $checkout = Checkout::findOrFail($id);

        // Kiểm tra xem người dùng đã đồng ý với kiểm kê chưa
        if ($checkout->user_confirmation_status !== 'Đồng ý') {
            throw new \Exception('Không thể xác nhận rời phòng khi chưa đồng ý với kết quả kiểm kê.');
        }

        // Cập nhật trạng thái rời phòng
        $checkout->update(['has_left' => true]);

        // Gửi thông báo qua job queue
        SendCheckoutNotification::dispatch(
            $checkout,
            'left-room',
            "Người dùng đã xác nhận rời phòng #{$checkout->id}",
            "Người dùng {$checkout->contract->user->name} đã xác nhận rời phòng #{$checkout->id} cho hợp đồng #{$checkout->contract->id}."
        );

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
?>
