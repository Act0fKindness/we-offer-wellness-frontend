<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    protected $table = 'product_options'; // Explicitly set table name

    protected $fillable = ['product_id', 'name', 'meta_name', 'position'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->hasMany(ProductOptionValue::class, 'option_id');
    }
}
