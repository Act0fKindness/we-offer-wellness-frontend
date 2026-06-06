<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferingDetail extends Model
{
    use HasFactory;

    protected $table = 'offering_details';

    protected $fillable = [
        'offering_id',
        'description',
        'what_to_expect',
        'whats_included',
        'video_url',
    ];
}
