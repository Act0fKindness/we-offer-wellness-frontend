<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Review;

class VendorDetail extends Model
{
    protected $fillable = [
        'user_id',
        'vendor_name',
        'vendor_contact',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function tiers()
    {
        return $this->hasMany(VendorTier::class, 'vendor_id');
    }

    public function insurances()
    {
        return $this->hasMany(VendorInsurance::class, 'vendor_id');
    }

    public function currentInsurance()
    {
        return $this->hasOne(VendorInsurance::class, 'vendor_id')->latestOfMany('valid_until');
    }

    public function reviews()
    {
        return $this->hasMany(VendorReview::class, 'vendor_id');
    }

    public function customerReviews()
    {
        return $this->hasMany(Review::class, 'vendor_id');
    }

    public function getReviewSummaryAttribute(): array
    {
        try {
            $reviews = $this->relationLoaded('customerReviews')
                ? $this->customerReviews
                : $this->customerReviews()->whereRaw("TRIM(COALESCE(review_text, '')) <> ''")->get();

            $filtered = $reviews->filter(static function ($review): bool {
                return trim((string) ($review->review_text ?? '')) !== '';
            });
            $count = $filtered->count();
            $rating = $count > 0 ? round((float) $filtered->avg('rating'), 1) : null;

            return [
                'count' => $count,
                'rating' => $rating,
            ];
        } catch (\Throwable $e) {
            return [
                'count' => 0,
                'rating' => null,
            ];
        }
    }

    public function locations()
    {
        return $this->hasMany(VendorLocation::class, 'vendor_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    public function offerings()
    {
        return $this->hasMany(OfferingV3::class, 'vendor_id');
    }
}
