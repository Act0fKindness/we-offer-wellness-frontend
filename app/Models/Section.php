<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'caption',
        'description',
        'selection_method',
        'product_limit',
        'url_pattern',
        'slot',
        'sort_order',
    ];

    /**
     * Products selected manually for this section.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'section_product')
                    ->withPivot('sort_order')
                    ->orderBy('pivot_sort_order');
    }
}
