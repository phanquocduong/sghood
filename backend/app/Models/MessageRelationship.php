<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageRelationship extends Model
{
    protected $fillable = ['user_id', 'admin_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function admin() {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
