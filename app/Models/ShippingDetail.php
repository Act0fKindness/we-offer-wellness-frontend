<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingDetail extends Model
{
    protected $fillable = [
        'order_id',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'shipping_status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
