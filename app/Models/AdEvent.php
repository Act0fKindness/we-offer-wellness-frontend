<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_id',
        'type',
        'ip_address',
        'ip',
        'user_agent',
        'device_type',
        'referrer',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}
