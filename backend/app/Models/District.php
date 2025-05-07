<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class District extends Model
{
    //
    use SoftDeletes;
    protected $table = 'districts';
    protected $fillable = [
        'name',
        'image',
    ];
    public function motels()
    {
        return $this->hasMany(Motel::class, 'district_id');
    }

}   
