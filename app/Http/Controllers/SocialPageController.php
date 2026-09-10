<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\PagePost;
use App\Models\PagePostLike;
use App\Models\PageReview;
use App\Models\PageVerificationRequest;
use App\Models\PageViewLog;
use App\Models\SocialPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SocialPageController extends Controller
{
    // GET /pages — discover
    public function index(Request $request)
    {
        $tab      = $request->query('tab', 'discover');
        $search   = $request->query('q');
        $category = $request->query('category', 'All');

        $myPages = auth()->check()
            ? SocialPage::where('user_id', auth()->id())->latest()->get()
            : collect();

        $query = SocialPage::where('is_active', true);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($category && $category !== 'All') {
            $query->whereJsonContains('categories', $category);
        }

        if ($tab === 'liked' && auth()->check()) {
            $query->whereHas('followers', fn($q) => $q->where('user_id', auth()->id()));
        } elseif ($tab === 'mine' && auth()->check()) {
            $query->where('user_id', auth()->id());
        } elseif ($tab === 'top_rated') {
            $query->orderByDesc('rating_avg');
        } elseif ($tab === 'nearby' && $request->filled('near')) {
            [$lat, $lng] = explode(',', $request->query('near'));
            $lat = (float) $lat; $lng = (float) $lng;
            $query->whereNotNull('lat')->whereNotNull('lng')
                  ->orderByRaw('(POW(lat - ?, 2) + POW(lng - ?, 2))', [$lat, $lng]);
        }

        if (!in_array($tab, ['top_rated', 'nearby'])) {
            $query->orderByDesc('followers_count');
        }

        $pages = $query->paginate(24)->withQueryString();

        return view('social-pages.discover', compact('pages', 'myPages'));
    }

    // GET /pages/start — intro splash
    public function intro()
    {
        return view('social-pages.intro');
    }

    // GET /pages/map — full map view
    public function mapView()
    {
        return view('social-pages.map');
    }

    // GET /api/pages/map-pins — JSON for Leaflet markers
    public function mapPins()
    {
        $pins = SocialPage::where('is_active', true)
            ->whereNotNull('lat')->whereNotNull('lng')
            ->select('id', 'name', 'slug', 'categories', 'lat', 'lng', 'avatar_url', 'followers_count', 'rating_avg', 'is_verified')
            ->get()
            ->map(fn($p) => [
                'id'              => $p->id,
                'name'            => $p->name,
                'slug'            => $p->slug,
                'category'        => $p->first_category,
                'lat'             => $p->lat,
                'lng'             => $p->lng,
                'avatar'          => $p->avatar,
                'followers_count' => $p->followers_count,
                'rating_avg'      => $p->rating_avg,
                'is_verified'     => $p->is_verified,
            ]);

        return response()->json($pins);
    }

    // GET /pages/create — multi-step wizard
    public function create()
    {
        return view('social-pages.create');
    }

    // POST /pages — store
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:150',
            'page_type'    => 'required|in:creator,business',
            'categories'   => 'nullable|array|max:3',
            'categories.*' => 'nullable|string|max:100',
            'bio'          => 'nullable|string|max:500',
            'website'      => 'nullable|url|max:255',
            'location'     => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:50',
        ]);

        $slug = Str::slug($data['name']);
        $base = $slug ?: 'page';
        $i = 1;
        while (SocialPage::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}"; $i++;
        }

        $page = SocialPage::create([
            'uuid'       => Str::uuid(),
            'user_id'    => auth()->id(),
            'name'       => $data['name'],
            'slug'       => $slug,
            'page_type'  => $data['page_type'],
            'categories' => array_filter($data['categories'] ?? []),
            'bio'        => $data['bio'] ?? null,
            'website'    => $data['website'] ?? null,
            'location'   => $data['location'] ?? null,
            'phone'      => $data['phone'] ?? null,
        ]);

        return redirect("/pages/{$page->slug}/dashboard")
            ->with('success', 'Your page has been created!');
    }

    // GET /pages/{slug} — public profile
    public function show(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // record daily view (MariaDB-compatible upsert)
        DB::statement(
            'INSERT INTO page_view_logs (social_page_id, date, views) VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE views = views + 1',
            [$page->id, today()->toDateString()]
        );
        $page->increment('views_count');

        $isOwner     = $page->isOwnedBy(auth()->user());
        $isFollowing = $page->isFollowedBy(auth()->user());
        $isSaved     = auth()->check()
            ? Bookmark::where('user_id', auth()->id())
                ->where('bookmarkable_type', SocialPage::class)
                ->where('bookmarkable_id', $page->id)
                ->exists()
            : false;

        $userReview = auth()->check()
            ? $page->reviews()->where('user_id', auth()->id())->first()
            : null;

        $posts   = PagePost::where('social_page_id', $page->id)->latest()->paginate(12);
        $reviews = $page->reviews()->with('user')->latest()->paginate(10);
        $events  = PagePost::where('social_page_id', $page->id)->where('type', 'event')
                        ->where('event_start', '>=', now())->orderBy('event_start')->limit(5)->get();

        $isOpen = $page->isOpenNow();

        return view('social-pages.show', compact(
            'page', 'isOwner', 'isFollowing', 'isSaved',
            'posts', 'reviews', 'userReview', 'events', 'isOpen'
        ));
    }

    // POST /pages/{slug}/follow — toggle follow (AJAX)
    public function follow(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        $user = auth()->user();

        if ($page->followers()->where('user_id', $user->id)->exists()) {
            $page->followers()->detach($user->id);
            $page->decrement('followers_count');
            $following = false;
        } else {
            $page->followers()->attach($user->id);
            $page->increment('followers_count');
            $following = true;
        }

        return response()->json([
            'following'       => $following,
            'followers_count' => $page->fresh()->followers_count,
        ]);
    }

    // POST /pages/{slug}/posts — store page post
    public function storePost(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();

        $data = $request->validate([
            'type'             => 'required|in:text,photo,video,event',
            'body'             => 'nullable|string|max:5000',
            'image'            => 'nullable|image|max:4096',
            'video_url'        => 'nullable|url|max:500',
            'event_title'      => 'nullable|string|max:200',
            'event_start'      => 'nullable|date',
            'event_end'        => 'nullable|date|after:event_start',
            'event_venue'      => 'nullable|string|max:255',
            'event_ticket_url' => 'nullable|url|max:500',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $filename = time() . '_post.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pages'), $filename);
            $imageUrl = '/uploads/pages/' . $filename;
        }

        PagePost::create([
            'social_page_id'   => $page->id,
            'user_id'          => auth()->id(),
            'type'             => $data['type'],
            'body'             => $data['body'] ?? null,
            'image_url'        => $imageUrl,
            'video_url'        => $data['video_url'] ?? null,
            'event_title'      => $data['event_title'] ?? null,
            'event_start'      => $data['event_start'] ?? null,
            'event_end'        => $data['event_end'] ?? null,
            'event_venue'      => $data['event_venue'] ?? null,
            'event_ticket_url' => $data['event_ticket_url'] ?? null,
        ]);

        return back()->with('success', 'Post published!');
    }

    // DELETE /pages/{slug}/posts/{postId}
    public function deletePost(string $slug, int $postId)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();
        $post = PagePost::where('id', $postId)->where('social_page_id', $page->id)->firstOrFail();
        $post->delete();

        return back()->with('success', 'Post deleted.');
    }

    // POST /pages/{slug}/posts/{postId}/like — toggle like (AJAX)
    public function likePost(string $slug, int $postId)
    {
        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $post = PagePost::where('id', $postId)->where('social_page_id', $page->id)->firstOrFail();

        $existing = PagePostLike::where('page_post_id', $postId)->where('user_id', auth()->id())->first();
        if ($existing) {
            $existing->delete();
            $post->decrement('likes_count');
            $liked = false;
        } else {
            PagePostLike::create(['page_post_id' => $postId, 'user_id' => auth()->id()]);
            $post->increment('likes_count');
            $liked = true;
        }

        return response()->json(['liked' => $liked, 'likes_count' => $post->fresh()->likes_count]);
    }

    // POST /pages/{slug}/reviews — store review
    public function storeReview(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'body'   => 'nullable|string|max:1000',
        ]);

        PageReview::updateOrCreate(
            ['social_page_id' => $page->id, 'user_id' => auth()->id()],
            ['rating' => $data['rating'], 'body' => $data['body'] ?? null]
        );

        $avg   = $page->reviews()->avg('rating');
        $count = $page->reviews()->count();
        $page->update(['rating_avg' => round($avg, 2), 'reviews_count' => $count]);

        return back()->with('success', 'Review submitted!');
    }

    // DELETE /pages/{slug}/reviews — delete own review
    public function deleteReview(string $slug)
    {
        $page   = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        PageReview::where('social_page_id', $page->id)->where('user_id', auth()->id())->delete();

        $avg   = $page->reviews()->avg('rating') ?? 0;
        $count = $page->reviews()->count();
        $page->update(['rating_avg' => round($avg, 2), 'reviews_count' => $count]);

        return back()->with('success', 'Review removed.');
    }

    // POST /pages/{slug}/request-verification
    public function requestVerification(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();

        if ($page->is_verified) {
            return back()->with('info', 'This page is already verified.');
        }

        $pending = PageVerificationRequest::where('social_page_id', $page->id)
                        ->where('status', 'pending')->exists();
        if ($pending) {
            return back()->with('info', 'A verification request is already pending.');
        }

        $data = $request->validate(['reason' => 'nullable|string|max:500']);
        PageVerificationRequest::create([
            'social_page_id' => $page->id,
            'reason'         => $data['reason'] ?? null,
        ]);

        return back()->with('success', 'Verification request submitted! We will review it shortly.');
    }

    // GET /pages/{slug}/dashboard — professional dashboard (owner only)
    public function dashboard(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();

        // 30-day view chart data
        $viewData = PageViewLog::where('social_page_id', $page->id)
            ->where('date', '>=', now()->subDays(29)->toDateString())
            ->orderBy('date')
            ->pluck('views', 'date');

        $chartLabels = [];
        $chartValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $chartLabels[] = now()->subDays($i)->format('M j');
            $chartValues[] = $viewData[$d] ?? 0;
        }

        $hasPendingVerification = PageVerificationRequest::where('social_page_id', $page->id)
                                        ->where('status', 'pending')->exists();

        return view('social-pages.dashboard', compact('page', 'chartLabels', 'chartValues', 'hasPendingVerification'));
    }

    // GET /pages/{slug}/settings — settings (owner only)
    public function settings(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();
        return view('social-pages.settings', compact('page'));
    }

    // PUT /pages/{slug}/settings — update settings
    public function updateSettings(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();

        $data = $request->validate([
            'name'         => 'required|string|max:150',
            'bio'          => 'nullable|string|max:500',
            'categories'   => 'nullable|array|max:3',
            'categories.*' => 'nullable|string|max:100',
            'website'      => 'nullable|url|max:300',
            'email'        => 'nullable|email|max:150',
            'phone'        => 'nullable|string|max:50',
            'location'     => 'nullable|string|max:200',
            'lat'          => 'nullable|numeric|between:-90,90',
            'lng'          => 'nullable|numeric|between:-180,180',
            'avatar'       => 'nullable|image|max:2048',
            'cover'        => 'nullable|image|max:4096',
            'business_hours' => 'nullable|array',
        ]);

        if ($request->hasFile('avatar')) {
            $file     = $request->file('avatar');
            $filename = time() . '_avatar.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pages'), $filename);
            $data['avatar_url'] = '/uploads/pages/' . $filename;
        }

        if ($request->hasFile('cover')) {
            $file     = $request->file('cover');
            $filename = time() . '_cover.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pages'), $filename);
            $data['cover_url'] = '/uploads/pages/' . $filename;
        }

        // build business_hours from individual day fields
        $days  = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $hours = [];
        foreach ($days as $day) {
            $hours[$day] = [
                'open'   => $request->input("hours_{$day}_open", '09:00'),
                'close'  => $request->input("hours_{$day}_close", '17:00'),
                'closed' => $request->boolean("hours_{$day}_closed"),
            ];
        }

        $page->update(array_filter([
            'name'           => $data['name'],
            'bio'            => $data['bio'] ?? null,
            'categories'     => array_filter($data['categories'] ?? []),
            'website'        => $data['website'] ?? null,
            'email'          => $data['email'] ?? null,
            'phone'          => $data['phone'] ?? null,
            'location'       => $data['location'] ?? null,
            'lat'            => $data['lat'] ?? null,
            'lng'            => $data['lng'] ?? null,
            'business_hours' => $hours,
            'avatar_url'     => $data['avatar_url'] ?? $page->avatar_url,
            'cover_url'      => $data['cover_url'] ?? $page->cover_url,
        ], fn($v) => $v !== null));

        return back()->with('success', 'Page settings updated!');
    }

    // GET /dashboard/pages — my pages list
    public function myPages()
    {
        $pages = SocialPage::where('user_id', auth()->id())->latest()->get();
        return view('social-pages.my-pages', compact('pages'));
    }
}
