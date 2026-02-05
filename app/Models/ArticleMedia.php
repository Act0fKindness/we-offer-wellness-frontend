<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleMedia extends Model
{
    use HasFactory;

    protected $table = 'article_media';

    protected $fillable = [
        'article_id',
        'type',
        'size',
        'media_url',
        'media_thumbnail_url',
        'mime_type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array', // Automatically cast JSON field to array
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
