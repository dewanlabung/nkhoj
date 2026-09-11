<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReelLike extends Model
{
    public $timestamps = false;
    protected $fillable = ['reel_id', 'user_id'];
}
