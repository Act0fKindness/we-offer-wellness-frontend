<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'birth_date',
        'gender',
        'skills',
        'notifications_enabled',
        'marketing_emails_enabled',
    ];

    protected $casts = [
        'skills' => 'array',
        'notifications_enabled' => 'boolean',
        'marketing_emails_enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
