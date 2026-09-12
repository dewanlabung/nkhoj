<?php

namespace App\Domains\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class CommentReaction extends Model
{
    public $timestamps = false;

    protected $fillable = ['comment_id', 'user_id', 'session_key', 'emoji'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
