<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserIdentityVerification extends Model
{
    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
