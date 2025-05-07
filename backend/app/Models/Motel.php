<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Motel extends Model
{
    //
    use SoftDeletes;
    protected $table = 'motels';
    protected $fillable = [
        'address',
        'district_id',
        'map_embed_url',
        'description',
        'status',
    ];

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }
    public function images()
    {
        return $this->hasMany(MotelImage::class, 'motel_id');
    }
}
