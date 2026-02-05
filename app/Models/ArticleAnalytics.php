<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'views',
        'shares',
        'comments',
        'demographics',
    ];

    protected $casts = [
        'demographics' => 'array',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
