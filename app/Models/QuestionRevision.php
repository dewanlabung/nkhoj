<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionRevision extends Model
{
    public $timestamps = false;
    protected $fillable = ['question_id', 'user_id', 'title', 'content'];
    protected $dates = ['created_at'];

    public function question() { return $this->belongsTo(Question::class); }
    public function user()     { return $this->belongsTo(User::class); }
}
