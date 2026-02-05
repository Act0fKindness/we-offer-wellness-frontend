<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'homepage_slot_id',
        'title', 'subtitle', 'cta_label', 'image_url', 'url_path',
        'source_type', 'source_ref',
        'metric_sessions', 'metric_conversions', 'metric_revenue', 'score',
        'active_from', 'active_to', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'active_from' => 'datetime',
        'active_to' => 'datetime',
        'metric_conversions' => 'decimal:2',
        'metric_revenue' => 'decimal:2',
        'score' => 'decimal:4',
    ];

    public function slot()
    {
        return $this->belongsTo(HomepageSlot::class, 'homepage_slot_id');
    }
}

