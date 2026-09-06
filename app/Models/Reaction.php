<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    public $timestamps = false;

    protected $fillable = ['post_id', 'user_id', 'session_key', 'emoji'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
