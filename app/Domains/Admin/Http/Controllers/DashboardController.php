<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Models\Blog\Comment;
use App\Models\Blog\Category;
use App\Models\Core\ContactMessage;
use App\Models\Core\NewsletterSubscriber;
use App\Models\Blog\Post;
use App\Models\Blog\Tag;
use App\Models\UserEngagement\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseAdminController
{
    public function index()
    {
        $this->requireAdmin();

        $chartLabels = [];
        $chartData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('d M');
            $chartData[] = Post::whereDate('created_at', $date->toDateString())
                ->where('status', 'published')->sum('view_count') ?? 0;
        }

        return view('admin.index', [
            'stats' => [
                'users'            => User::count(),
                'posts'            => Post::count(),
                'published'        => Post::where('status', 'published')->count(),
                'comments'         => Comment::count(),
                'views'            => Post::sum('view_count'),
                'tags'             => Tag::count(),
                'pending_posts'    => Post::where('status', 'draft')->count(),
                'scheduled_posts'  => Post::where('status', 'scheduled')->count(),
                'contacts'         => ContactMessage::whereNull('read_at')->count(),
                'subscribers'      => NewsletterSubscriber::where('is_active', true)->count(),
                'pending_comments' => Comment::where('is_approved', false)->count(),
            ],
            'recentPosts'    => Post::with('author')->latest()->limit(15)->get(),
            'recentComments' => Comment::with(['post', 'author'])->latest()->limit(6)->get(),
            'recentUsers'    => User::latest()->limit(8)->get(),
            'chartLabels'    => $chartLabels,
            'chartData'      => $chartData,
        ]);
    }

    public function searchAnalytics()
    {
        $this->requireAdmin();

        $topQueries = DB::table('search_logs')
            ->select('query', DB::raw('COUNT(*) as total'), DB::raw('AVG(results_count) as avg_results'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('query')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $zeroResults = DB::table('search_logs')
            ->select('query', DB::raw('COUNT(*) as total'))
            ->where('results_count', 0)
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('query')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $dailyVolume = DB::table('search_logs')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalSearches   = DB::table('search_logs')->where('created_at', '>=', now()->subDays(30))->count();
        $uniqueSearchers = DB::table('search_logs')->where('created_at', '>=', now()->subDays(30))->distinct('user_id')->count('user_id');

        return view('admin.search-analytics', compact('topQueries', 'zeroResults', 'dailyVolume', 'totalSearches', 'uniqueSearchers'));
    }

    public function analyticsReport(Request $request)
    {
        $this->requireAdmin();

        $days   = (int) $request->input('days', 30);
        $days   = in_array($days, [7, 14, 30, 90]) ? $days : 30;
        $labels = [];
        $views  = [];
        $users  = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date     = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d M');
            $views[]  = (int) DB::table('posts')
                ->whereDate('published_at', $date)
                ->where('status', 'published')
                ->sum('view_count');
            $users[] = User::whereDate('created_at', $date)->count();
        }

        return response()->json(compact('labels', 'views', 'users'));
    }

    public function analytics()
    {
        $this->requireAdmin();

        $days = 30;
        $chartLabels = [];
        $chartViews  = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date          = now()->subDays($i)->toDateString();
            $chartLabels[] = now()->subDays($i)->format('d M');
            $chartViews[]  = (int) DB::table('posts')
                ->whereDate('published_at', $date)
                ->where('status', 'published')
                ->sum('view_count');
        }
        $chartUsers = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $chartUsers[] = User::whereDate('created_at', now()->subDays($i)->toDateString())->count();
        }

        $topPosts = Post::with(['author', 'category'])
            ->where('status', 'published')
            ->orderByDesc('view_count')
            ->limit(10)->get();

        $topUsers = User::withCount(['posts', 'comments'])
            ->orderByDesc('posts_count')
            ->limit(8)->get();

        $topCategories = Category::withCount(['posts' => fn($q) => $q->where('status', 'published')])
            ->orderByDesc('posts_count')
            ->limit(10)->get();

        $topTags = Tag::withCount(['posts' => fn($q) => $q->where('status', 'published')])
            ->having('posts_count', '>', 0)
            ->orderByDesc('posts_count')
            ->limit(15)->get();

        $statusBreakdown = Post::selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status')->toArray();

        $postFormatBreakdown = Post::selectRaw('COALESCE(post_format,"article") as fmt, count(*) as total')
            ->groupBy('fmt')->pluck('total', 'fmt')->toArray();

        $totalViews    = (int) Post::sum('view_count');
        $totalPosts    = Post::where('status', 'published')->count();
        $avgViews      = $totalPosts > 0 ? round($totalViews / $totalPosts) : 0;
        $totalUsers    = User::count();
        $newUsersMonth = User::where('created_at', '>=', now()->startOfMonth())->count();

        return view('admin.analytics', compact(
            'chartLabels', 'chartViews', 'chartUsers',
            'topPosts', 'topUsers', 'topCategories', 'topTags',
            'statusBreakdown', 'postFormatBreakdown',
            'totalViews', 'totalPosts', 'avgViews', 'totalUsers', 'newUsersMonth'
        ));
    }
}
