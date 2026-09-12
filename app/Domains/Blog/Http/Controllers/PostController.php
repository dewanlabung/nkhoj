<?php

namespace App\Domains\Blog\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Widget;
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
    public function show(string $slug)
    {
        $post = Post::with(['author', 'category', 'tags', 'series'])->published()->where('slug', $slug)->firstOrFail();

        $post->increment('view_count');

        $related = Post::with(['author'])
            ->published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(4)
            ->get();

        // Tag-based recommendations: posts sharing the most tags with this post
        $tagIds = $post->tags->pluck('id');
        $tagRecommended = collect();
        if ($tagIds->isNotEmpty()) {
            $tagRecommended = Post::with(['author', 'category'])
                ->published()
                ->where('id', '!=', $post->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->whereHas('tags', fn($q) => $q->whereIn('tags.id', $tagIds))
                ->withCount(['tags as shared_tags' => fn($q) => $q->whereIn('tags.id', $tagIds)])
                ->orderByDesc('shared_tags')
                ->orderByDesc('view_count')
                ->limit(4)
                ->get();
        }

        $comments = $post->comments()
            ->with(['author', 'replies.author'])
            ->approved()
            ->topLevel()
            ->latest()
            ->get();

        $reactionCounts = $post->reactionCounts();

        $userReaction = null;
        $isBookmarked = false;
        if (auth()->check()) {
            $userReaction = $post->reactions()->where('user_id', auth()->id())->value('emoji');
            $isBookmarked = Bookmark::where('user_id', auth()->id())
                ->where('bookmarkable_type', \App\Models\Post::class)
                ->where('bookmarkable_id', $post->id)
                ->exists();
        }

        // Load sidebar widgets for post page
        $sidebarWidgets = Widget::forPosition('sidebar')->merge(Widget::forPosition('post_sidebar'));
        $widgetTypes    = $sidebarWidgets->pluck('type')->unique();
        $widgetData     = [];

        if ($widgetTypes->contains('popular_posts')) {
            $widgetData['popular_posts'] = Post::with(['author','category'])
                ->published()->orderByDesc('view_count')->limit(5)->get();
        }
        if ($widgetTypes->contains('popular_tags') || $widgetTypes->contains('related_searches')) {
            $widgetData['popular_tags'] = Tag::withCount(['posts' => fn($q) => $q->published()])
                ->having('posts_count', '>', 0)->orderByDesc('posts_count')->limit(15)->get();
        }
        if ($widgetTypes->contains('recommended_posts')) {
            $widgetData['recommended_posts'] = Post::with(['author','category'])
                ->published()->latest('published_at')->limit(5)->get();
        }
        if ($widgetTypes->contains('trending_now')) {
            $widgetData['trending_now'] = Post::with(['author','category'])
                ->published()->where('published_at', '>=', now()->subHours(24))
                ->orderByDesc('view_count')->limit(5)->get()
                ->whenEmpty(fn() => Post::with(['author','category'])->published()->orderByDesc('view_count')->limit(5)->get());
        }
        if ($widgetTypes->contains('comment_highlights')) {
            $widgetData['comment_highlights'] = Comment::with(['post:id,slug,title','author:id,name,username'])
                ->approved()->whereNotNull('body')->where('body','!=','')->latest()->limit(4)->get();
        }
        if ($widgetTypes->contains('follow_us') || $widgetTypes->contains('about_us') || $widgetTypes->contains('social_proof')) {
            $path = storage_path('app/site_settings.json');
            $widgetData['settings'] = File::exists($path) ? (json_decode(File::get($path), true) ?? []) : [];
        }

        return view('posts.show', compact('post', 'related', 'tagRecommended', 'comments', 'reactionCounts', 'userReaction', 'isBookmarked', 'sidebarWidgets', 'widgetData'));
    }

    public function share(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();
        $post->increment('share_count');
        return response()->json(['share_count' => $post->share_count]);
    }
}
