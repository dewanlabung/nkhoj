<?php

namespace App\Domains\Blog\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StoryHighlight extends Model
{
    protected $fillable = ['user_id', 'title', 'cover_url', 'order'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stories()
    {
        return $this->belongsToMany(Story::class, 'story_highlight_items', 'highlight_id', 'story_id')
                    ->withPivot('order')
                    ->orderByPivot('order');
    }
}
