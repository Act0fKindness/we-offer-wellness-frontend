<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class V3Subscriber extends Model
{
    use HasFactory;

    protected $table = 'v3_subscribers';

    protected $fillable = [
        'session_token',
        'email',
        'name',
        'landing_path',
        'referrer',
        'timezone',
        'locale',
        'languages',
        'platform',
        'user_agent',
        'device_memory',
        'hardware_concurrency',
        'screen_width',
        'screen_height',
        'geo_lat',
        'geo_lng',
        'geo_accuracy',
        'session_started_at',
        'last_seen_at',
        'session_duration_seconds',
    ];
}
