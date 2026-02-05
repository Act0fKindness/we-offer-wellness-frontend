<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorInsuranceDocument extends Model
{
    protected $fillable = ['vendor_insurance_id', 'file_path'];

    public function vendorInsurance()
    {
        return $this->belongsTo(VendorInsurance::class);
    }
}
