<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'user_id',
        'article_id',
        'type',
        'size',
        'media_url',
        'media_thumbnail_url',
        'mime_type',
        'metadata',
        'order',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
