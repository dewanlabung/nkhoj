<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;

class SitemapController extends Controller
{
    public function index()
    {
        $posts      = Post::where('status', 'published')->latest('published_at')->get(['slug', 'published_at', 'updated_at']);
        $categories = Category::all(['slug', 'updated_at']);
        $tags       = Tag::all(['slug', 'updated_at']);

        return response()->view('sitemap', compact('posts', 'categories', 'tags'))
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
