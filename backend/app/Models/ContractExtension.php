<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractExtension extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'contract_id',
        'new_end_date',
        'new_rental_price',
        'content',
        'file',
        'status',
        'cancellation_reason',
    ];

    protected $casts = [
        'new_end_date' => 'date',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
