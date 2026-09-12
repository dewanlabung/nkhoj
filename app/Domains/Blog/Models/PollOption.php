<?php

namespace App\Domains\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class PollOption extends Model
{
    protected $fillable = ['poll_id', 'text', 'votes_count'];

    public function poll()  { return $this->belongsTo(Poll::class); }
    public function votes() { return $this->hasMany(PollVote::class, 'poll_option_id'); }
}
