<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagePollVote extends Model
{
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = ['page_post_id', 'page_poll_option_id', 'user_id'];

    protected $casts = ['created_at' => 'datetime'];

    public function option()
    {
        return $this->belongsTo(PagePollOption::class, 'page_poll_option_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
