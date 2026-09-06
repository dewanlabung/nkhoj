<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\AI\OrchestratorAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function index(Request $request, OrchestratorAgent $orchestrator)
    {
        $query    = trim($request->get('q', ''));
        $enhanced = null;
        $posts    = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);

        if ($query) {
            // AI-enhance the query (cached 10 min)
            try {
                $enhanced = Cache::remember('search_' . md5($query), 600, fn() =>
                    $orchestrator->handle('search_enhance', ['query' => $query])
                );
            } catch (\Throwable) {}

            $posts = Post::with(['author', 'category'])
                ->published()
                ->where(fn($q) =>
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('excerpt', 'like', "%{$query}%")
                )
                ->latest('published_at')
                ->paginate(10)
                ->withQueryString();
        }

        return view('search', compact('query', 'posts', 'enhanced'));
    }
}
