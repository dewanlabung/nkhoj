<?php

namespace App\Domains\Blog\Models;

use App\Models\QnA\Question;
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
        return $this->belongsToMany(\App\Models\QnA\Question::class, 'question_tag');
    }
}
