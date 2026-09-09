<?php

namespace App\Http\Controllers;

use App\Models\PostSeries;
use Illuminate\Http\Request;

class PostSeriesController extends Controller
{
    public function index()
    {
        $series = PostSeries::with(['author:id,name,username', 'posts' => fn($q) => $q->published()])
            ->where('is_published', true)
            ->withCount(['posts as published_posts_count' => fn($q) => $q->published()])
            ->having('published_posts_count', '>', 0)
            ->latest()
            ->paginate(12);

        return view('series.index', compact('series'));
    }

    public function show(PostSeries $series)
    {
        abort_if(!$series->is_published, 404);

        $posts = $series->posts()->with(['author:id,name,username', 'category:id,name_en,slug'])
            ->published()
            ->orderBy('series_order')
            ->get();

        return view('series.show', compact('series', 'posts'));
    }

    public function create()
    {
        return view('series.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $data['user_id'] = auth()->id();

        $series = PostSeries::create($data);

        return redirect("/series/{$series->slug}")->with('success', 'Series created!');
    }
}
