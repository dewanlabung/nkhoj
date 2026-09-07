<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;

class CategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = $category->posts()
            ->with(['author', 'category'])
            ->published()
            ->latest('published_at')
            ->paginate(12, ['*'], 'page');

        $questions = $category->questions()
            ->with(['user'])
            ->where('status', '!=', 'pending')
            ->withCount('answers')
            ->latest()
            ->paginate(10, ['*'], 'qpage');

        if (request()->ajax() || request('ajax')) {
            return view('partials.posts-feed', compact('posts'));
        }

        return view('categories.show', compact('category', 'posts', 'questions'));
    }
}
