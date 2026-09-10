<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\PageAdmin;
use App\Models\PageCategory;
use App\Models\PagePollOption;
use App\Models\PagePollVote;
use App\Models\PagePost;
use App\Models\PagePostComment;
use App\Models\PagePostLike;
use App\Models\PageProduct;
use App\Models\PageQna;
use App\Models\PageReport;
use App\Models\PageReview;
use App\Models\PageVerificationRequest;
use App\Models\PageViewLog;
use App\Models\SocialPage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
                'page_type'    => 'required|in:creator,business',
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
                'page_type'    => 'required|in:creator,business',
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

        $page = SocialPage::create([
            'uuid'         => Str::uuid(),
            'user_id'      => auth()->id(),
            'name'         => $data['name'],
            'slug'         => $slug,
            'username'     => $username,
            'page_type'    => $data['page_type'],
            'categories'   => array_values(array_filter($data['categories'] ?? [])),
            'bio'          => $data['bio'] ?? null,
            'website'      => $data['website'] ?? null,
            'location'     => $data['location'] ?? null,
            'phone'        => $data['phone'] ?? null,
            'social_links' => $data['social_links'] ?? null,
            'status'       => 'active',
        ]);

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

        return view('social-pages.show', compact(
            'page', 'isOwner', 'isManager', 'userRole', 'isFollowing', 'isSaved',
            'posts', 'reviews', 'userReview', 'events', 'isOpen', 'pinnedPost',
            'photos', 'qnaItems', 'products', 'userVoteMap'
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
        }

        return response()->json([
            'following'       => $following,
            'followers_count' => $page->fresh()->followers_count,
        ]);
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
            'name'           => 'required|string|max:150',
            'bio'            => 'nullable|string|max:500',
            'categories'     => 'nullable|array|max:3',
            'categories.*'   => 'nullable|string|max:100',
            'website'        => 'nullable|url|max:300',
            'email'          => 'nullable|email|max:150',
            'phone'          => 'nullable|string|max:50',
            'location'       => 'nullable|string|max:200',
            'lat'            => 'nullable|numeric|between:-90,90',
            'lng'            => 'nullable|numeric|between:-180,180',
            'avatar'         => 'nullable|image|max:2048',
            'cover'          => 'nullable|image|max:4096',
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

        $days  = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $hours = [];
        foreach ($days as $day) {
            $hours[$day] = [
                'open'   => $request->input("hours_{$day}_open", '09:00'),
                'close'  => $request->input("hours_{$day}_close", '17:00'),
                'closed' => $request->boolean("hours_{$day}_closed"),
            ];
        }

        $page->update([
            'name'           => $data['name'],
            'bio'            => $data['bio'] ?? null,
            'categories'     => array_values(array_filter($data['categories'] ?? [])),
            'website'        => $data['website'] ?? null,
            'email'          => $data['email'] ?? null,
            'phone'          => $data['phone'] ?? null,
            'location'       => $data['location'] ?? null,
            'lat'            => $data['lat'] ?? $page->lat,
            'lng'            => $data['lng'] ?? $page->lng,
            'business_hours' => $hours,
            'avatar_url'     => $data['avatar_url'] ?? $page->avatar_url,
            'cover_url'      => $data['cover_url'] ?? $page->cover_url,
        ]);

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
}
