<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserConnectedAccount extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'account_email',
        'verification_code',
        'is_connected',
    ];

    protected $casts = [
        'is_connected' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
