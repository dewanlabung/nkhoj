<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

abstract class BaseAdminController extends Controller
{
    protected function requireAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Admin access only.');
        }
    }

    protected function flushPostCaches(): void
    {
        foreach (['home_hero_strip', 'home_editors_pick', 'home_trending', 'widget_popular_posts', 'widget_recommended_posts', 'widget_trending_now', 'widget_comment_highlights'] as $key) {
            Cache::forget($key);
        }
    }

    protected function flushWidgetCaches(): void
    {
        foreach (['widgets_sidebar', 'widgets_home_top', 'widgets_home_bottom'] as $key) {
            Cache::forget($key);
        }
        $this->flushPostCaches();
    }
}
