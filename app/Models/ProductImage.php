<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $table = 'product_images'; // Explicitly set table name

    protected $fillable = [
        'product_id',
        'image_path',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute()
    {
        $defaultImage = 'https://www.survivorsuk.org/wp-content/uploads/2017/01/no-image.jpg';

        // If the image_path is a valid URL, return it
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        // Check if the image path is valid in the storage directory
        $storagePath = 'storage/' . $this->image_path;
        if (file_exists(public_path($storagePath))) {
            return asset($storagePath);
        }

        // Check if the image exists directly in the product_images directory
        $productImagePath = 'product_images/' . $this->image_path;
        if (file_exists(public_path($productImagePath))) {
            return asset($productImagePath);
        }

        // Return the default image if nothing is found
        return $defaultImage;
    }

}
