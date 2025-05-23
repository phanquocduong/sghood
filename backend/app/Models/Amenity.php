<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Amenity extends Model
{
    use SoftDeletes;

    protected $table = 'amenities';

    protected $fillable = [
        'name',
        'order',
        'status',
        'type',
    ];
};
