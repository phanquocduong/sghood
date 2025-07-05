<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Amenity extends Model
{
    use SoftDeletes;

    protected $table = 'amenities';

    protected $fillable = [
        'name',
        'order',
        'status',
        'type',
    ];

    public function motels()
    {
        return $this->belongsToMany(Motel::class, 'motel_amenities', 'amenity_id', 'motel_id');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_amenities', 'amenity_id', 'room_id');
    }
};
