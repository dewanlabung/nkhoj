<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Post;
use App\Models\Question;
use App\Models\Recipe;
use App\Models\SearchLog;
use App\Models\SocialPage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim($request->get('q', ''));
        $type  = $request->get('type', 'all');

        $results = null;
        $counts  = [
            'all'      => 0,
            'posts'    => 0,
            'pages'    => 0,
            'events'   => 0,
            'users'    => 0,
            'questions'=> 0,
            'recipes'  => 0,
        ];

        $trending = DB::table('search_logs')
            ->select('query', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('query')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('query');

        // Recent searches and suggested people for empty state
        $recent = collect();
        $suggested = collect();

        if ($query === '') {
            if (auth()->check()) {
                $recent = DB::table('search_logs')
                    ->where('user_id', auth()->id())
                    ->orderByDesc('created_at')
                    ->limit(40)
                    ->pluck('query')
                    ->unique()
                    ->take(8)
                    ->values();
            }

            $suggested = User::query()
                ->when(auth()->check(), fn($q) => $q->where('id', '!=', auth()->id()))
                ->latest()
                ->limit(6)
                ->get(['id', 'name', 'username', 'avatar_url', 'bio']);
        }

        if ($query !== '') {
            $like = "%{$query}%";

            $counts['posts'] = Post::published()
                ->where(fn($q) => $q->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('body', 'like', $like))
                ->count();

            // social_pages.categories is a JSON array; search name, page_type, bio instead
            $counts['pages'] = SocialPage::where('status', 'active')
                ->where(fn($q) => $q->where('name', 'like', $like)
                    ->orWhere('page_type', 'like', $like)
                    ->orWhere('bio', 'like', $like))
                ->count();

            $counts['events'] = Event::where('is_published', true)
                ->where(fn($q) => $q->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('venue', 'like', $like)
                    ->orWhere('organizer', 'like', $like))
                ->count();

            $counts['users'] = User::where(fn($q) => $q->where('name', 'like', $like)
                ->orWhere('username', 'like', $like)
                ->orWhere('bio', 'like', $like))
                ->count();

            $counts['questions'] = Question::where(fn($q) => $q->where('title', 'like', $like)
                ->orWhere('content', 'like', $like))
                ->count();

            $counts['recipes'] = Recipe::where('is_published', true)
                ->where(fn($q) => $q->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('cuisine_type', 'like', $like)
                    ->orWhere('meal_type', 'like', $like))
                ->count();

            $counts['all'] = array_sum(array_values($counts));

            DB::table('search_logs')->insert([
                'user_id'       => auth()->id(),
                'query'         => $query,
                'results_count' => $counts['all'],
                'ip'            => $request->ip(),
                'created_at'    => now(),
            ]);

            $results = match ($type) {
                'posts' => Post::with(['author', 'category', 'tags'])
                    ->published()
                    ->where(fn($q) => $q->where('title', 'like', $like)
                        ->orWhere('excerpt', 'like', $like)
                        ->orWhere('body', 'like', $like))
                    ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", [$like])
                    ->orderByDesc('published_at')
                    ->paginate(12)
                    ->withQueryString(),

                'pages' => SocialPage::where('status', 'active')
                    ->where(fn($q) => $q->where('name', 'like', $like)
                        ->orWhere('page_type', 'like', $like)
                        ->orWhere('bio', 'like', $like))
                    ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", [$like])
                    ->orderByDesc('followers_count')
                    ->paginate(12)
                    ->withQueryString(),

                'events' => Event::where('is_published', true)
                    ->where(fn($q) => $q->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('venue', 'like', $like)
                        ->orWhere('organizer', 'like', $like))
                    ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", [$like])
                    ->orderBy('starts_at')
                    ->paginate(12)
                    ->withQueryString(),

                'users' => User::where(fn($q) => $q->where('name', 'like', $like)
                    ->orWhere('username', 'like', $like)
                    ->orWhere('bio', 'like', $like))
                    ->orderByRaw("CASE WHEN name LIKE ? OR username LIKE ? THEN 0 ELSE 1 END", [$like, $like])
                    ->latest()
                    ->paginate(12)
                    ->withQueryString(),

                'questions' => Question::with(['user', 'category'])
                    ->where(fn($q) => $q->where('title', 'like', $like)
                        ->orWhere('content', 'like', $like))
                    ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", [$like])
                    ->latest()
                    ->paginate(12)
                    ->withQueryString(),

                'recipes' => Recipe::with('author')
                    ->where('is_published', true)
                    ->where(fn($q) => $q->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('cuisine_type', 'like', $like)
                        ->orWhere('meal_type', 'like', $like))
                    ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", [$like])
                    ->latest()
                    ->paginate(12)
                    ->withQueryString(),

                default => $this->allResults($query, $like),
            };
        }

        return view('search', compact('query', 'type', 'results', 'counts', 'trending', 'recent', 'suggested'));
    }

    public function suggest(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $like = "%{$query}%";
        $results = [];

        $posts = Post::published()
            ->where('title', 'like', $like)
            ->orderByDesc('view_count')
            ->limit(4)
            ->get(['id', 'title', 'slug', 'thumbnail_url']);

        foreach ($posts as $p) {
            $results[] = ['type' => 'post', 'label' => $p->title, 'url' => '/posts/' . $p->slug, 'icon' => '📰'];
        }

        $pages = SocialPage::where('status', 'active')
            ->where('name', 'like', $like)
            ->limit(2)
            ->get(['id', 'name', 'slug']);

        foreach ($pages as $p) {
            $results[] = ['type' => 'page', 'label' => $p->name, 'url' => '/pages/' . $p->slug, 'icon' => '📄'];
        }

        $users = User::where('name', 'like', $like)
            ->orWhere('username', 'like', $like)
            ->limit(2)
            ->get(['id', 'name', 'username']);

        foreach ($users as $u) {
            $results[] = ['type' => 'user', 'label' => $u->name . ' @' . $u->username, 'url' => '/profile/' . $u->username, 'icon' => '👤'];
        }

        $events = Event::where('is_published', true)
            ->where('title', 'like', $like)
            ->limit(2)
            ->get(['id', 'title', 'slug', 'uuid']);

        foreach ($events as $e) {
            $results[] = ['type' => 'event', 'label' => $e->title, 'url' => '/events/' . ($e->slug ?? $e->uuid), 'icon' => '📅'];
        }

        return response()->json(array_slice($results, 0, 8));
    }

    private function allResults(string $query, string $like): array
    {
        return [
            'posts' => Post::with(['author', 'category'])
                ->published()
                ->where(fn($q) => $q->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like))
                ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", [$like])
                ->orderByDesc('published_at')
                ->limit(4)
                ->get(),

            'pages' => SocialPage::where('status', 'active')
                ->where(fn($q) => $q->where('name', 'like', $like)
                    ->orWhere('page_type', 'like', $like)
                    ->orWhere('bio', 'like', $like))
                ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", [$like])
                ->orderByDesc('followers_count')
                ->limit(4)
                ->get(),

            'events' => Event::where('is_published', true)
                ->where(fn($q) => $q->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('venue', 'like', $like))
                ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", [$like])
                ->orderBy('starts_at')
                ->limit(4)
                ->get(),

            'users' => User::where(fn($q) => $q->where('name', 'like', $like)
                ->orWhere('username', 'like', $like))
                ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", [$like])
                ->limit(4)
                ->get(),

            'questions' => Question::with('user')
                ->where(fn($q) => $q->where('title', 'like', $like)
                    ->orWhere('content', 'like', $like))
                ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", [$like])
                ->latest()
                ->limit(4)
                ->get(),

            'recipes' => Recipe::with('author')
                ->where('is_published', true)
                ->where(fn($q) => $q->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('cuisine_type', 'like', $like))
                ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", [$like])
                ->limit(4)
                ->get(),
        ];
    }
}
