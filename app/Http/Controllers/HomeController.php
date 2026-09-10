<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Poll;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Models\Widget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    public function index()
    {
        // 3-card hero strip (featured or top 3 by views)
        $heroStrip = Cache::remember('home_hero_strip', 600, fn() =>
            Post::with(['author', 'category'])->published()
                ->orderByDesc('is_featured')->orderByDesc('view_count')
                ->limit(3)->get()
        );

        // Editor's pick (next 3 after hero)
        $heroIds     = $heroStrip->pluck('id');
        $editorsPick = Cache::remember('home_editors_pick', 600, fn() =>
            Post::with(['author', 'category'])->published()
                ->whereNotIn('id', $heroIds)
                ->orderByDesc('view_count')
                ->limit(3)->get()
        );

        $posts = Post::with(['author', 'category', 'tags'])->published()
                    ->latest('published_at')->paginate(12);

        // Fallback sidebar data (used when no matching widget exists)
        $trending   = Cache::remember('home_trending', 600, fn() =>
            Post::published()->orderByDesc('view_count')->limit(5)->get()
        );
        $categories = Cache::remember('home_categories', 900, fn() =>
            Category::withCount(['posts' => fn($q) => $q->published()])->orderBy('sort_order')->get()
        );

        // Load widgets for each sidebar position
        $sidebarWidgets  = Cache::remember('widgets_sidebar', 600, fn() => Widget::forPosition('sidebar'));
        $homeTopWidgets  = Cache::remember('widgets_home_top', 600, fn() => Widget::forPosition('home_top'));
        $homeBottomWidgets = Cache::remember('widgets_home_bottom', 600, fn() => Widget::forPosition('home_bottom'));

        // Pre-fetch data needed by widget types that are active
        $allWidgets = $sidebarWidgets->merge($homeTopWidgets)->merge($homeBottomWidgets);
        $widgetTypes = $allWidgets->pluck('type')->unique();

        $widgetData = [];

        if ($widgetTypes->contains('popular_posts')) {
            $widgetData['popular_posts'] = Cache::remember('widget_popular_posts', 600, fn() =>
                Post::with(['author','category'])->published()->orderByDesc('view_count')->limit(5)->get()
            );
        }
        if ($widgetTypes->contains('popular_tags')) {
            $widgetData['popular_tags'] = Cache::remember('widget_popular_tags', 900, fn() =>
                Tag::withCount(['posts' => fn($q) => $q->published()])
                    ->having('posts_count', '>', 0)->orderByDesc('posts_count')->limit(15)->get()
            );
        }
        if ($widgetTypes->contains('recommended_posts')) {
            $widgetData['recommended_posts'] = Cache::remember('widget_recommended_posts', 600, fn() =>
                Post::with(['author','category'])->published()->latest('published_at')->limit(5)->get()
            );
        }
        if ($widgetTypes->contains('voting_poll')) {
            $widgetData['voting_poll'] = Cache::remember('widget_voting_poll', 300, fn() =>
                Poll::with('options')->latest()->first()
            );
        }
        if ($widgetTypes->contains('follow_us') || $widgetTypes->contains('about_us') || $widgetTypes->contains('social_proof')) {
            $path = storage_path('app/site_settings.json');
            $widgetData['settings'] = File::exists($path)
                ? (json_decode(File::get($path), true) ?? []) : [];
        }
        if ($widgetTypes->contains('trending_now')) {
            $widgetData['trending_now'] = Cache::remember('widget_trending_now', 300, fn() =>
                Post::with(['author','category'])
                    ->published()
                    ->where('published_at', '>=', now()->subHours(24))
                    ->orderByDesc('view_count')
                    ->limit(5)
                    ->get()
                    ->whenEmpty(fn() => Post::with(['author','category'])->published()->orderByDesc('view_count')->limit(5)->get())
            );
        }
        if ($widgetTypes->contains('comment_highlights')) {
            $widgetData['comment_highlights'] = Cache::remember('widget_comment_highlights', 300, fn() =>
                Comment::with(['post:id,slug,title', 'author:id,name,username'])
                    ->approved()
                    ->whereNotNull('body')
                    ->where('body', '!=', '')
                    ->latest()
                    ->limit(4)
                    ->get()
            );
        }
        if ($widgetTypes->contains('related_searches') && !isset($widgetData['popular_tags'])) {
            $widgetData['popular_tags'] = Cache::remember('widget_related_tags', 900, fn() =>
                Tag::withCount(['posts' => fn($q) => $q->published()])
                    ->having('posts_count', '>', 0)->orderByDesc('posts_count')->limit(12)->get()
            );
        }

        if (request()->ajax() || request('ajax')) {
            return view('partials.posts-feed', compact('posts'));
        }

        return view('home', compact(
            'heroStrip', 'editorsPick', 'posts',
            'trending', 'categories',
            'sidebarWidgets', 'homeTopWidgets', 'homeBottomWidgets',
            'widgetData'
        ));
    }

    public function leaderboard()
    {
        $topByFollowers = User::withCount('followers')
            ->where('role', '!=', 'reader')
            ->orderByDesc('followers_count')
            ->limit(10)
            ->get();

        $topByViews = User::withSum(['posts as total_views' => fn($q) => $q->published()], 'view_count')
            ->where('role', '!=', 'reader')
            ->orderByDesc('total_views')
            ->limit(10)
            ->get();

        $topByPosts = User::withCount(['posts as published_posts_count' => fn($q) => $q->published()])
            ->where('role', '!=', 'reader')
            ->orderByDesc('published_posts_count')
            ->limit(10)
            ->get();

        return view('leaderboard', compact('topByFollowers', 'topByViews', 'topByPosts'));
    }

    public function followingFeed()
    {
        $followingIds = auth()->user()->following()->pluck('users.id');

        $posts = Post::with(['author', 'category', 'tags'])
            ->published()
            ->whereIn('author_id', $followingIds)
            ->latest('published_at')
            ->paginate(12);

        if (request()->ajax() || request('ajax')) {
            return view('partials.posts-feed', compact('posts'));
        }

        return view('following-feed', compact('posts'));
    }
}
