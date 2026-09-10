<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Post;
use App\Models\Question;
use App\Models\Recipe;
use App\Models\SocialPage;
use App\Models\Tag;

class SitemapController extends Controller
{
    public function index()
    {
        $posts      = Post::where('status', 'published')->latest('published_at')->get(['slug', 'published_at', 'updated_at']);
        $categories = Category::all(['slug', 'updated_at']);
        $tags       = Tag::all(['slug', 'updated_at']);
        $events     = Event::where('is_published', true)->latest()->get(['slug', 'uuid', 'updated_at']);
        $recipes    = Recipe::where('is_published', true)->latest()->get(['slug', 'uuid', 'updated_at']);
        $questions  = Question::where('status', 'open')->latest()->get(['slug', 'id', 'updated_at']);
        $pages      = SocialPage::where('status', 'active')->latest()->get(['slug', 'updated_at']);

        return response()->view('sitemap', compact('posts', 'categories', 'tags', 'events', 'recipes', 'questions', 'pages'))
            ->header('Content-Type', 'application/xml');
    }

    public function feed()
    {
        $posts = Post::with('author', 'category')
            ->where('status', 'published')
            ->latest('published_at')
            ->limit(50)
            ->get();

        return response()->view('feed', compact('posts'))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
