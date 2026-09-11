<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    protected $fillable = [
        'platform_id',
        'slug',
        'title',
        'content',
        'effective_date',
    ];

    protected $casts = [
        'platform_id' => 'integer',
        'effective_date' => 'date',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }
}
