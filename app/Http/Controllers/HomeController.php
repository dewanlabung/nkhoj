<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Poll;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Widget;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    public function index()
    {
        // 3-card hero strip (featured or top 3 by views)
        $heroStrip = Post::with(['author', 'category'])->published()
                        ->orderByDesc('is_featured')->orderByDesc('view_count')
                        ->limit(3)->get();

        // Editor's pick (next 3 after hero)
        $heroIds     = $heroStrip->pluck('id');
        $editorsPick = Post::with(['author', 'category'])->published()
                        ->whereNotIn('id', $heroIds)
                        ->orderByDesc('view_count')
                        ->limit(3)->get();

        $posts = Post::with(['author', 'category', 'tags'])->published()
                    ->whereNotIn('id', $heroIds)
                    ->latest('published_at')->paginate(12);

        // Fallback sidebar data (used when no matching widget exists)
        $trending   = Post::published()->orderByDesc('view_count')->limit(5)->get();
        $categories = Category::withCount(['posts' => fn($q) => $q->published()])->orderBy('sort_order')->get();

        // Load widgets for each sidebar position
        $sidebarWidgets  = Widget::forPosition('sidebar');
        $homeTopWidgets  = Widget::forPosition('home_top');
        $homeBottomWidgets = Widget::forPosition('home_bottom');

        // Pre-fetch data needed by widget types that are active
        $allWidgets = $sidebarWidgets->merge($homeTopWidgets)->merge($homeBottomWidgets);
        $widgetTypes = $allWidgets->pluck('type')->unique();

        $widgetData = [];

        if ($widgetTypes->contains('popular_posts')) {
            $widgetData['popular_posts'] = Post::with(['author','category'])
                ->published()->orderByDesc('view_count')->limit(5)->get();
        }
        if ($widgetTypes->contains('popular_tags')) {
            $widgetData['popular_tags'] = Tag::withCount(['posts' => fn($q) => $q->published()])
                ->having('posts_count', '>', 0)->orderByDesc('posts_count')->limit(15)->get();
        }
        if ($widgetTypes->contains('recommended_posts')) {
            $widgetData['recommended_posts'] = Post::with(['author','category'])
                ->published()->latest('published_at')->limit(5)->get();
        }
        if ($widgetTypes->contains('voting_poll')) {
            $widgetData['voting_poll'] = Poll::with('options')->latest()->first();
        }
        if ($widgetTypes->contains('follow_us') || $widgetTypes->contains('about_us')) {
            $path = storage_path('app/site_settings.json');
            $widgetData['settings'] = File::exists($path)
                ? (json_decode(File::get($path), true) ?? []) : [];
        }

        return view('home', compact(
            'heroStrip', 'editorsPick', 'posts',
            'trending', 'categories',
            'sidebarWidgets', 'homeTopWidgets', 'homeBottomWidgets',
            'widgetData'
        ));
    }
}
