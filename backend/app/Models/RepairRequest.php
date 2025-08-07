<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairRequest extends Model
{
    protected $table = 'repair_requests';

    protected $fillable = [
        'contract_id',
        'title',
        'description',
        'images',
        'status',
        'note',
        'repaired_at',
    ];

    protected $casts = [
        'repaired_at' => 'datetime',
    ];

    // Quan hệ
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
    
}
