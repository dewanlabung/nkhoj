<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DailyAnalytic extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'date';
    protected $keyType = 'string';

    protected $fillable = ['date', 'page_views', 'unique_visitors'];

    public static function recordView(string $visitorKey): void
    {
        $today = now()->toDateString();
        DB::statement('
            INSERT INTO daily_analytics (date, page_views, unique_visitors)
            VALUES (?, 1, 1)
            ON DUPLICATE KEY UPDATE
                page_views = page_views + 1,
                unique_visitors = unique_visitors + IF(
                    JSON_CONTAINS(COALESCE(@uv_cache, JSON_ARRAY()), JSON_QUOTE(?)), 0, 1
                )
        ', [$today, $visitorKey]);
    }

    public static function incrementPageView(): void
    {
        $today = now()->toDateString();
        DB::statement('
            INSERT INTO daily_analytics (date, page_views, unique_visitors)
            VALUES (?, 1, 0)
            ON DUPLICATE KEY UPDATE page_views = page_views + 1
        ', [$today]);
    }

    public static function getLast30Days(): array
    {
        $rows = DB::table('daily_analytics')
            ->where('date', '>=', now()->subDays(29)->toDateString())
            ->orderBy('date')
            ->pluck('page_views', 'date')
            ->toArray();

        $labels = [];
        $views  = [];
        for ($i = 29; $i >= 0; $i--) {
            $date     = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d M');
            $views[]  = (int) ($rows[$date] ?? 0);
        }
        return compact('labels', 'views');
    }
}
