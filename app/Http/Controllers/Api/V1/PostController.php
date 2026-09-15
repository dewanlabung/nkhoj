<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::where('status', 'published')
            ->with('author:id,name,username')
            ->select('id', 'title', 'slug', 'excerpt', 'thumbnail_url', 'view_count', 'author_id', 'published_at');

        if ($s = $request->query('q')) {
            $query->where(fn($q) => $q->where('title', 'like', "%{$s}%")->orWhere('excerpt', 'like', "%{$s}%"));
        }
        if ($cat = $request->query('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $cat));
        }

        $posts = $query->latest('published_at')->paginate(20)->withQueryString();

        return response()->json([
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page'    => $posts->lastPage(),
                'per_page'     => $posts->perPage(),
                'total'        => $posts->total(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $post = Post::where('slug', $slug)->where('status', 'published')
            ->with('author:id,name,username')
            ->firstOrFail();

        return response()->json(['data' => $post]);
    }
}
