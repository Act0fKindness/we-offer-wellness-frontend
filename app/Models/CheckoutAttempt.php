<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckoutAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'currency',
        'amount_total',
        'items',
        'status',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'order_id',
        'meta',
    ];

    protected $casts = [
        'items' => 'array',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
