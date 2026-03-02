<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'vendor_name',
        'provider_identifier',
        'source',
        'external_id',
        'source_url',
        'rating',
        'review_text',
        'reviewer_name',
        'reviewed_at',
        'confidence',
        'match_reason',
        'query_used',
        'product_titles',
        'place_id',
        'place_payload',
        'raw_payload',
        'review_id',
    ];

    protected $casts = [
        'product_titles' => 'array',
        'place_payload' => 'array',
        'raw_payload' => 'array',
        'reviewed_at' => 'datetime',
        'rating' => 'float',
        'confidence' => 'float',
    ];

    public function vendor()
    {
        return $this->belongsTo(VendorDetail::class, 'vendor_id');
    }

    public function publishedReview()
    {
        return $this->belongsTo(Review::class, 'review_id');
    }
}
