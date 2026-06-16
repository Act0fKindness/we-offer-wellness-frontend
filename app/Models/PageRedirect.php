<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageRedirect extends Model
{
    use HasFactory;

    protected $table = 'page_redirects';

    protected $fillable = [
        'platform_id',
        'from_path',
        'to_path',
        'http_code',
        'preserve_query',
        'is_active',
        'hit_count',
        'last_hit_at',
    ];

    protected $casts = [
        'platform_id' => 'integer',
        'http_code' => 'integer',
        'preserve_query' => 'boolean',
        'is_active' => 'boolean',
        'hit_count' => 'integer',
        'last_hit_at' => 'datetime',
    ];
}
