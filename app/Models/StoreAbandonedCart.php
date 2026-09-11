<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreAbandonedCart extends Model
{
    protected $fillable = [
        'visitor_key', 'user_id', 'email', 'customer_name', 'items', 'cart_total',
        'first_added_at', 'last_activity_at', 'purchased_at', 'stage_one_sent_at',
        'stage_two_sent_at', 'stage_three_sent_at',
    ];

    protected $casts = [
        'items' => 'array', 'cart_total' => 'decimal:2', 'first_added_at' => 'datetime',
        'last_activity_at' => 'datetime', 'purchased_at' => 'datetime',
        'stage_one_sent_at' => 'datetime', 'stage_two_sent_at' => 'datetime', 'stage_three_sent_at' => 'datetime',
    ];
}
