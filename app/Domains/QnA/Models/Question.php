<?php

namespace App\Domains\QnA\Models;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id', 'title', 'slug', 'content', 'category_id', 'featured_image',
        'is_poll', 'is_anonymous', 'is_private', 'notify_email',
        'status', 'closed_reason', 'duplicate_of', 'views_count', 'answers_count',
        'best_answer_id', 'votes', 'edited_at',
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
    public function follows()    { return $this->hasMany(\App\Models\QuestionFollow::class); }
    public function flags()      { return $this->hasMany(\App\Models\QuestionFlag::class); }
    public function revisions()  { return $this->hasMany(\App\Models\QuestionRevision::class)->latest(); }
    public function bookmarks()  { return $this->morphMany(\App\Models\Bookmark::class, 'bookmarkable'); }
}
