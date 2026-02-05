<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'device',
        'location',
        'last_active_at',
    ];

    protected $dates = [
        'last_active_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
