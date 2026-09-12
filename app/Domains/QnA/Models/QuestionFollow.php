<?php

namespace App\Domains\QnA\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class QuestionFollow extends Model
{
    public $timestamps = false;
    protected $fillable = ['question_id', 'user_id'];

    public function question() { return $this->belongsTo(Question::class); }
    public function user()     { return $this->belongsTo(User::class); }
}
