<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Post;

class PostController extends Controller
{
    public function show(string $slug)
    {
        $post = Post::with(['author', 'category', 'tags'])->published()->where('slug', $slug)->firstOrFail();

        $post->increment('view_count');

        $related = Post::with(['author'])
            ->published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(4)
            ->get();

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

        return view('posts.show', compact('post', 'related', 'comments', 'reactionCounts', 'userReaction', 'isBookmarked'));
    }
}
