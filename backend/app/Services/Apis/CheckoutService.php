<?php

namespace App\Services\Apis;

use App\Models\Checkout;
use Illuminate\Support\Facades\Auth;

class CheckoutService
{
    public function getCheckouts(array $filters)
    {
        $query = Checkout::query()
            ->whereHas('contract', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->with(['contract.room.motel', 'contract.room.images']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

        $checkouts = $query->get()->map(function ($checkout) {
            return [
                'id' => $checkout->id,
                'contract_id' => $checkout->contract_id,
                'check_out_date' => $checkout->check_out_date,
                'inventory_details' => $checkout->inventory_details,
                'deduction_amount' => $checkout->deduction_amount,
                'status' => $checkout->status,
                'deposit_refunded' => $checkout->deposit_refunded,
                'has_left' => $checkout->has_left,
                'note' => $checkout->note,
                'images' => $checkout->images,
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
        $checkout->update(['status' => 'Huỷ bỏ']);

        // Cập nhật trạng thái của RefundRequest liên kết (nếu có) thành 'Huỷ bỏ'
        if ($checkout->refund_request) {
            $checkout->refund_request->update(['status' => 'Huỷ bỏ']);
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
