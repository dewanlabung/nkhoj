<?php

namespace App\Http\Controllers;

use App\Models\Tag;

class TagController extends Controller
{
    public function show(string $slug)
    {
        $tag   = Tag::where('slug', $slug)->firstOrFail();
        $posts = $tag->posts()->with(['author', 'category'])
                     ->published()->latest('published_at')->paginate(12);

        return view('tags.show', compact('tag', 'posts'));
    }
}
