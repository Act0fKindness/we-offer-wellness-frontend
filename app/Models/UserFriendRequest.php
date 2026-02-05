<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFriendRequest extends Model
{
    protected $table = 'user_friend_requests';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'status', // pending, accepted, declined
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
