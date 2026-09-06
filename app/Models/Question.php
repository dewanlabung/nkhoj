<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'user_id', 'title', 'slug', 'content', 'category_id', 'featured_image',
        'is_poll', 'is_anonymous', 'is_private', 'notify_email',
        'status', 'views_count', 'answers_count', 'best_answer_id', 'votes',
    ];

    protected $casts = [
        'is_poll'      => 'boolean',
        'is_anonymous' => 'boolean',
        'is_private'   => 'boolean',
        'notify_email' => 'boolean',
    ];

    public function user()       { return $this->belongsTo(User::class); }
    public function category()   { return $this->belongsTo(Category::class); }
    public function answers()    { return $this->hasMany(Answer::class)->latest(); }
    public function bestAnswer() { return $this->belongsTo(Answer::class, 'best_answer_id'); }
    public function tags()       { return $this->belongsToMany(Tag::class, 'question_tag'); }
}
