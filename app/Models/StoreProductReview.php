<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreProductReview extends Model
{
    protected $fillable = ['store_product_id', 'user_id', 'rating', 'title', 'body', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
