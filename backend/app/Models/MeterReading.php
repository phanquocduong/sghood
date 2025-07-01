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

    // Relationships
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
