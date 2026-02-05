<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBio extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'social_links',
    ];

    protected $casts = [
        'social_links' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
