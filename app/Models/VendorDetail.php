<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function reviews()
    {
        return $this->hasMany(VendorReview::class, 'vendor_id');
    }

    public function locations()
    {
        return $this->hasMany(VendorLocation::class, 'vendor_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }
}
