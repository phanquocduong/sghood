<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Config extends Model
{
    use SoftDeletes;
    protected $table = 'configs';

    protected $fillable = [
        'config_key',
        'config_value',
        'description',
        'config_type',
    ];

    protected $dates = ['deleted_at'];
}