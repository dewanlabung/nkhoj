<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use Illuminate\Validation\Rule;
use App\Models\PageActivityLog;
use App\Models\PageAdmin;
use App\Models\PageBlock;
use App\Models\PageCategory;
use App\Models\PageNotificationPref;
use App\Models\PagePollOption;
use App\Models\PagePollVote;
use App\Models\PagePost;
use App\Models\PagePostComment;
use App\Models\PagePostLike;
use App\Models\PageProduct;
use App\Models\PageQna;
use App\Models\PageReport;
use App\Models\PageReview;
use App\Models\PageStory;
use App\Models\PageVerificationRequest;
use App\Models\PageFaq;
use App\Models\PageMilestone;
use App\Models\PageViewLog;
use App\Models\SocialPage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ZipArchive;

class SocialPageController extends Controller
{
    // ─── Discover / Index ──────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tab      = $request->query('tab', 'discover');
        $search   = $request->query('q');
        $category = $request->query('category');

        $myPages = auth()->check()
            ? SocialPage::where('user_id', auth()->id())->latest()->get()
            : collect();

        $query = SocialPage::where('is_active', true)->where('status', 'active');

        // Only show pages that opted into recommendations on the discover tab
        if (!$search && !in_array($tab, ['liked', 'mine', 'nearby'])
            && \Illuminate\Support\Facades\Schema::hasColumn('social_pages', 'allow_recommendations')) {
            $query->where(function($q) {
                $q->whereNull('allow_recommendations')->orWhere('allow_recommendations', true);
            });
        }

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
            $query->where('reviews_count', '>', 0)->orderByDesc('rating_avg');
        } elseif ($tab === 'nearby' && $request->filled('near')) {
            [$lat, $lng] = explode(',', $request->query('near'));
            $lat = (float) $lat; $lng = (float) $lng;
            $query->whereNotNull('lat')->whereNotNull('lng')
                  ->orderByRaw('(POW(lat - ?, 2) + POW(lng - ?, 2))', [$lat, $lng]);
        }

        if (!in_array($tab, ['top_rated', 'nearby'])) {
            $query->orderByDesc('followers_count');
        }

        $pages      = $query->paginate(24)->withQueryString();
        $categories = PageCategory::active()->pluck('name')->toArray() ?: SocialPage::CATEGORIES;

        return view('social-pages.discover', compact('pages', 'myPages', 'categories'));
    }

    // ─── Intro / Map ───────────────────────────────────────────────────────────

    public function intro()
    {
        return view('social-pages.intro');
    }

    public function mapView()
    {
        return view('social-pages.map');
    }

    public function mapPins()
    {
        $pins = SocialPage::where('is_active', true)->where('status', 'active')
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

    // ─── Create ────────────────────────────────────────────────────────────────

    public function create()
    {
        $categories = PageCategory::active()->pluck('name')->toArray() ?: SocialPage::CATEGORIES;
        return view('social-pages.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if ($request->expectsJson()) {
            $data = $request->validate([
                'name'         => 'required|string|max:150',
                'username'     => ['nullable','string','max:60','regex:/^[a-z0-9._-]+$/i',
                                   'unique:social_pages,username'],
                'page_type'    => 'nullable|in:creator,business,community,nonprofit,other',
                'categories'   => 'nullable|array|max:3',
                'categories.*' => 'nullable|string|max:100',
                'bio'          => 'nullable|string|max:500',
                'website'      => 'nullable|url|max:255',
                'location'     => 'nullable|string|max:255',
                'phone'        => 'nullable|string|max:50',
                'social_links' => 'nullable|array',
            ]);
        } else {
            $data = $request->validate([
                'name'         => 'required|string|max:150',
                'username'     => ['nullable','string','max:60','regex:/^[a-z0-9._-]+$/i',
                                   'unique:social_pages,username'],
                'page_type'    => 'nullable|in:creator,business,community,nonprofit,other',
                'categories'   => 'nullable|array|max:3',
                'categories.*' => 'nullable|string|max:100',
                'bio'          => 'nullable|string|max:500',
                'website'      => 'nullable|url|max:255',
                'location'     => 'nullable|string|max:255',
                'phone'        => 'nullable|string|max:50',
            ]);
        }

        $slug = Str::slug($data['name']);
        $base = $slug ?: 'page';
        $i = 1;
        while (SocialPage::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}"; $i++;
        }

        $username = null;
        if (!empty($data['username'])) {
            $username = strtolower(trim($data['username']));
        }

        $createData = [
            'uuid'         => Str::uuid(),
            'user_id'      => auth()->id(),
            'name'         => $data['name'],
            'slug'         => $slug,
            'username'     => $username,
            'categories'   => array_values(array_filter($data['categories'] ?? [])),
            'bio'          => $data['bio'] ?? null,
            'website'      => $data['website'] ?? null,
            'location'     => $data['location'] ?? null,
            'phone'        => $data['phone'] ?? null,
            'social_links' => $data['social_links'] ?? null,
            'status'       => 'active',
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('social_pages', 'page_type')) {
            $createData['page_type'] = $data['page_type'] ?? 'other';
        }
        $page = SocialPage::create($createData);

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'redirect' => "/pages/{$page->slug}/dashboard",
            ]);
        }

        return redirect("/pages/{$page->slug}/dashboard")
            ->with('success', 'Your page has been created!');
    }

    public function checkUsername(Request $request): \Illuminate\Http\JsonResponse
    {
        $username = strtolower(trim($request->input('username', '')));
        if (!$username || !preg_match('/^[a-z0-9._-]+$/', $username)) {
            return response()->json(['available' => false, 'message' => 'Only letters, numbers, dots, dashes, underscores.']);
        }
        if (strlen($username) < 3) {
            return response()->json(['available' => false, 'message' => 'At least 3 characters.']);
        }
        $taken = SocialPage::where('username', $username)->exists();
        return response()->json([
            'available' => !$taken,
            'message'   => $taken ? 'Already taken.' : 'Available!',
        ]);
    }

    // ─── Public Profile ────────────────────────────────────────────────────────

    public function show(string $slug)
    {
        $page = SocialPage::where('slug', $slug)
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('status', 'active')->orWhereNull('status'))
            ->firstOrFail();

        // record daily view
        DB::statement(
            'INSERT INTO page_view_logs (social_page_id, date, views) VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE views = views + 1',
            [$page->id, today()->toDateString()]
        );
        $page->increment('views_count');

        $user        = auth()->user();
        $userRole    = $page->roleFor($user);
        $isOwner     = $userRole === 'owner';
        $isManager   = in_array($userRole, ['owner', 'admin', 'moderator', 'editor']);
        $isFollowing = $page->isFollowedBy($user);

        $isSaved = $user
            ? Bookmark::where('user_id', $user->id)
                ->where('bookmarkable_type', SocialPage::class)
                ->where('bookmarkable_id', $page->id)
                ->exists()
            : false;

        $userReview  = $user ? $page->reviews()->where('user_id', $user->id)->first() : null;
        $pinnedPost  = $page->pinned_post_id ? $page->pinnedPost()->with('author')->first() : null;
        $posts       = PagePost::where('social_page_id', $page->id)
                            ->where('id', '!=', $page->pinned_post_id ?? 0)
                            ->with(['pollOptions', 'pollVotes'])
                            ->latest()->paginate(12);
        $reviews     = $page->reviews()->with('user')->latest()->paginate(10);
        $events      = PagePost::where('social_page_id', $page->id)->where('type', 'event')
                            ->where('event_start', '>=', now())->orderBy('event_start')->limit(5)->get();
        $photos      = PagePost::where('social_page_id', $page->id)->where('type', 'photo')
                            ->whereNotNull('image_url')->latest()->paginate(24);
        $qnaItems    = PageQna::where('social_page_id', $page->id)->where('is_visible', true)
                            ->with(['asker', 'answerer'])->latest()->paginate(20);
        $products    = PageProduct::where('social_page_id', $page->id)->where('is_available', true)
                            ->orderBy('sort_order')->get();
        $isOpen      = $page->isOpenNow();
        $userVoteMap = [];
        if ($user) {
            $postIds = $posts->pluck('id');
            $votes   = PagePollVote::whereIn('page_post_id', $postIds)->where('user_id', $user->id)->get();
            $userVoteMap = $votes->keyBy('page_post_id')->map->page_poll_option_id->toArray();
        }

        // Active stories (24h)
        $activeStories = PageStory::where('social_page_id', $page->id)->active()->latest()->get();

        // Notification prefs for current follower
        $notifPrefs = ($user && $isFollowing)
            ? PageNotificationPref::where('user_id', $user->id)->where('social_page_id', $page->id)->first()
            : null;

        // Is user blocked?
        $isBlocked = $user ? PageBlock::where('social_page_id', $page->id)->where('blocked_user_id', $user->id)->exists() : false;

        return view('social-pages.show', compact(
            'page', 'isOwner', 'isManager', 'userRole', 'isFollowing', 'isSaved',
            'posts', 'reviews', 'userReview', 'events', 'isOpen', 'pinnedPost',
            'photos', 'qnaItems', 'products', 'userVoteMap',
            'activeStories', 'notifPrefs', 'isBlocked'
        ));
    }

    // ─── Follow ────────────────────────────────────────────────────────────────

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
            $page->refresh();
            PageMilestone::checkAndRecord($page);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'following'       => $following,
                'followers_count' => $page->fresh()->followers_count,
            ]);
        }

        return back();
    }

    // ─── Posts ─────────────────────────────────────────────────────────────────

    public function storePost(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);

        $data = $request->validate([
            'type'             => 'required|in:text,photo,video,event',
            'post_format'      => 'nullable|in:standard,article,poll,qna',
            'article_title'    => 'nullable|string|max:255',
            'body'             => 'nullable|string|max:5000',
            'image'            => 'nullable|image|max:4096',
            'video_url'        => 'nullable|url|max:500',
            'event_title'      => 'nullable|string|max:200',
            'event_start'      => 'nullable|date',
            'event_end'        => 'nullable|date|after:event_start',
            'event_venue'      => 'nullable|string|max:255',
            'event_ticket_url' => 'nullable|url|max:500',
            'poll_options'     => 'nullable|array|min:2|max:6',
            'poll_options.*'   => 'required|string|max:200',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $filename = time() . '_post.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pages'), $filename);
            $imageUrl = '/uploads/pages/' . $filename;
        }

        $postFormat = $data['post_format'] ?? 'standard';
        $type = $data['type'];
        if ($postFormat === 'article') $type = 'text';
        if ($postFormat === 'poll')    $type = 'text';
        if ($postFormat === 'qna')     $type = 'text';

        $post = PagePost::create([
            'social_page_id'   => $page->id,
            'user_id'          => auth()->id(),
            'type'             => $type,
            'post_format'      => $postFormat,
            'article_title'    => $data['article_title'] ?? null,
            'body'             => $data['body'] ?? null,
            'image_url'        => $imageUrl,
            'video_url'        => $data['video_url'] ?? null,
            'event_title'      => $data['event_title'] ?? null,
            'event_start'      => $data['event_start'] ?? null,
            'event_end'        => $data['event_end'] ?? null,
            'event_venue'      => $data['event_venue'] ?? null,
            'event_ticket_url' => $data['event_ticket_url'] ?? null,
        ]);

        if ($postFormat === 'poll' && !empty($data['poll_options'])) {
            foreach (array_values($data['poll_options']) as $i => $optText) {
                PagePollOption::create([
                    'page_post_id' => $post->id,
                    'text'         => $optText,
                    'sort_order'   => $i,
                ]);
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('social_pages', 'last_post_at')) {
            $page->update(['last_post_at' => now()]);
        }

        return back()->with('success', 'Post published!');
    }

    public function deletePost(string $slug, int $postId)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);
        $post = PagePost::where('id', $postId)->where('social_page_id', $page->id)->firstOrFail();

        // unpin if pinned
        if ($page->pinned_post_id === $postId) {
            $page->update(['pinned_post_id' => null]);
        }
        $post->delete();

        return back()->with('success', 'Post deleted.');
    }

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

    // POST /pages/{slug}/posts/{postId}/react
    public function reactPost(Request $request, string $slug, int $postId)
    {
        $valid = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];
        $reaction = $request->input('reaction', 'like');
        if (!in_array($reaction, $valid)) {
            $reaction = 'like';
        }

        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $post = PagePost::where('id', $postId)->where('social_page_id', $page->id)->firstOrFail();

        $existing = PagePostLike::where('page_post_id', $postId)->where('user_id', auth()->id())->first();

        if ($existing) {
            if ($existing->reaction === $reaction) {
                // Toggle off
                $existing->delete();
                $post->decrement('likes_count');
                return response()->json([
                    'reaction'    => null,
                    'likes_count' => $post->fresh()->likes_count,
                ]);
            }
            // Switch reaction
            $existing->update(['reaction' => $reaction]);
        } else {
            PagePostLike::create(['page_post_id' => $postId, 'user_id' => auth()->id(), 'reaction' => $reaction]);
            $post->increment('likes_count');
        }

        return response()->json([
            'reaction'    => $reaction,
            'likes_count' => $post->fresh()->likes_count,
        ]);
    }

    // GET /pages/{slug}/posts/{postId}/comments
    public function loadComments(string $slug, int $postId)
    {
        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $post = PagePost::where('id', $postId)->where('social_page_id', $page->id)->firstOrFail();

        $comments = $post->comments()->with('author')->latest()->limit(50)->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'body'       => $c->body,
                'time'       => $c->created_at->diffForHumans(),
                'author'     => $c->author->name,
                'avatar'     => $c->author->avatar ?? null,
                'user_id'    => $c->user_id,
            ]);

        return response()->json($comments);
    }

    // POST /pages/{slug}/posts/{postId}/comments
    public function storeComment(Request $request, string $slug, int $postId)
    {
        $request->validate(['body' => 'required|string|max:1000']);

        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $post = PagePost::where('id', $postId)->where('social_page_id', $page->id)->firstOrFail();

        $comment = \App\Models\PagePostComment::create([
            'page_post_id' => $post->id,
            'user_id'      => auth()->id(),
            'body'         => $request->input('body'),
        ]);
        $post->increment('comments_count');

        return response()->json([
            'id'      => $comment->id,
            'body'    => $comment->body,
            'time'    => $comment->created_at->diffForHumans(),
            'author'  => auth()->user()->name,
            'avatar'  => auth()->user()->avatar ?? null,
            'user_id' => auth()->id(),
            'comments_count' => $post->fresh()->comments_count,
        ]);
    }

    // DELETE /pages/{slug}/posts/{postId}/comments/{commentId}
    public function deleteComment(string $slug, int $postId, int $commentId)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        $post = PagePost::where('id', $postId)->where('social_page_id', $page->id)->firstOrFail();

        $comment = \App\Models\PagePostComment::where('id', $commentId)
            ->where('page_post_id', $postId)
            ->firstOrFail();

        // Allow: comment author, page manager
        abort_unless(
            $comment->user_id === auth()->id() || $page->isManagedBy(auth()->user()),
            403
        );

        $comment->delete();
        $post->decrement('comments_count');

        return response()->json(['deleted' => true, 'comments_count' => $post->fresh()->comments_count]);
    }

    // POST /pages/{slug}/posts/{postId}/pin
    public function pinPost(string $slug, int $postId)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isOwnedBy(auth()->user()), 403);
        PagePost::where('id', $postId)->where('social_page_id', $page->id)->firstOrFail();

        $page->update(['pinned_post_id' => $postId]);
        return back()->with('success', 'Post pinned.');
    }

    // DELETE /pages/{slug}/posts/{postId}/pin
    public function unpinPost(string $slug, int $postId)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isOwnedBy(auth()->user()), 403);

        $page->update(['pinned_post_id' => null]);
        return back()->with('success', 'Post unpinned.');
    }

    // ─── Reviews ───────────────────────────────────────────────────────────────

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

    public function deleteReview(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        PageReview::where('social_page_id', $page->id)->where('user_id', auth()->id())->delete();

        $avg   = $page->reviews()->avg('rating') ?? 0;
        $count = $page->reviews()->count();
        $page->update(['rating_avg' => round($avg, 2), 'reviews_count' => $count]);

        return back()->with('success', 'Review removed.');
    }

    // ─── Reports / Moderation ──────────────────────────────────────────────────

    // POST /pages/{slug}/report
    // POST /pages/{slug}/posts/{postId}/report
    // POST /pages/{slug}/reviews/{reviewId}/report
    public function report(Request $request, string $slug, ?string $type = null, ?int $entityId = null)
    {
        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $data = $request->validate([
            'reason'  => 'required|in:spam,inappropriate,harassment,fake,other',
            'details' => 'nullable|string|max:500',
        ]);

        if ($type === 'post' && $entityId) {
            $reportable = PagePost::where('id', $entityId)->where('social_page_id', $page->id)->firstOrFail();
        } elseif ($type === 'review' && $entityId) {
            $reportable = PageReview::where('id', $entityId)->where('social_page_id', $page->id)->firstOrFail();
        } else {
            $reportable = $page;
        }

        PageReport::updateOrCreate(
            [
                'reportable_type' => get_class($reportable),
                'reportable_id'   => $reportable->id,
                'user_id'         => auth()->id(),
            ],
            [
                'social_page_id' => $page->id,
                'reason'         => $data['reason'],
                'details'        => $data['details'] ?? null,
                'status'         => 'pending',
            ]
        );

        return back()->with('success', 'Report submitted. Thank you.');
    }

    // GET /pages/{slug}/moderation
    public function moderationQueue(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);

        $reports = PageReport::where('social_page_id', $page->id)
            ->with(['reporter', 'reportable'])
            ->latest()
            ->paginate(20);

        return view('social-pages.moderation', compact('page', 'reports'));
    }

    // POST /pages/{slug}/moderation/{reportId}
    public function moderationAction(Request $request, string $slug, int $reportId)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);

        $data   = $request->validate(['action' => 'required|in:dismiss,delete_content']);
        $report = PageReport::where('id', $reportId)->where('social_page_id', $page->id)->firstOrFail();

        if ($data['action'] === 'delete_content' && $report->reportable) {
            $report->reportable->delete();
        }

        $report->update([
            'status'      => 'reviewed',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Report resolved.');
    }

    // ─── Admin Management ──────────────────────────────────────────────────────

    // GET /pages/{slug}/admins
    public function manageAdmins(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isOwnedBy(auth()->user()), 403);

        $admins = PageAdmin::where('social_page_id', $page->id)
            ->with(['user', 'inviter'])
            ->latest()
            ->get();

        return view('social-pages.admins', compact('page', 'admins'));
    }

    // POST /pages/{slug}/admins/invite
    public function inviteAdmin(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isOwnedBy(auth()->user()), 403);

        $data = $request->validate([
            'username' => 'required|string|exists:users,username',
            'role'     => 'required|in:admin,moderator,editor',
        ]);

        $invitee = User::where('username', $data['username'])->firstOrFail();

        if ($page->isOwnedBy($invitee)) {
            return back()->withErrors(['username' => 'Cannot invite the page owner.']);
        }

        PageAdmin::updateOrCreate(
            ['social_page_id' => $page->id, 'user_id' => $invitee->id],
            ['invited_by' => auth()->id(), 'role' => $data['role'], 'accepted_at' => null]
        );

        return back()->with('success', "@{$invitee->username} has been invited as {$data['role']}.");
    }

    // POST /pages/{slug}/admins/accept
    public function acceptAdminInvite(string $slug)
    {
        $page   = SocialPage::where('slug', $slug)->firstOrFail();
        $invite = PageAdmin::where('social_page_id', $page->id)
                           ->where('user_id', auth()->id())
                           ->whereNull('accepted_at')
                           ->firstOrFail();

        $invite->update(['accepted_at' => now()]);

        return redirect("/pages/{$page->slug}")->with('success', "You are now an {$invite->role} of {$page->name}.");
    }

    // DELETE /pages/{slug}/admins/{userId}
    public function removeAdmin(string $slug, int $userId)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isOwnedBy(auth()->user()), 403);

        PageAdmin::where('social_page_id', $page->id)->where('user_id', $userId)->delete();

        return back()->with('success', 'Admin removed.');
    }

    // ─── Verification ──────────────────────────────────────────────────────────

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

    // ─── Page Status (Disable / Enable / Delete) ───────────────────────────────

    // POST /pages/{slug}/disable
    public function disable(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();
        $data = $request->validate(['reason' => 'nullable|string|max:500']);

        $page->update([
            'status'          => 'disabled',
            'disabled_reason' => $data['reason'] ?? null,
        ]);

        return redirect("/pages/{$page->slug}/dashboard")->with('success', 'Page has been disabled.');
    }

    // POST /pages/{slug}/enable
    public function enable(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();

        $page->update(['status' => 'active', 'disabled_reason' => null]);

        return redirect("/pages/{$page->slug}/dashboard")->with('success', 'Page is now active.');
    }

    // DELETE /pages/{slug}
    public function destroy(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();
        $page->delete();

        return redirect('/pages')->with('success', 'Page deleted permanently.');
    }

    // ─── Dashboard ─────────────────────────────────────────────────────────────

    public function dashboard(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);

        $isOwner = $page->isOwnedBy(auth()->user());
        $userRole = $page->roleFor(auth()->user());

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

        $pendingReports = PageReport::where('social_page_id', $page->id)
                              ->where('status', 'pending')->count();

        $pendingInvites = PageAdmin::where('social_page_id', $page->id)
                              ->whereNotNull('accepted_at')->count();

        $categories = PageCategory::active()->pluck('name')->toArray() ?: SocialPage::CATEGORIES;

        return view('social-pages.dashboard', compact(
            'page', 'isOwner', 'userRole',
            'chartLabels', 'chartValues',
            'hasPendingVerification', 'pendingReports', 'pendingInvites',
            'categories'
        ));
    }

    // ─── Settings ──────────────────────────────────────────────────────────────

    public function settings(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();
        $categories = PageCategory::active()->pluck('name')->toArray() ?: SocialPage::CATEGORIES;
        return view('social-pages.settings', compact('page', 'categories'));
    }

    public function updateSettings(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();

        $data = $request->validate([
            'name'                => 'required|string|max:150',
            'bio'                 => 'nullable|string|max:500',
            'categories'          => 'nullable|array|max:3',
            'categories.*'        => 'nullable|string|max:100',
            'website'             => 'nullable|url|max:300',
            'email'               => 'nullable|email|max:150',
            'phone'               => 'nullable|string|max:50',
            'location'            => 'nullable|string|max:200',
            'lat'                 => 'nullable|numeric|between:-90,90',
            'lng'                 => 'nullable|numeric|between:-180,180',
            'avatar'              => 'nullable|image|max:2048',
            'cover'               => 'nullable|image|max:4096',
            'business_hours'      => 'nullable|array',
            'action_button_type'  => 'nullable|string|max:30',
            'action_button_text'  => 'nullable|string|max:60',
            'action_button_url'   => 'nullable|url|max:300',
            'donation_url'        => 'nullable|url|max:300',
            'donation_label'      => 'nullable|string|max:60',
            'allow_tagging'           => 'nullable|boolean',
            'allow_recommendations'   => 'nullable|boolean',
            'posts_privacy_default'   => 'nullable|in:public,followers',
            'social_links'            => 'nullable|array',
            'social_links.instagram'  => 'nullable|url|max:300',
            'social_links.twitter'    => 'nullable|url|max:300',
            'social_links.youtube'    => 'nullable|url|max:300',
            'social_links.tiktok'     => 'nullable|url|max:300',
            'social_links.facebook'   => 'nullable|url|max:300',
            'social_links.linkedin'   => 'nullable|url|max:300',
            'username'                => ['nullable','string','max:60','regex:/^[a-z0-9._-]+$/i',
                                          Rule::unique('social_pages','username')->ignore($page->id)],
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

        $days  = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $hours = [];
        foreach ($days as $day) {
            $hours[$day] = [
                'open'   => $request->input("hours_{$day}_open", '09:00'),
                'close'  => $request->input("hours_{$day}_close", '17:00'),
                'closed' => $request->boolean("hours_{$day}_closed"),
            ];
        }

        $updateData = [
            'name'                => $data['name'],
            'bio'                 => $data['bio'] ?? null,
            'categories'          => array_values(array_filter($data['categories'] ?? [])),
            'website'             => $data['website'] ?? null,
            'email'               => $data['email'] ?? null,
            'phone'               => $data['phone'] ?? null,
            'location'            => $data['location'] ?? null,
            'lat'                 => $data['lat'] ?? $page->lat,
            'lng'                 => $data['lng'] ?? $page->lng,
            'business_hours'      => $hours,
            'avatar_url'          => $data['avatar_url'] ?? $page->avatar_url,
            'cover_url'           => $data['cover_url'] ?? $page->cover_url,
            'action_button_type'  => $data['action_button_type'] ?? null,
            'action_button_text'  => $data['action_button_text'] ?? null,
            'action_button_url'   => $data['action_button_url'] ?? null,
            'donation_url'        => $data['donation_url'] ?? null,
            'donation_label'      => $data['donation_label'] ?? null,
            'allow_tagging'       => $request->boolean('allow_tagging', true),
            'social_links'        => array_filter($data['social_links'] ?? []),
            'username'            => $data['username'] ? strtolower(trim($data['username'])) : $page->username,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('social_pages', 'allow_recommendations')) {
            $updateData['allow_recommendations'] = $request->boolean('allow_recommendations', true);
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('social_pages', 'posts_privacy_default')) {
            $updateData['posts_privacy_default'] = $data['posts_privacy_default'] ?? 'public';
        }
        $page->update($updateData);
        PageActivityLog::record($page->id, 'update_settings', 'Page settings updated.');

        return back()->with('success', 'Page settings updated!');
    }

    // ─── Poll Voting ───────────────────────────────────────────────────────────

    public function votePoll(Request $request, string $slug, int $postId)
    {
        $page   = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $post   = PagePost::where('id', $postId)->where('social_page_id', $page->id)->firstOrFail();
        $data   = $request->validate(['option_id' => 'required|integer']);
        $option = PagePollOption::where('id', $data['option_id'])->where('page_post_id', $postId)->firstOrFail();

        $existing = PagePollVote::where('page_post_id', $postId)->where('user_id', auth()->id())->first();
        if ($existing) {
            if ($existing->page_poll_option_id === $option->id) {
                // un-vote
                $existing->delete();
                $option->decrement('votes_count');
                $votedId = null;
            } else {
                // switch vote
                PagePollOption::where('id', $existing->page_poll_option_id)->decrement('votes_count');
                $existing->update(['page_poll_option_id' => $option->id]);
                $option->increment('votes_count');
                $votedId = $option->id;
            }
        } else {
            PagePollVote::create(['page_post_id' => $postId, 'page_poll_option_id' => $option->id, 'user_id' => auth()->id()]);
            $option->increment('votes_count');
            $votedId = $option->id;
        }

        $totalVotes = PagePollVote::where('page_post_id', $postId)->count();
        $options    = PagePollOption::where('page_post_id', $postId)->orderBy('sort_order')
                        ->get()->map(fn($o) => ['id' => $o->id, 'text' => $o->text, 'votes' => $o->votes_count]);

        return response()->json(['voted_option_id' => $votedId, 'total_votes' => $totalVotes, 'options' => $options]);
    }

    // ─── Q&A ──────────────────────────────────────────────────────────────────

    public function storeQna(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $data = $request->validate(['question' => 'required|string|max:500']);

        $qna = PageQna::create([
            'social_page_id' => $page->id,
            'user_id'        => auth()->id() ?: null,
            'question'       => $data['question'],
        ]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'id' => $qna->id]);
        }

        return back()->with('success', 'Your question has been submitted!');
    }

    public function answerQna(Request $request, string $slug, int $qnaId)
    {
        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);

        $item = PageQna::where('id', $qnaId)->where('social_page_id', $page->id)->firstOrFail();
        $data = $request->validate([
            'answer'      => 'required|string|max:2000',
            'is_featured' => 'boolean',
        ]);

        $item->update([
            'answer'      => $data['answer'],
            'answered_by' => auth()->id(),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'id' => $item->id]);
        }

        return back()->with('success', 'Answer saved.');
    }

    public function deleteQna(string $slug, int $qnaId)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);
        PageQna::where('id', $qnaId)->where('social_page_id', $page->id)->delete();
        return back()->with('success', 'Question deleted.');
    }

    // ─── Products ──────────────────────────────────────────────────────────────

    public function storeProduct(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'price'       => 'nullable|numeric|min:0',
            'currency'    => 'nullable|string|max:10',
            'image'       => 'nullable|image|max:2048',
            'link_url'    => 'nullable|url|max:500',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $filename = time() . '_prod.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pages'), $filename);
            $imageUrl = '/uploads/pages/' . $filename;
        }

        $count = PageProduct::where('social_page_id', $page->id)->count();
        PageProduct::create([
            'social_page_id' => $page->id,
            'name'           => $data['name'],
            'description'    => $data['description'] ?? null,
            'price'          => $data['price'] ?? null,
            'currency'       => $data['currency'] ?? 'NPR',
            'image_url'      => $imageUrl,
            'link_url'       => $data['link_url'] ?? null,
            'sort_order'     => $count,
        ]);

        return back()->with('success', 'Product added!');
    }

    public function deleteProduct(string $slug, int $productId)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);
        PageProduct::where('id', $productId)->where('social_page_id', $page->id)->delete();
        return back()->with('success', 'Product removed.');
    }

    // ─── Announcement & Highlights ─────────────────────────────────────────────

    public function updateAnnouncement(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);
        $data = $request->validate(['announcement' => 'nullable|string|max:500']);
        $page->update(['announcement' => $data['announcement'] ?? null]);
        return back()->with('success', 'Announcement updated.');
    }

    public function updateHighlights(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);
        $data = $request->validate([
            'highlights'          => 'nullable|array|max:6',
            'highlights.*.title'  => 'required|string|max:100',
            'highlights.*.url'    => 'required|url|max:500',
            'highlights.*.icon'   => 'nullable|string|max:10',
        ]);
        $page->update(['highlights' => $data['highlights'] ?? []]);
        return back()->with('success', 'Highlights updated.');
    }

    // ─── My Pages ──────────────────────────────────────────────────────────────

    public function myPages()
    {
        $pages = SocialPage::where('user_id', auth()->id())->latest()->get();
        return view('social-pages.my-pages', compact('pages'));
    }

    // ─── Archive ───────────────────────────────────────────────────────────────

    public function archive(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();
        $page->update(['is_archived' => true, 'is_active' => false]);
        PageActivityLog::record($page->id, 'archive', 'Page archived by owner.');
        return back()->with('success', 'Page archived. It is now hidden from public.');
    }

    public function unarchive(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();
        $page->update(['is_archived' => false, 'is_active' => true]);
        PageActivityLog::record($page->id, 'unarchive', 'Page restored from archive by owner.');
        return back()->with('success', 'Page restored and is now public.');
    }

    // ─── Blocking ──────────────────────────────────────────────────────────────

    public function blockedUsers(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);
        $blocks = PageBlock::where('social_page_id', $page->id)
            ->with('blockedUser')->latest()->paginate(30);
        return view('social-pages.blocked-users', compact('page', 'blocks'));
    }

    public function blockUser(Request $request, string $slug, int $userId)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);
        abort_if($userId === $page->user_id, 422, 'Cannot block the page owner.');

        PageBlock::firstOrCreate(['social_page_id' => $page->id, 'blocked_user_id' => $userId]);
        // Also unfollow
        $page->followers()->detach($userId);
        if ($page->followers_count > 0) $page->decrement('followers_count');

        $blockedUser = User::find($userId);
        PageActivityLog::record($page->id, 'block_user', "Blocked user: {$blockedUser?->name}", ['user_id' => $userId]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'User blocked.');
    }

    public function unblockUser(string $slug, int $userId)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);

        PageBlock::where('social_page_id', $page->id)->where('blocked_user_id', $userId)->delete();
        PageActivityLog::record($page->id, 'unblock_user', "Unblocked user ID {$userId}", ['user_id' => $userId]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'User unblocked.');
    }

    // ─── Activity Log ──────────────────────────────────────────────────────────

    public function activityLog(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);
        $logs = PageActivityLog::where('social_page_id', $page->id)
            ->with('user')->latest()->paginate(50);
        return view('social-pages.activity-log', compact('page', 'logs'));
    }

    // ─── Stories ──────────────────────────────────────────────────────────────

    public function stories(string $slug)
    {
        $page    = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);
        $stories = PageStory::where('social_page_id', $page->id)->latest()->paginate(20);
        return view('social-pages.stories', compact('page', 'stories'));
    }

    public function storeStory(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);

        $data = $request->validate([
            'image'    => 'nullable|image|max:4096',
            'caption'  => 'nullable|string|max:255',
            'bg_color' => 'nullable|string|max:20',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $filename = time() . '_story.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/stories'), $filename);
            $imageUrl = '/uploads/stories/' . $filename;
        }

        $story = PageStory::create([
            'social_page_id' => $page->id,
            'user_id'        => auth()->id(),
            'image_url'      => $imageUrl,
            'caption'        => $data['caption'] ?? null,
            'bg_color'       => $data['bg_color'] ?? null,
            'expires_at'     => now()->addHours(24),
        ]);

        PageActivityLog::record($page->id, 'create_story', 'Added a new story.');
        return back()->with('success', 'Story posted! It will expire in 24 hours.');
    }

    public function deleteStory(string $slug, int $storyId)
    {
        $page  = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);
        $story = PageStory::where('id', $storyId)->where('social_page_id', $page->id)->firstOrFail();
        $story->delete();
        return back()->with('success', 'Story deleted.');
    }

    // ─── Notification Preferences ──────────────────────────────────────────────

    public function updateNotificationPrefs(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Only followers can set prefs
        abort_unless($page->isFollowedBy(auth()->user()), 403);

        PageNotificationPref::updateOrCreate(
            ['user_id' => auth()->id(), 'social_page_id' => $page->id],
            [
                'notify_posts'         => $request->boolean('notify_posts'),
                'notify_events'        => $request->boolean('notify_events'),
                'notify_announcements' => $request->boolean('notify_announcements'),
            ]
        );

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Notification preferences updated.');
    }

    // ─── Comments Manager ──────────────────────────────────────────────────────

    public function commentsManager(string $slug)
    {
        $page     = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);
        $comments = PagePostComment::whereHas('post', fn($q) => $q->where('social_page_id', $page->id))
            ->with(['post', 'author'])
            ->latest()
            ->paginate(30);
        return view('social-pages.comments-manager', compact('page', 'comments'));
    }

    // ─── Follow Suggestions ────────────────────────────────────────────────────

    // ─── FAQ (Pinned Q&A) ──────────────────────────────────────────────────────

    public function storeFaq(Request $request, string $slug)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);

        $data = $request->validate([
            'question' => 'required|string|max:300',
            'answer'   => 'required|string|max:2000',
        ]);

        $order = PageFaq::where('social_page_id', $page->id)->max('display_order') + 1;
        PageFaq::create([
            'social_page_id' => $page->id,
            'question'       => $data['question'],
            'answer'         => $data['answer'],
            'display_order'  => $order,
        ]);

        return back()->with('success', 'FAQ added.');
    }

    public function updateFaq(Request $request, string $slug, int $faqId)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);
        $faq = PageFaq::where('id', $faqId)->where('social_page_id', $page->id)->firstOrFail();

        $data = $request->validate([
            'question' => 'required|string|max:300',
            'answer'   => 'required|string|max:2000',
        ]);

        $faq->update($data);
        return back()->with('success', 'FAQ updated.');
    }

    public function deleteFaq(string $slug, int $faqId)
    {
        $page = SocialPage::where('slug', $slug)->firstOrFail();
        abort_unless($page->isManagedBy(auth()->user()), 403);
        PageFaq::where('id', $faqId)->where('social_page_id', $page->id)->delete();
        return back()->with('success', 'FAQ deleted.');
    }

    // ─── Data Export ───────────────────────────────────────────────────────────

    public function exportData(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();

        $zipPath = sys_get_temp_dir() . '/page_export_' . $page->id . '_' . time() . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Page info
        $info = $page->only(['name', 'slug', 'username', 'page_type', 'bio', 'categories',
                              'website', 'email', 'phone', 'location', 'followers_count',
                              'views_count', 'rating_avg', 'reviews_count', 'created_at']);
        $zip->addFromString('page_info.json', json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Posts
        $posts = PagePost::where('social_page_id', $page->id)->latest()->get()
            ->map(fn($p) => $p->only(['id', 'type', 'body', 'image_url', 'video_url',
                                       'event_title', 'event_start', 'event_venue',
                                       'likes_count', 'created_at']));
        $zip->addFromString('posts.json', json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Followers list (user ids + timestamps only, no PII)
        $followers = DB::table('social_page_followers')
            ->where('social_page_id', $page->id)
            ->select('user_id', 'created_at')
            ->get();
        $zip->addFromString('followers.json', json_encode($followers, JSON_PRETTY_PRINT));

        // Activity log
        $activity = PageActivityLog::where('social_page_id', $page->id)->latest()->limit(500)->get()
            ->map(fn($a) => $a->only(['action', 'description', 'created_at']));
        $zip->addFromString('activity_log.json', json_encode($activity, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $zip->close();

        return response()->download($zipPath, $page->slug . '_export.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend();
    }

    // ─── Follow suggestions ────────────────────────────────────────────────────

    public function followSuggestions(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Suggest pages in the same categories that the current user doesn't follow yet
        $categories = $page->categories ?? [];
        $userId     = auth()->id();

        $followed = $userId
            ? SocialPage::whereHas('followers', fn($q) => $q->where('user_id', $userId))->pluck('id')
            : collect();

        $suggestions = SocialPage::where('is_active', true)
            ->where('id', '!=', $page->id)
            ->whereNotIn('id', $followed)
            ->where(function ($q) use ($categories) {
                foreach ($categories as $cat) {
                    $q->orWhereJsonContains('categories', $cat);
                }
            })
            ->orderByDesc('followers_count')
            ->limit(6)
            ->get(['id', 'name', 'slug', 'avatar_url', 'categories', 'followers_count', 'is_verified']);

        return response()->json($suggestions->map(fn($p) => [
            'id'              => $p->id,
            'name'            => $p->name,
            'slug'            => $p->slug,
            'avatar'          => $p->avatar,
            'category'        => $p->first_category,
            'followers_count' => $p->followers_count,
            'is_verified'     => $p->is_verified,
        ]));
    }
}
