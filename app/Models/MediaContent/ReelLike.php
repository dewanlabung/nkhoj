<?php

namespace App\Models\MediaContent;

use Illuminate\Database\Eloquent\Model;

class ReelLike extends Model
{
    public $timestamps = false;
    protected $fillable = ['reel_id', 'user_id'];
}
