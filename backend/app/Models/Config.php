<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'configs';

    protected $fillable = [
        'config_key',
        'config_value',
        'description',
        'config_type',
    ];
}
