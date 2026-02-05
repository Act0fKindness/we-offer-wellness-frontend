<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SudukooPuzzle extends Model {
    protected $fillable = ['date', 'level', 'puzzle', 'solution'];
    protected $casts = [
        'puzzle' => 'array',
        'solution' => 'array',
    ];

    public function attempts() {
        return $this->hasMany(SudukooAttempt::class);
    }
}
