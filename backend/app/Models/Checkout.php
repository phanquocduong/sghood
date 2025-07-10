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
        'status',
        'deposit_refunded',
        'has_left',
        'images',
        'note',
    ];

    protected $casts = [
        'check_out_date'     => 'date',
        'inventory_details'  => 'array',
        'images'             => 'array',
        'deduction_amount'   => 'integer',
        'deposit_refunded'   => 'boolean',
        'has_left'           => 'boolean',
    ];

    /**
     * Quan hệ 1-1 với hợp đồng
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
