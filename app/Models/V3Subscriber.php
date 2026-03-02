<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class V3Subscriber extends Model
{
    use HasFactory;

    protected $table = 'v3_subscribers';

    protected $fillable = [
        'first_name',
        'last_name',
        'business_name',
        'offers_online',
        'offers_in_person',
        'in_person_locations',
        'session_token',
        'email',
        'name',
        'status',
        'confirmation_token',
        'manage_token',
        'confirmation_sent_at',
        'confirmed_at',
        'unsubscribed_at',
        'preferences',
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

    protected $casts = [
        'offers_online' => 'boolean',
        'offers_in_person' => 'boolean',
        'session_started_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'confirmation_sent_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'geo_lat' => 'float',
        'geo_lng' => 'float',
        'geo_accuracy' => 'float',
        'session_duration_seconds' => 'integer',
        'preferences' => 'array',
    ];
}
