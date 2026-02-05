<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOptionValue extends Model
{
    protected $table = 'product_option_values'; // Explicitly set table name

    protected $fillable = ['option_id', 'value', 'radius_of', 'distance_in_miles']; // Add new fields

    /**
     * Relationship with ProductOption
     */
    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'option_id');
    }

    /**
     * Scope to filter values with radius enabled
     */
    public function scopeWithRadius($query)
    {
        return $query->where('radius_of', true);
    }

    /**
     * Scope to filter values by specific distance
     */
    public function scopeWithinDistance($query, $distance)
    {
        return $query->where('distance_in_miles', '<=', $distance);
    }
}
