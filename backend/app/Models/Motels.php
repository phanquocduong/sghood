<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Motels extends Model
{
    use SoftDeletes;
    protected $table = 'motels';
    protected $fillable = [
        'address',
        'price',
        'map_embed_url',
        'status',
        'description',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
