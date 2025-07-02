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
        'note',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
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
    public function getMainImageAttribute()
    {
        // Tìm hình ảnh có is_main = 1
        $mainImage = $this->images->firstWhere('is_main', 1);

        // Nếu không tìm thấy, sử dụng hình đầu tiên (nếu có)
        if (!$mainImage && $this->images->count() > 0) {
            $mainImage = $this->images->first();
        }

        return $mainImage;
    }

    public function mainImage()
    {
        return $this->hasOne(RoomImage::class, 'room_id')->where('is_main', 1);
    }

    public function booking()
    {
        return $this->hasMany(Booking::class);
    }

    public function meterReadings()
    {
        return $this->hasMany(MeterReading::class, 'room_id');
    }

}
