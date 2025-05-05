<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rooms extends Model
{
    use SoftDeletes;
    protected $table = 'rooms';
    protected $fillable = [
        'name',
        'price',
        'area',
        'status',
        'motel_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $casts = [
        'status' => 'string',
    ];
    public function motel()
    {
        return $this->belongsTo(Motels::class, 'motel_id');
    }
}
