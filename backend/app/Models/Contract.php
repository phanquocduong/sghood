<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'room_id', 'user_id', 'booking_id', 'start_date', 'end_date',
        'rental_price', 'deposit_amount', 'content', 'status', 'file',
        'otp_code', 'otp_expires_at', 'signed_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'otp_expires_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

}
