<?php

// app/Models/Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'user_id',
        'order_id',
        'offering_id',
        'price_option_id',
        'price_amount',
        'audience_type',
        'pricing_type',
        'channel',
        'date',
        'start_time',
        'end_time',
        'title',
        'client_name',
        'client_email',
        'session_format',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
