<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomImages extends Model
{
    protected $table = 'room_images';
    protected $fillable = [
        'room_id',
        'image_url',
        'created_at',
        'updated_at',
    ];

    public function room()
    {
        return $this->belongsTo(Rooms::class, 'room_id', 'id');
    }
}
