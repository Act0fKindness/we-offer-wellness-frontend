<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Navigation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'platform_id'];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function items()
    {
        return $this->hasMany(NavigationItem::class)->whereNull('parent_id')->orderBy('order');
    }
}
