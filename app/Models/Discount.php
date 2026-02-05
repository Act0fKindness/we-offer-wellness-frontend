<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'discount_code',
        'discount_percentage',
        'start_date',
        'end_date',
        'product_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
