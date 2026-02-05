<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/QrCode.php
class QrCode extends Model
{
    protected $fillable = ['url', 'image_path'];
}
