<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'product_variants'; // Explicitly set table name

    protected $fillable = [
        'product_id',
        'title',
        'price',
        'price_difference',
        'options',
        'sku',
        'inventory_quantity',
        'metadata',
    ];

    protected $casts = [
        'options' => 'array', // Cast JSON to array
        'metadata' => 'array', // Cast JSON to array
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
