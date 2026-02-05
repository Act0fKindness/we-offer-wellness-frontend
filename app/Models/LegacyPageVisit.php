<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegacyPageVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'slug',
        'full_url',
        'referer',
        'user_agent',
        'ip_address',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
