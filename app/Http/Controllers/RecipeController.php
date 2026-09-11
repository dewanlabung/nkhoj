<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeLike;
use App\Models\RecipeRating;
use App\Traits\SavesOptimizedThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecipeController extends Controller
{
    use SavesOptimizedThumbnail;
    public function index(Request $request)
    {
        $tab      = $request->query('tab', 'trending');
        $cuisine  = $request->query('cuisine');
        $meal     = $request->query('meal');
        $search   = $request->query('q');

        $query = Recipe::where('is_published', true)->with('author');

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }
        if ($cuisine) {
            $query->where('cuisine_type', $cuisine);
        }
        if ($meal) {
            $query->where('meal_type', $meal);
        }

        $query = match($tab) {
            'newest'   => $query->latest(),
            'popular'  => $query->orderByDesc('views_count'),
            'favourite'=> $query->orderByDesc('likes_count'),
            default    => $query->orderByDesc('likes_count')->orderByDesc('views_count'),
        };

        $recipes  = $query->paginate(18)->withQueryString();
        $featured = Recipe::where('is_published', true)->where('is_featured', true)->latest()->limit(4)->get();

        $cuisines = ['Nepali', 'Indian', 'Chinese', 'Italian', 'Mexican', 'Japanese', 'Thai', 'Continental', 'Newari', 'Tibetan'];
        $meals    = ['Breakfast', 'Lunch', 'Dinner', 'Snack', 'Dessert', 'Drinks'];

        return view('recipes.index', compact('recipes', 'featured', 'tab', 'cuisine', 'meal', 'cuisines', 'meals', 'search'));
    }

    public function show(string $slug)
    {
        $recipe  = Recipe::where('slug', $slug)->where('is_published', true)->with('author')->firstOrFail();
        $recipe->increment('views_count');
        $isLiked = auth()->check() && $recipe->isLikedBy(auth()->user());
        $related = Recipe::where('is_published', true)
            ->where('id', '!=', $recipe->id)
            ->where(fn($q) => $q->where('cuisine_type', $recipe->cuisine_type)->orWhere('meal_type', $recipe->meal_type))
            ->limit(6)->get();

        return view('recipes.show', compact('recipe', 'isLiked', 'related'));
    }

    public function create()
    {
        return view('recipes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'description'  => 'nullable|string|max:1000',
            'cuisine_type' => 'nullable|string|max:100',
            'meal_type'    => 'nullable|string|max:50',
            'difficulty'   => 'required|in:easy,medium,hard',
            'prep_time'    => 'nullable|integer|min:0',
            'cook_time'    => 'nullable|integer|min:0',
            'servings'     => 'nullable|integer|min:1',
            'ingredients'  => 'nullable|array',
            'steps'        => 'nullable|array',
            'thumbnail'    => 'nullable|image|max:4096',
        ]);

        $slug = Str::slug($data['title']);
        $base = $slug ?: 'recipe'; $i = 1;
        while (Recipe::where('slug', $slug)->exists()) { $slug = "{$base}-{$i}"; $i++; }

        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailUrl = $this->saveOptimizedThumbnail($request->file('thumbnail'), 'recipes');
        }

        $recipe = Recipe::create([
            'uuid'         => Str::uuid(),
            'user_id'      => auth()->id(),
            'title'        => $data['title'],
            'slug'         => $slug,
            'description'  => $data['description'],
            'cuisine_type' => $data['cuisine_type'],
            'meal_type'    => $data['meal_type'],
            'difficulty'   => $data['difficulty'],
            'prep_time'    => $data['prep_time'],
            'cook_time'    => $data['cook_time'],
            'servings'     => $data['servings'] ?? 2,
            'ingredients'  => array_filter($data['ingredients'] ?? []),
            'steps'        => array_filter($data['steps'] ?? []),
            'thumbnail_url'=> $thumbnailUrl,
        ]);

        return redirect("/recipe/{$recipe->slug}")->with('success', 'Recipe shared!');
    }

    public function like(Recipe $recipe)
    {
        $userId = auth()->id();
        $existing = RecipeLike::where('recipe_id', $recipe->id)->where('user_id', $userId)->first();
        if ($existing) {
            $existing->delete();
            $recipe->decrement('likes_count');
            $liked = false;
        } else {
            RecipeLike::create(['recipe_id' => $recipe->id, 'user_id' => $userId]);
            $recipe->increment('likes_count');
            $liked = true;
        }
        return response()->json(['liked' => $liked, 'count' => $recipe->fresh()->likes_count]);
    }

    public function rate(Request $request, Recipe $recipe)
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
        ]);

        RecipeRating::updateOrCreate(
            ['recipe_id' => $recipe->id, 'user_id' => auth()->id()],
            $data
        );

        $avg   = $recipe->ratings()->avg('rating');
        $count = $recipe->ratings()->count();
        $recipe->update(['rating_avg' => round($avg, 2), 'ratings_count' => $count]);

        return response()->json(['rating_avg' => round($avg, 1), 'ratings_count' => $count, 'your_rating' => $data['rating']]);
    }
}
