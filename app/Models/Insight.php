<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insight extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_slug', // ✅ Add this line
        'title',
        'slug',
        'content',
        'vendor',
        'location',
        'price',
        'product_type',
        'image',
        'published_at',
    ];

    protected $dates = ['published_at', 'updated_at'];
}
