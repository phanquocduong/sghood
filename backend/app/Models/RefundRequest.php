<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RefundRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'checkout_id',
        'deposit_amount',
        'deduction_amount',
        'final_amount',
        'bank_info',
        'status',
        'rejection_reason',
        'transaction_id',
    ];

    protected $casts = [
        'bank_info'        => 'array',
        'deposit_amount'   => 'integer',
        'deduction_amount' => 'integer',
        'final_amount'     => 'integer',
    ];


    /**
     * Quan hệ với phiếu trả phòng (checkout)
     */
    public function checkout()
    {
        return $this->belongsTo(Checkout::class);
    }
}
