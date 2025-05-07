<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotelFee extends Model
{
    protected $table = 'motel_fees';
    protected $fillable = [
        'motel_id',
        'fee_type',
        'fee_amount',
    ];
    public function motel()
    {
        return $this->belongsTo(Motel::class, 'motel_id');
    }
}