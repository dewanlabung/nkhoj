<?php

namespace App\Domains\Place\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageMilestone extends Model
{
    protected $table = 'page_milestones';
    public $timestamps = false;

    protected $fillable = ['social_page_id', 'milestone_type', 'milestone_value', 'achieved_at'];

    protected $casts = ['achieved_at' => 'datetime'];

    public function page(): BelongsTo
    {
        return $this->belongsTo(SocialPage::class, 'social_page_id');
    }

    public static function checkAndRecord(SocialPage $page): void
    {
        $thresholds = [100, 500, 1000, 5000, 10000, 50000, 100000, 500000, 1000000];
        $count = $page->followers_count;
        foreach ($thresholds as $t) {
            if ($count >= $t) {
                static::firstOrCreate(
                    ['social_page_id' => $page->id, 'milestone_type' => 'followers', 'milestone_value' => $t],
                    ['achieved_at' => now()]
                );
            }
        }
    }
}
