<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Post;
use App\Models\Recipe;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->validate(['q' => 'required|string|min:2|max:100'])['q'];

        $posts = Post::where('status', 'published')
            ->where(fn($qb) => $qb->where('title', 'like', "%{$q}%")->orWhere('excerpt', 'like', "%{$q}%"))
            ->select('id', 'title', 'slug', 'thumbnail_url', 'published_at')
            ->limit(5)->get()->map(fn($p) => [...$p->toArray(), 'type' => 'post']);

        $events = Event::where('is_published', true)
            ->where('title', 'like', "%{$q}%")
            ->select('id', 'title', 'slug', 'thumbnail_url', 'starts_at')
            ->limit(5)->get()->map(fn($e) => [...$e->toArray(), 'type' => 'event']);

        $recipes = Recipe::where('is_published', true)
            ->where('title', 'like', "%{$q}%")
            ->select('id', 'title', 'slug', 'thumbnail_url')
            ->limit(5)->get()->map(fn($r) => [...$r->toArray(), 'type' => 'recipe']);

        return response()->json([
            'data' => $posts->concat($events)->concat($recipes)->values(),
            'meta' => ['query' => $q],
        ]);
    }
}
