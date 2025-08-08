<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    protected $table = 'rooms';

    protected $fillable = [
        'name',
        'price',
        'area',
        'status',
        'motel_id',
        'description',
        'note'
    ];

    public function motel()
    {
        return $this->belongsTo(Motel::class, 'motel_id');
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class, 'room_id', 'id');
    }

    public function getMainImageAttribute()
    {
        $mainImage = $this->images->firstWhere('is_main', 1);
        if (!$mainImage && $this->images->count() > 0) {
            $mainImage = $this->images->first();
        }
        return $mainImage;
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_amenities', 'room_id', 'amenity_id');
    }

    public function booking()
    {
        return $this->hasMany(Booking::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class)->latest('id');
    }

    public function meterReadings()
    {
        return $this->hasMany(MeterReading::class, 'room_id');
    }

}
