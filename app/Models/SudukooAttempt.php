<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SudukooAttempt extends Model {
    protected $fillable = ['user_id', 'sudukoo_puzzle_id', 'time_taken', 'correct'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function puzzle() {
        return $this->belongsTo(SudukooPuzzle::class);
    }
}
