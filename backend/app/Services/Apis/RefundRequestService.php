<?php

namespace App\Services\Apis;

use App\Models\RefundRequest;
use Illuminate\Support\Facades\Auth;

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

    public function rejectRefundRequest($id)
    {
        $refundRequest = RefundRequest::findOrFail($id);
        $refundRequest->update(['status' => 'Huỷ bỏ']);
        return $refundRequest;
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
