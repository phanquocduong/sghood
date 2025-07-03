<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $table = 'contracts';

    protected $fillable = [
        'room_id',
        'user_id',
        'booking_id',
        'start_date',
        'end_date',
        'rental_price',
        'deposit_amount',
        'content',
        'signature',
        'status',
        'file',
        'signed_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'rental_price' => 'integer',
        'deposit_amount' => 'integer',
        'signed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'contract_id');
    }
}
