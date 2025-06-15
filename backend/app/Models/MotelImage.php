<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotelImage extends Model
{
    protected $table = 'motel_images';

    protected $fillable = ['motel_id', 'image_url', 'is_main'];

    public function motel() {
        return $this->belongsTo(Motel::class, 'motel_id');
    }
}

