<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','email','currency','amount_total','status','stripe_session_id','stripe_payment_intent_id'
    ];

    public function items(){ return $this->hasMany(OrderItem::class); }
}

