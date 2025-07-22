<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_id',
        'transaction_date',
        'content',
        'transfer_type',
        'transfer_amount',
        'reference_code',
        'refund_request_id',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function refundRequest()
    {
        return $this->belongsTo(RefundRequest::class);
    }
}
