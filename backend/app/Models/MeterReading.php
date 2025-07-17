<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeterReading extends Model
{
    protected $table = 'meter_readings';

    protected $fillable = [
        'room_id',
        'month',
        'year',
        'electricity_kwh',
        'water_m3',
    ];

    protected $casts = [
        'electricity_kwh' => 'integer',
        'water_m3' => 'integer',
    ];

    // Relationships
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
