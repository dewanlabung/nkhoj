<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $fillable = ['post_id', 'author_id', 'question', 'allow_multiple', 'expires_at'];
    protected $casts = ['allow_multiple' => 'boolean', 'expires_at' => 'datetime'];

    public function options() { return $this->hasMany(PollOption::class); }
    public function votes()   { return $this->hasMany(PollVote::class); }
    public function post()    { return $this->belongsTo(Post::class); }
    public function author()  { return $this->belongsTo(User::class, 'author_id'); }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function totalVotes(): int
    {
        return $this->options->sum('votes_count');
    }
}
