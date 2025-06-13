<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = [
        'user_id',
        'room_id',
        'start_date',
        'end_date',
        'note',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    const STATUS_PENDING = 'Chờ xác nhận';
    const STATUS_ACCEPTED = 'Chấp nhận';
    const STATUS_REFUSED = 'Từ chối';
    const STATUS_CANCELED = 'Huỷ bỏ';

    /**
     * Mối quan hệ với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class, 'booking_id');
    }
}
