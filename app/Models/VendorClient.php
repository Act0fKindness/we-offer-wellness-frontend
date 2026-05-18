<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'client_user_id',
        'reservation_id',
        'booking_id',
        'first_booked_at',
        'last_booked_at',
        'date_of_birth',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
    ];

    protected $casts = [
        'first_booked_at' => 'datetime',
        'last_booked_at' => 'datetime',
        'date_of_birth' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(VendorDetail::class, 'vendor_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
