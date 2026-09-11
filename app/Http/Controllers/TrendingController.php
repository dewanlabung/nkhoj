<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrendingController extends Controller
{
    public function index()
    {
        $trending = $this->getTrending();
        return view('trending.index', compact('trending'));
    }

    public function widget()
    {
        return $this->getTrending();
    }

    public static function getTrending(int $limit = 15): \Illuminate\Support\Collection
    {
        return Cache::remember('trending_topics', 600, function () use ($limit) {
            return DB::table('tags')
                ->join('post_tag', 'tags.id', '=', 'post_tag.tag_id')
                ->join('posts', 'post_tag.post_id', '=', 'posts.id')
                ->where('posts.created_at', '>=', now()->subHours(24))
                ->select('tags.id', 'tags.slug', 'tags.name_en', DB::raw('COUNT(post_tag.post_id) as post_count'))
                ->groupBy('tags.id', 'tags.slug', 'tags.name_en')
                ->orderByDesc('post_count')
                ->limit($limit)
                ->get()
                ->map(fn($t) => (object)[
                    'id'    => $t->id,
                    'slug'  => $t->slug,
                    'name'  => $t->name_en ?: '#' . $t->slug,
                    'count' => $t->post_count,
                ]);
        });
    }
}
