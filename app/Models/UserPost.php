<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPost extends Model
{
    protected $fillable = [
        'user_id',
        'content',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(UserComment::class, 'post_id');
    }

    public function likes()
    {
        return $this->hasMany(UserLike::class, 'post_id');
    }
}
