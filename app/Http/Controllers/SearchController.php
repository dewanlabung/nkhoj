<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Question;
use App\Models\User;
use App\Services\AI\OrchestratorAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function index(Request $request, OrchestratorAgent $orchestrator)
    {
        $query    = trim($request->get('q', ''));
        $type     = $request->get('type', 'posts');
        $enhanced = null;

        $results  = null;
        $counts   = ['posts' => 0, 'questions' => 0, 'users' => 0];

        if ($query) {
            // AI-enhance the query (cached 10 min)
            try {
                $enhanced = Cache::remember('search_' . md5($query), 600, fn() =>
                    $orchestrator->handle('search_enhance', ['query' => $query])
                );
            } catch (\Throwable) {}

            // Count totals for all types (for tab badges)
            // Use FULLTEXT if query has 3+ chars (MySQL requirement), fall back to LIKE
            $useFulltext = mb_strlen($query) >= 3;
            $counts['posts'] = Post::published()
                ->where(fn($q) => $useFulltext
                    ? $q->whereRaw('MATCH(title, excerpt) AGAINST(? IN BOOLEAN MODE)', ["*{$query}*"])
                        ->orWhere('title', 'like', "%{$query}%")
                    : $q->where('title', 'like', "%{$query}%")
                         ->orWhere('excerpt', 'like', "%{$query}%")
                )->count();

            $counts['questions'] = Question::where(fn($q) =>
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
            )->count();

            $counts['users'] = User::where(fn($q) =>
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('username', 'like', "%{$query}%")
                  ->orWhere('bio', 'like', "%{$query}%")
            )->count();

            // Paginated results for selected type
            if ($type === 'questions') {
                $results = Question::with(['user', 'category'])
                    ->where(fn($q) =>
                        $q->where('title', 'like', "%{$query}%")
                          ->orWhere('content', 'like', "%{$query}%")
                    )
                    ->latest()
                    ->paginate(10)
                    ->withQueryString();
            } elseif ($type === 'users') {
                $results = User::where(fn($q) =>
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('username', 'like', "%{$query}%")
                      ->orWhere('bio', 'like', "%{$query}%")
                )
                ->latest()
                ->paginate(10)
                ->withQueryString();
            } else {
                // Default: posts (all formats — articles, videos, galleries, polls, events)
                $type = 'posts';
                $results = Post::with(['author', 'category', 'tags'])
                    ->published()
                    ->when($useFulltext, fn($q) =>
                        $q->whereRaw('MATCH(title, excerpt) AGAINST(? IN BOOLEAN MODE)', ["*{$query}*"])
                          ->orWhere('title', 'like', "%{$query}%")
                    , fn($q) =>
                        $q->where('title', 'like', "%{$query}%")
                          ->orWhere('excerpt', 'like', "%{$query}%")
                    )
                    ->orderByRaw($useFulltext
                        ? 'MATCH(title, excerpt) AGAINST(? IN BOOLEAN MODE) DESC'
                        : 'published_at DESC',
                        $useFulltext ? ["*{$query}*"] : []
                    )
                    ->paginate(10)
                    ->withQueryString();
            }
        }

        return view('search', compact('query', 'type', 'results', 'counts', 'enhanced'));
    }
}
