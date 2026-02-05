<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body',
        'target_type', 'target_role',
        'send_email', 'send_notification',
        'scheduled_at', 'sent_at', 'status',
        'created_by', 'count_sent',
    ];

    protected $casts = [
        'send_email' => 'boolean',
        'send_notification' => 'boolean',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}

