<?php

namespace App\Domains\QnA\Models;

use Illuminate\Database\Eloquent\Model;

class AnswerRevision extends Model
{
    public $timestamps = false;
    protected $fillable = ['answer_id', 'user_id', 'content'];
    protected $dates = ['created_at'];

    public function answer() { return $this->belongsTo(Answer::class); }
    public function user()   { return $this->belongsTo(User::class); }
}
