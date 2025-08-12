<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommentBlog extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_id',
        'user_id',
        'content',
        'parent_id'
    ];

    // Quan hệ đến bài viết
    public function blog()
    {
        return $this->belongsTo(Blog::class, 'blog_id');
    }

    // Quan hệ đến user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Quan hệ cha
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    // Quan hệ con
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

}
