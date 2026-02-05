<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMedia extends Model
{
    use HasFactory;

    protected $table = 'product_media';

    protected $fillable = [
        'product_id',
        'type',
        'size',
        'metadata',
        'media_url',
        'media_thumbnail_url',
        'mime_type',
        'order'
    ];


    protected $casts = [
        'metadata' => 'array', // Automatically cast JSON field to array
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
