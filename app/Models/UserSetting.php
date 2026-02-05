<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'settings_data',
    ];

    protected $casts = [
        'settings_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
