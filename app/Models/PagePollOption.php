<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagePollOption extends Model
{
    public $timestamps = false;

    protected $fillable = ['page_post_id', 'text', 'votes_count', 'sort_order'];

    public function post()
    {
        return $this->belongsTo(PagePost::class, 'page_post_id');
    }

    public function votes()
    {
        return $this->hasMany(PagePollVote::class);
    }
}
