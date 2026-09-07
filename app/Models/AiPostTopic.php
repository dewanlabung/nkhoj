<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPostTopic extends Model
{
    protected $fillable = [
        'keyword', 'language', 'category_id', 'rss_source',
        'frequency', 'is_active', 'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'   => 'boolean',
            'last_run_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function isDueToday(): bool
    {
        if ($this->frequency === 'manual') return false;
        if (is_null($this->last_run_at)) return true;
        if ($this->frequency === 'daily') return $this->last_run_at->isYesterday() || $this->last_run_at->lt(now()->startOfDay());
        if ($this->frequency === 'weekly') return $this->last_run_at->lt(now()->subWeek());
        return false;
    }
}
