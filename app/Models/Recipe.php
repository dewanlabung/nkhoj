<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Recipe extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'title', 'slug', 'description',
        'cuisine_type', 'meal_type', 'difficulty',
        'prep_time', 'cook_time', 'servings',
        'ingredients', 'steps', 'tags',
        'thumbnail_url', 'is_published', 'is_featured',
    ];

    protected $casts = [
        'ingredients'  => 'array',
        'steps'        => 'array',
        'tags'         => 'array',
        'is_published' => 'boolean',
        'is_featured'  => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function likes()
    {
        return $this->hasMany(RecipeLike::class);
    }

    public function getTotalTimeAttribute(): int
    {
        return ($this->prep_time ?? 0) + ($this->cook_time ?? 0);
    }

    public function getDifficultyColorAttribute(): string
    {
        return match($this->difficulty) {
            'easy'   => 'text-green-600 bg-green-50',
            'medium' => 'text-yellow-600 bg-yellow-50',
            'hard'   => 'text-red-600 bg-red-50',
            default  => 'text-gray-600 bg-gray-50',
        };
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}
