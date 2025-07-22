<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Checkout extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'check_out_date',
        'inventory_details',
        'deduction_amount',
        'final_refunded_amount',
        'inventory_status',
        'user_confirmation_status',
        'user_rejection_reason',
        'has_left',
        'images',
        'note',
    ];

    protected $casts = [
        'inventory_details' => 'array',
        'images' => 'array',
        'check_out_date' => 'date',
        'has_left' => 'boolean',
    ];

    /**
     * Quan hệ 1-1 với hợp đồng
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function refund_request()
    {
        return $this->hasOne(RefundRequest::class);
    }
}
