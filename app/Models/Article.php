<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'articles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'title', 'content', 'status', 'views', 'category_id'];


    /**
     * The default attributes for the model.
     *
     * @var array
     */
    protected $attributes = [
        'views' => 0,
        'status' => 'draft',
    ];

    /**
     * Define the relationship between Article and User (Journalist).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function journalist()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function media()
    {
        return $this->hasMany(UserMedia::class);
    }

    public function featuredMedia()
    {
        return $this->hasOne(UserMedia::class, 'article_id', 'id')
            ->where(function($q){
                $q->where('type', 'image')
                  ->orWhere('type', 'like', 'image%')
                  ->orWhere('mime_type', 'like', 'image%');
            })
            ->latest('id');
    }

    // Define the relationship
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function additionalCategories()
    {
        return $this->belongsToMany(\App\Models\Category::class, 'article_category');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

}
