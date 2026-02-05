<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SudukooMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'sudukoo_puzzle_id',
        'inviter_user_id',
        'invitee_user_id',
        'invitee_email',
        'token',
        'status',
    ];

    public function puzzle()
    {
        return $this->belongsTo(SudukooPuzzle::class, 'sudukoo_puzzle_id');
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_user_id');
    }

    public function invitee()
    {
        return $this->belongsTo(User::class, 'invitee_user_id');
    }

    public function attempts()
    {
        return $this->hasMany(SudukooAttempt::class, 'sudukoo_match_id');
    }
}

