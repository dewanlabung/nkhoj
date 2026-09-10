<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['slug', 'name_en', 'name_ne'];

    protected $appends = ['name'];

    public function getNameAttribute(): string
    {
        return $this->name_en ?? $this->slug;
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_tag');
    }
}
