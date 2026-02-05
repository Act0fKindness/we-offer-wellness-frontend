<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'name', 'description', 'enabled', 'sort_order',
    ];

    public function items()
    {
        return $this->hasMany(HomepageItem::class);
    }
}

