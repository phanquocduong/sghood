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
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    // protected $casts = [
    //     'status' => 'string',
    // ];
    public function motel()
    {
        return $this->belongsTo(Motel::class, 'motel_id');
    }
    public function images()
    {
        return $this->hasMany(RoomImage::class, 'room_id', 'id');
    }
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_amenities', 'room_id', 'amenity_id');
    }
}
