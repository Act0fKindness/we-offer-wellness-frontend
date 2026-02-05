<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorLocation extends Model
{
    protected $fillable = [
        'vendor_id',
        'label',
        'place_id',
        'formatted_address',
        'country',
        'lat',
        'lng',
    ];

    public function vendor()
    {
        return $this->belongsTo(VendorDetail::class, 'vendor_id');
    }

    public function inUse(): bool
    {
        // A location is considered "in use" if any product for this vendor has an option
        // with meta_name = 'locations' and a value equal to this location's label.
        return \App\Models\ProductOption::where('meta_name', 'locations')
            ->whereHas('product', function ($q) {
                $q->where('vendor_id', $this->vendor_id);
            })
            ->whereHas('values', function ($q) {
                $q->where('value', $this->label);
            })
            ->exists();
    }
}
