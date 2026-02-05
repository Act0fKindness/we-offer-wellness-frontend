<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorTier extends Model
{
    protected $fillable = [
        'vendor_id',
        'tier',
    ];

    public function vendor()
    {
        return $this->belongsTo(VendorDetail::class, 'vendor_id');
    }
}
