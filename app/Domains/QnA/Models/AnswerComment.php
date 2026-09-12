<?php

namespace App\Domains\QnA\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AnswerComment extends Model
{
    protected $fillable = ['answer_id', 'user_id', 'content', 'is_anonymous'];

    protected $casts = ['is_anonymous' => 'boolean'];

    public function answer() { return $this->belongsTo(Answer::class); }
    public function user()   { return $this->belongsTo(User::class); }
}
