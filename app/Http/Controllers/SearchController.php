<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Post;
use App\Models\Question;
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
        ];

        $trending = DB::table('search_logs')
            ->select('query', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('query')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('query');

        if ($query !== '') {
            $like = "%{$query}%";

            $counts['posts'] = Post::published()
                ->where(fn($q) => $q->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('body', 'like', $like))
                ->count();

            $counts['pages'] = SocialPage::where('status', 'active')
                ->where(fn($q) => $q->where('name', 'like', $like)
                    ->orWhere('category', 'like', $like)
                    ->orWhere('description', 'like', $like))
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

            $counts['all'] = array_sum(array_values($counts));

            // Log the search query
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
                    ->orderByDesc('published_at')
                    ->paginate(12)
                    ->withQueryString(),

                'pages' => SocialPage::where('status', 'active')
                    ->where(fn($q) => $q->where('name', 'like', $like)
                        ->orWhere('category', 'like', $like)
                        ->orWhere('description', 'like', $like))
                    ->orderByDesc('followers_count')
                    ->paginate(12)
                    ->withQueryString(),

                'events' => Event::where('is_published', true)
                    ->where(fn($q) => $q->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('venue', 'like', $like)
                        ->orWhere('organizer', 'like', $like))
                    ->orderBy('starts_at')
                    ->paginate(12)
                    ->withQueryString(),

                'users' => User::where(fn($q) => $q->where('name', 'like', $like)
                    ->orWhere('username', 'like', $like)
                    ->orWhere('bio', 'like', $like))
                    ->latest()
                    ->paginate(12)
                    ->withQueryString(),

                'questions' => Question::with(['user', 'category'])
                    ->where(fn($q) => $q->where('title', 'like', $like)
                        ->orWhere('content', 'like', $like))
                    ->latest()
                    ->paginate(12)
                    ->withQueryString(),

                default => $this->allResults($query, $like),
            };
        }

        return view('search', compact('query', 'type', 'results', 'counts', 'trending'));
    }

    private function allResults(string $query, string $like): array
    {
        return [
            'posts' => Post::with(['author', 'category'])
                ->published()
                ->where(fn($q) => $q->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like))
                ->orderByDesc('published_at')
                ->limit(4)
                ->get(),

            'pages' => SocialPage::where('status', 'active')
                ->where(fn($q) => $q->where('name', 'like', $like)
                    ->orWhere('category', 'like', $like)
                    ->orWhere('description', 'like', $like))
                ->orderByDesc('followers_count')
                ->limit(4)
                ->get(),

            'events' => Event::where('is_published', true)
                ->where(fn($q) => $q->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('venue', 'like', $like))
                ->orderBy('starts_at')
                ->limit(4)
                ->get(),

            'users' => User::where(fn($q) => $q->where('name', 'like', $like)
                ->orWhere('username', 'like', $like))
                ->limit(4)
                ->get(),

            'questions' => Question::with('user')
                ->where(fn($q) => $q->where('title', 'like', $like)
                    ->orWhere('content', 'like', $like))
                ->latest()
                ->limit(4)
                ->get(),
        ];
    }
}
