<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagePostComment extends Model
{
    protected $fillable = ['page_post_id', 'user_id', 'body'];

    public function post()
    {
        return $this->belongsTo(PagePost::class, 'page_post_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
