<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmarks extends Model
{
    protected $fillable = ['user_id', 'motel_id'];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function motel()
    {
        return $this->belongsTo(Motels::class);
    }
}

