<?php

namespace App\Domains\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class PollVote extends Model
{
    protected $fillable = ['poll_id', 'poll_option_id', 'user_id', 'session_key'];
}
