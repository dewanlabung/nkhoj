<?php

namespace App\Http\Controllers;

use App\Models\Blog\Category;
use App\Models\MediaContent\Event;
use App\Models\Blog\Post;
use App\Models\QnA\Question;
use App\Models\MediaContent\Recipe;
use App\Models\SocialPages\SocialPage;
use App\Models\Blog\Tag;

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
