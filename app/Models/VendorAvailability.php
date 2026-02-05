<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAvailability extends Model
{
    use HasFactory;

    protected $table = 'vendor_availability'; // Explicitly set table name

    protected $fillable = ['user_id', 'date', 'start_time', 'end_time', 'is_available'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
