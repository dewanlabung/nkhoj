<?php

namespace App\Domains\Blog\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = ['post_id', 'user_id', 'parent_id', 'body', 'guest_name', 'guest_email', 'is_approved'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('author')->approved();
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function reactionCounts(): array
    {
        try {
            return $this->reactions()
                ->selectRaw('emoji, count(*) as cnt')
                ->groupBy('emoji')
                ->pluck('cnt', 'emoji')
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    public function displayName(): string
    {
        return $this->author?->name ?? $this->guest_name ?? 'अतिथि';
    }
}
