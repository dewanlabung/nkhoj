<?php

namespace App\Domains\QnA\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'question_id', 'user_id', 'content', 'is_best', 'is_anonymous', 'votes',
    ];

    protected $casts = [
        'is_best'      => 'boolean',
        'is_anonymous' => 'boolean',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function question() { return $this->belongsTo(Question::class); }
    public function comments() { return $this->hasMany(AnswerComment::class)->oldest(); }
    public function revisions(){ return $this->hasMany(AnswerRevision::class)->latest(); }
}
