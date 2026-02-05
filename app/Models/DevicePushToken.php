<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevicePushToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'platform', 'token', 'active', 'device_model', 'app_version', 'last_seen_at'
    ];
}

