<?php

namespace App\Services\Apis;

use App\Jobs\Apis\SendCheckoutNotification;
use App\Models\Checkout;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    public function requestReturn(int $id, ?array $bankInfo, string $checkOutDate): array
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

            $latestExtension = $contract->extensions()->where('status', 'Chờ duyệt')->first();
            if ($latestExtension) {
                return [
                    'error' => 'Hợp đồng đang có yêu cầu gia hạn chờ duyệt, không thể trả phòng',
                    'status' => 400,
                ];
            }

            if ($contract->deposit_amount <= 0) {
                return [
                    'error' => 'Hợp đồng không có tiền cọc để hoàn',
                    'status' => 400,
                ];
            }

            $existingCheckout = $contract->checkouts()
                ->whereNull('canceled_at')
                ->first();

            if ($existingCheckout) {
                return [
                    'error' => 'Hợp đồng đã có yêu cầu trả phòng',
                    'status' => 400,
                ];
            }

            $qrUrl = null;
            if ($bankInfo) {
                $qrUrl = sprintf(
                    'https://qr.sepay.vn/img?acc=%s&bank=%s&amount=&des=&template=qronly',
                    urlencode($bankInfo['account_number']),
                    urlencode($bankInfo['bank_name']),
                );
            }

            $checkout = Checkout::create([
                'contract_id' => $contract->id,
                'check_out_date' => $checkOutDate,
                'bank_info' => $bankInfo,
                'qr_code_path' => $qrUrl,
                'inventory_status' => 'Chờ kiểm kê',
                'user_confirmation_status' => 'Chưa xác nhận',
                'refund_status' => 'Chờ xử lý',
                'has_left' => false,
            ]);

            $method = $bankInfo ? 'chuyển khoản' : 'tiền mặt';
            SendCheckoutNotification::dispatch(
                $checkout,
                'pending',
                'Yêu cầu trả phòng #' . $checkout->id,
                "Người dùng {$contract->user->name} đã tạo yêu cầu trả phòng #{$checkout->id} cho hợp đồng #{$contract->id} vào ngày {$checkOutDate} với phương thức hoàn tiền {$method}."
            );

            return [
                'data' => [
                    'contract' => $contract->fresh(),
                    'checkout' => $checkout,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi yêu cầu trả phòng: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getCheckouts()
    {
        $query = Checkout::query()
            ->whereHas('contract', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->with(['contract.room.motel', 'contract.room.images'])
            ->orderBy('created_at', 'desc');

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
                'bank_info' => $checkout->bank_info,
                'qr_code_path' => $checkout->qr_code_path,
                'refund_status' => $checkout->refund_status,
                'room_name' => $checkout->contract->room->name,
                'motel_name' => $checkout->contract->room->motel->name,
                'room_image' => $checkout->contract->room->main_image->image_url,
                'contract' => [
                    'deposit_amount' => $checkout->contract->deposit_amount,
                ],
            ];
        });

        return $checkouts->values();
    }

    public function cancelCheckout($id)
    {
        $checkout = Checkout::findOrFail($id);

        $checkout->update([
            'canceled_at' => now(),
        ]);

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

        $checkout->update([
            'user_confirmation_status' => $status,
            'user_rejection_reason' => $userRejectionReason,
        ]);

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

        if ($checkout->user_confirmation_status !== 'Đồng ý') {
            throw new \Exception('Không thể xác nhận rời phòng khi chưa đồng ý với kết quả kiểm kê.');
        }

        $checkout->update(['has_left' => true]);

        SendCheckoutNotification::dispatch(
            $checkout,
            'left-room',
            "Người dùng đã xác nhận rời phòng #{$checkout->id}",
            "Người dùng {$checkout->contract->user->name} đã xác nhận rời phòng #{$checkout->id} cho hợp đồng #{$checkout->contract->id}."
        );

        return $checkout;
    }

    public function updateBankInfo(int $id, ?array $bankInfo): array
    {
        try {
            $checkout = Checkout::query()
                ->where('id', $id)
                ->where('refund_status', 'Chờ xử lý')
                ->whereHas('contract', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->first();

            if (!$checkout) {
                return [
                    'error' => 'Không tìm thấy yêu cầu trả phòng hoặc bạn không có quyền chỉnh sửa.',
                    'status' => 404,
                ];
            }

            $qrUrl = null;
            if ($bankInfo) {
                $qrUrl = sprintf(
                    'https://qr.sepay.vn/img?acc=%s&bank=%s&amount=&des=&template=qronly',
                    urlencode($bankInfo['account_number']),
                    urlencode($bankInfo['bank_name'])
                );
            }

            $checkout->update([
                'bank_info' => $bankInfo,
                'qr_code_path' => $qrUrl,
            ]);

            $method = $bankInfo ? 'chuyển khoản' : 'tiền mặt';
            SendCheckoutNotification::dispatch(
                $checkout,
                'update-bank',
                "Thông tin hoàn tiền yêu cầu trả phòng #{$checkout->id} đã được cập nhật",
                "Người dùng {$checkout->contract->user->name} đã cập nhật thông tin hoàn tiền cho yêu cầu trả phòng #{$checkout->id} thành {$method}."
            );

            return [
                'data' => [
                    'checkout' => $checkout,
                ],
                'message' => 'Cập nhật thông tin hoàn tiền thành công',
                'status' => 200,
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi chỉnh sửa thông tin hoàn tiền', [
                'checkout_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
