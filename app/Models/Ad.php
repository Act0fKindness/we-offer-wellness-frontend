<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_id',
        'title',
        'description',
        'slot',
        'media_mobile',
        'media_tablet',
        'media_desktop',
        'media_large_desktop',
        'media_xl_desktop',
        'target_url',
        'start_date',
        'end_date',
        'status',
        'show_heading',
        'show_description',
        'button_text',
        'disable_overlay',
        'title_colour',
        'description_colour',
        'overlay_colour',
        'clicks',
        'impressions',
    ];

    protected $dates = [
        'start_date',
        'end_date',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function isActive()
    {
        return $this->status === 'active' &&
            $this->start_date &&
            $this->end_date &&
            now()->between($this->start_date, $this->end_date);
    }

    public function events()
    {
        return $this->hasMany(AdEvent::class);
    }

    public function getCtrAttribute(): string
    {
        $views = $this->events()->where('type', 'view')->count();
        $clicks = $this->events()->where('type', 'click')->count();

        return $views > 0 ? round(($clicks / $views) * 100, 2) . '%' : '0%';
    }

}
