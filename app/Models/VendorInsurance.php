<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorInsurance extends Model
{
    protected $table = 'vendor_insurance'; // Explicitly set the table name

    protected $fillable = [
        'vendor_id',
        'insurance_type',
        'insurance_provider',
        'coverage_amount',
        'valid_from',
        'valid_until',
        'status',
        'rejection_reason',
    ];

    public function vendor()
    {
        return $this->belongsTo(VendorDetail::class, 'vendor_id');
    }

    public function documents()
    {
        return $this->hasMany(VendorInsuranceDocument::class);
    }

}
