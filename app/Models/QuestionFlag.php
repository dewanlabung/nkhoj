<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionFlag extends Model
{
    protected $fillable = ['question_id', 'user_id', 'reason', 'note', 'resolved'];

    protected $casts = ['resolved' => 'boolean'];

    public function question() { return $this->belongsTo(Question::class); }
    public function user()     { return $this->belongsTo(User::class); }
}
