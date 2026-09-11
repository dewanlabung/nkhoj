<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $query = Recipe::where('is_published', true)
            ->with('author:id,name,username')
            ->select('id', 'title', 'slug', 'description', 'thumbnail_url', 'cuisine_type', 'meal_type', 'difficulty', 'prep_time', 'cook_time', 'servings', 'rating_avg', 'ratings_count', 'likes_count', 'views_count', 'user_id');

        if ($s = $request->query('q')) {
            $query->where('title', 'like', "%{$s}%");
        }
        if ($cuisine = $request->query('cuisine')) {
            $query->where('cuisine_type', $cuisine);
        }
        if ($meal = $request->query('meal')) {
            $query->where('meal_type', $meal);
        }

        $recipes = $query->orderByDesc('likes_count')->paginate(20)->withQueryString();

        return response()->json([
            'data' => $recipes->items(),
            'meta' => [
                'current_page' => $recipes->currentPage(),
                'last_page'    => $recipes->lastPage(),
                'per_page'     => $recipes->perPage(),
                'total'        => $recipes->total(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $recipe = Recipe::where('slug', $slug)->where('is_published', true)
            ->with('author:id,name,username')
            ->firstOrFail();

        return response()->json(['data' => $recipe]);
    }
}
