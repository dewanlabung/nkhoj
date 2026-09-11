<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reel extends Model
{
    protected $fillable = ['uuid', 'user_id', 'title', 'description', 'video_url', 'thumbnail_url', 'views_count', 'likes_count', 'comments_count', 'is_published'];

    public function user()     { return $this->belongsTo(User::class); }
    public function likes()    { return $this->hasMany(ReelLike::class); }
    public function comments() { return $this->hasMany(ReelComment::class)->latest(); }

    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}
