<?php

namespace App\Services\Apis;

use App\Models\RefundRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RefundRequestService
{
    public function getRefundRequests(array $filters)
    {
        $query = RefundRequest::query()
            ->whereHas('checkout.contract', function ($query) {
                $query->where('user_id', Auth::id());
            });

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

        $refundRequests = $query->get()->map(function ($refundRequest) {
            return [
                'id' => $refundRequest->id,
                'checkout_id' => $refundRequest->checkout_id,
                'contract_id' => $refundRequest->checkout->contract_id,
                'deposit_amount' => $refundRequest->deposit_amount,
                'deduction_amount' => $refundRequest->deduction_amount,
                'final_amount' => $refundRequest->final_amount,
                'bank_info' => $refundRequest->bank_info,
                'qr_code_path' => $refundRequest->qr_code_path,
                'status' => $refundRequest->status,
                'rejection_reason' => $refundRequest->rejection_reason,
            ];
        });

        return $refundRequests->values();
    }

    protected function getSortOrder($sort)
    {
        return match ($sort) {
            'oldest' => 'asc',
            'latest', 'default' => 'desc',
            default => 'desc',
        };
    }

    public function updateBankInfo(int $id, array $bankInfo): array
    {
        try {
            $refundRequest = RefundRequest::query()
                ->where('id', $id)
                ->where('status', 'Chờ xử lý')
                ->whereHas('checkout.contract', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->first();

            if (!$refundRequest) {
                return [
                    'error' => 'Không tìm thấy yêu cầu hoàn tiền hoặc bạn không có quyền chỉnh sửa yêu cầu hoàn tiền',
                    'status' => 404,
                ];
            }

            // Tạo URL mã QR theo định dạng Sepay
            $qrUrl = sprintf(
                'https://qr.sepay.vn/img?acc=%s&bank=%s&amount=&des=&template=qronly',
                urlencode($bankInfo['account_number']),
                urlencode($bankInfo['bank_name'])
            );

            // Cập nhật thông tin yêu cầu hoàn tiền
            $refundRequest->update([
                'bank_info' => $bankInfo,
                'qr_code_path' => $qrUrl,
            ]);

            return [
                'data' => [
                    'refund_request' => $refundRequest,
                ],
                'message' => 'Cập nhật thông tin chuyển khoản thành công',
                'status' => 200,
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi chỉnh sửa thông tin chuyển khoản', [
                'contract_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return [
                'error' => 'Lỗi khi cập nhật thông tin chuyển khoản: ' . $e->getMessage(),
                'status' => 500,
            ];
        }
    }
}
