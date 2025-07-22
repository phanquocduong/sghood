<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'code',
        'contract_id',
        'meter_reading_id',
        'type',
        'month',
        'year',
        'electricity_fee',
        'water_fee',
        'parking_fee',
        'junk_fee',
        'internet_fee',
        'service_fee',
        'refunded_at',
        'total_amount',
        'status'
    ];

    // Relationships
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function meterReading()
    {
        return $this->belongsTo(MeterReading::class);
    }
}
