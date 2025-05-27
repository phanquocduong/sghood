<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Motel extends Model
{
    use SoftDeletes;

    protected $table = 'motels';

    protected $fillable = [
        'slug',
        'name',
        'address',
        'district_id',
        'map_embed_url',
        'description',
        'electricity_fee',
        'water_fee',
        'parking_fee',
        'junk_fee',
        'internet_fee',
        'service_fee',
        'status',
    ];

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function images()
    {
        return $this->hasMany(MotelImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(MotelImage::class, 'motel_id')->where('is_main', 1);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'motel_amenities', 'motel_id', 'amenity_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
