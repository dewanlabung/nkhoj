<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyOption extends Model
{
    public $timestamps = false;
    protected $fillable = ['survey_question_id', 'label', 'sort_order'];

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }
}
