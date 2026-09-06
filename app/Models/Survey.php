<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $fillable = ['post_id', 'title', 'type', 'status', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at'   => 'datetime',
        ];
    }

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('sort_order');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function toAnalyticsArray(): array
    {
        $totalResponses = $this->responses()->count();

        return [
            'title'            => $this->title,
            'total_responses'  => $totalResponses,
            'completion_rate'  => $totalResponses > 0 ? round($this->responses()->whereNotNull('completed_at')->count() / $totalResponses, 2) : 0,
            'questions'        => $this->questions->map(fn($q) => $q->toAnalyticsArray())->toArray(),
            'geography'        => $this->responses()
                ->selectRaw('country_code, COUNT(*) as count')
                ->groupBy('country_code')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(fn($r) => ['country' => $r->country_code, 'count' => $r->count, 'pct' => $totalResponses > 0 ? round($r->count / $totalResponses * 100, 1) : 0])
                ->toArray(),
        ];
    }
}
