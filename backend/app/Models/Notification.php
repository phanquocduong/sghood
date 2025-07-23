<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'status'
    ];

    // Accessor để định dạng created_at thành ISO 8601
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->toIso8601String();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
