<?php

namespace App\Domains\Place\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PagePostLike extends Model
{
    public $timestamps = false;
    protected $fillable = ['page_post_id', 'user_id', 'reaction'];

    public function post() { return $this->belongsTo(PagePost::class, 'page_post_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
