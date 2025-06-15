<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = 'viewing_schedules';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'room_id',
        'scheduled_at',
        'message',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    const STATUS_PENDING = 'Chờ xác nhận';
    const STATUS_CONFIRMED = 'Đã xác nhận';
    const STATUS_COMPLETED = 'Hoàn thành';
    const STATUS_CANCELED = 'Huỷ bỏ';

    // Quan hệ với bảng users
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Quan hệ với bảng rooms
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
