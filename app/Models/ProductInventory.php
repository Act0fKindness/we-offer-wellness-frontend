<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'reserved_quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
