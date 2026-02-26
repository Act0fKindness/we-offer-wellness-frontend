<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** @mixin \Illuminate\Database\Eloquent\Builder */

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','email','currency','amount_total','status','stripe_session_id','stripe_payment_intent_id'
    ];

    /**
     * All line items included in the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Customer profile information captured at checkout.
     */
    public function customerProfile(): HasOne
    {
        return $this->hasOne(OrderCustomer::class);
    }

    /**
     * Shipping/contact detail record for physical items.
     */
    public function shippingDetail(): HasOne
    {
        return $this->hasOne(ShippingDetail::class);
    }

    /**
     * Scope orders that belong to the authenticated customer (by id or email).
     */
    public function scopeForCustomer(Builder $query, ?User $user): Builder
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $inner) use ($user) {
            $inner->where('user_id', $user->id);
            if ($user->email) {
                $inner->orWhere('email', $user->email);
            }
        });
    }
}
