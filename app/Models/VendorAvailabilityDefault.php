<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAvailabilityDefault extends Model
{
    use HasFactory;

    protected $table = 'vendor_availability_defaults';

    protected $fillable = [
        'user_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
