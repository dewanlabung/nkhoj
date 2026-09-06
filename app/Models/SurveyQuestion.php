<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    protected $fillable = ['survey_id', 'sort_order', 'question', 'type'];

    public function options()
    {
        return $this->hasMany(SurveyOption::class)->orderBy('sort_order');
    }

    public function toAnalyticsArray(): array
    {
        $options = $this->options()->withCount('responses')->get();

        return [
            'question' => $this->question,
            'type'     => $this->type,
            'options'  => $options->map(fn($o) => [
                'label'           => $o->label,
                'response_count'  => $o->responses_count,
            ])->toArray(),
        ];
    }
}
