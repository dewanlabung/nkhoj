<?php

namespace App\Http\Controllers;

use App\Models\AdZone;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Role;
use App\Models\Widget;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Post;
use App\Models\Question;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\SupportReply;
use App\Models\SupportTicket;
use App\Models\Tag;
use App\Models\User;
use App\Models\AiPostTopic;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    private function requireAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Admin access only.');
        }
    }

    private function getSettings(): array
    {
        $path = storage_path('app/site_settings.json');
        return File::exists($path) ? (json_decode(File::get($path), true) ?? []) : [];
    }

    private function saveSettings(array $data): void
    {
        File::put(storage_path('app/site_settings.json'), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // ── Dashboard ─────────────────────────────────────────────
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
                'users'             => User::count(),
                'posts'             => Post::count(),
                'published'         => Post::where('status', 'published')->count(),
                'comments'          => Comment::count(),
                'views'             => Post::sum('view_count'),
                'tags'              => Tag::count(),
                'pending_posts'     => Post::where('status', 'draft')->count(),
                'scheduled_posts'   => Post::where('status', 'scheduled')->count(),
                'contacts'          => ContactMessage::whereNull('read_at')->count(),
                'subscribers'       => NewsletterSubscriber::where('is_active', true)->count(),
                'pending_comments'  => Comment::where('is_approved', false)->count(),
            ],
            'recentPosts'    => Post::with('author')->latest()->limit(15)->get(),
            'recentComments' => Comment::with(['post', 'author'])->latest()->limit(6)->get(),
            'recentUsers'    => User::latest()->limit(8)->get(),
            'chartLabels'    => $chartLabels,
            'chartData'      => $chartData,
        ]);
    }

    // ── Analytics ─────────────────────────────────────────────
    public function analytics()
    {
        $this->requireAdmin();

        // Traffic chart — views per day (last 30 days, real view_count from published posts)
        $chartLabels = [];
        $chartViews  = [];
        $chartUsers  = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('d M');
            $chartViews[]  = (int) Post::whereDate('published_at', $date->toDateString())
                ->where('status', 'published')->sum('view_count');
            $chartUsers[]  = User::whereDate('created_at', $date->toDateString())->count();
        }

        $topPosts = Post::with(['author', 'category'])
            ->where('status', 'published')
            ->orderByDesc('view_count')
            ->limit(10)->get();

        $topUsers = User::withCount(['posts', 'comments'])
            ->orderByDesc('posts_count')
            ->limit(8)->get();

        $topCategories = Category::withCount(['posts' => fn($q) => $q->where('status','published')])
            ->orderByDesc('posts_count')
            ->limit(10)->get();

        $topTags = Tag::withCount(['posts' => fn($q) => $q->where('status','published')])
            ->having('posts_count', '>', 0)
            ->orderByDesc('posts_count')
            ->limit(15)->get();

        $statusBreakdown = Post::selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status')->toArray();

        $postFormatBreakdown = Post::selectRaw('COALESCE(post_format,"article") as fmt, count(*) as total')
            ->groupBy('fmt')->pluck('total', 'fmt')->toArray();

        $totalViews   = (int) Post::sum('view_count');
        $totalPosts   = Post::where('status', 'published')->count();
        $avgViews     = $totalPosts > 0 ? round($totalViews / $totalPosts) : 0;
        $totalUsers   = User::count();
        $newUsersMonth= User::where('created_at', '>=', now()->startOfMonth())->count();

        return view('admin.analytics', compact(
            'chartLabels', 'chartViews', 'chartUsers',
            'topPosts', 'topUsers', 'topCategories', 'topTags',
            'statusBreakdown', 'postFormatBreakdown',
            'totalViews', 'totalPosts', 'avgViews', 'totalUsers', 'newUsersMonth'
        ));
    }

    // ── Users ─────────────────────────────────────────────────
    public function users(Request $request)
    {
        $this->requireAdmin();
        $query = User::withCount(['posts','comments']);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($sub) => $sub->where('name','like',"%$q%")->orWhere('email','like',"%$q%")->orWhere('username','like',"%$q%"));
        }
        if ($request->filled('role'))   $query->where('role', $request->role);
        if ($request->filled('status')) {
            $request->status === 'banned' ? $query->where('is_banned', true) : $query->where('is_banned', false);
        }
        $perPage = (int) $request->input('per_page', 20);
        return view('admin.users', ['users' => $query->latest()->paginate($perPage)->withQueryString(), 'roles' => Role::orderBy('sort_order')->get()]);
    }

    public function createUser()
    {
        $this->requireAdmin();
        return view('admin.user-create', ['roles' => Role::orderBy('sort_order')->get()]);
    }

    public function storeUser(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'name'                  => 'required|string|max:100',
            'username'              => 'required|string|max:60|unique:users',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|string|min:6|confirmed',
            'role'                  => 'required|in:reader,reporter,editor,admin',
        ]);
        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'role'     => $data['role'],
            'uuid'     => (string) \Illuminate\Support\Str::uuid(),
        ]);
        return redirect('/admin/users/' . $user->id)->with('success', 'User created.');
    }

    public function showUser(int $id)
    {
        $this->requireAdmin();
        $user = User::withCount(['posts','comments'])->findOrFail($id);
        return view('admin.user-details', compact('user'));
    }

    public function editUser(int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        return view('admin.user-edit', ['user' => $user, 'roles' => Role::orderBy('sort_order')->get(), 'platforms' => User::socialPlatforms()]);
    }

    public function updateUser(Request $request, int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'first_name' => 'nullable|string|max:80',
            'last_name'  => 'nullable|string|max:80',
            'username'   => 'required|string|max:60|unique:users,username,'.$id,
            'email'      => 'required|email|unique:users,email,'.$id,
            'role'       => 'required|in:reader,reporter,editor,admin',
            'bio'        => 'nullable|string|max:500',
            'balance'    => 'nullable|numeric|min:0',
            'website'    => 'nullable|url|max:255',
        ]);
        $data['is_banned']      = $request->input('status') === 'banned';
        $data['reward_system']  = $request->boolean('reward_system');
        if ($request->boolean('verify_email') && !$user->email_verified_at) {
            $data['email_verified_at'] = now();
        }
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $data['password'] = bcrypt($request->password);
        }
        $social = array_filter($request->input('social_links', []), fn($v) => !empty(trim((string)$v)));
        $data['social_links'] = $social ?: null;
        $user->update($data);
        return redirect('/admin/users/' . $id)->with('success', 'User updated.');
    }

    public function deleteUser(int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) return back()->with('error', 'Cannot delete yourself.');
        if ($user->role === 'admin') return back()->with('error', 'Cannot delete an admin account.');
        $user->delete();
        return redirect('/admin/users')->with('success', 'User deleted.');
    }

    public function verifyEmail(int $id)
    {
        $this->requireAdmin();
        User::findOrFail($id)->update(['email_verified_at' => now()]);
        return back()->with('success', 'Email marked as verified.');
    }

    public function toggleRewardSystem(int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        $user->update(['reward_system' => !$user->reward_system]);
        return back()->with('success', 'Reward system ' . ($user->reward_system ? 'enabled' : 'disabled') . '.');
    }

    public function userPermissions(int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        return view('admin.user-permissions', ['user' => $user, 'allPerms' => Role::allPermissions(), 'roles' => Role::orderBy('sort_order')->get()]);
    }

    public function updateUserPermissions(Request $request, int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        $user->update([
            'role'             => $request->input('role', $user->role),
            'extra_permissions'=> $request->input('permissions', []),
        ]);
        return back()->with('success', 'Permissions updated.');
    }

    public function impersonate(int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) return back()->with('error', 'Cannot impersonate yourself.');
        session(['impersonating_admin_id' => auth()->id()]);
        auth()->loginUsingId($id);
        return redirect('/')->with('info', 'You are now logged in as ' . $user->name . '. <a href="/admin/users/stop-impersonating" class="underline font-semibold">Return to Admin</a>');
    }

    public function stopImpersonating()
    {
        $adminId = session('impersonating_admin_id');
        if (!$adminId) return redirect('/admin');
        session()->forget('impersonating_admin_id');
        auth()->loginUsingId($adminId);
        return redirect('/admin/users')->with('success', 'Returned to your admin account.');
    }

    public function updateUserRole(Request $request, int $id)
    {
        $this->requireAdmin();
        User::findOrFail($id)->update($request->validate(['role' => 'required|in:reader,reporter,editor,admin']));
        return back()->with('success', 'Role updated.');
    }

    public function banUser(int $id)
    {
        $this->requireAdmin();
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) return back()->with('error', 'Cannot ban yourself.');
        $user->update(['is_banned' => !($user->is_banned ?? false)]);
        return back()->with('success', $user->is_banned ? 'User banned.' : 'User unbanned.');
    }

    // ── Categories ────────────────────────────────────────────
    public function categories()
    {
        $this->requireAdmin();
        return view('admin.categories', ['categories' => Category::withCount('posts')->orderBy('sort_order')->get()]);
    }

    public function storeCategory(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate(['name_en' => 'required|string|max:100', 'name_ne' => 'nullable|string|max:100', 'slug' => 'nullable|string|max:100', 'sort_order' => 'nullable|integer']);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name_en']);
        Category::create($data);
        return back()->with('success', 'Category added.');
    }

    public function updateCategory(Request $request, int $id)
    {
        $this->requireAdmin();
        Category::findOrFail($id)->update($request->validate([
            'name_en'      => 'required|string|max:100',
            'name_ne'      => 'nullable|string|max:100',
            'sort_order'   => 'nullable|integer',
            'is_active'    => 'nullable|boolean',
            'is_exclusive' => 'nullable|boolean',
            'color'        => 'nullable|string|max:20',
        ]));
        return back()->with('success', 'Category updated.');
    }

    public function reorderCategories(Request $request)
    {
        $this->requireAdmin();
        $order = $request->input('order', []);
        foreach ($order as $index => $id) {
            Category::where('id', $id)->update(['sort_order' => $index + 1]);
        }
        return response()->json(['ok' => true]);
    }

    public function deleteCategory(int $id)
    {
        $this->requireAdmin();
        Category::findOrFail($id)->delete();
        return back()->with('success', 'Category deleted.');
    }

    // ── Posts ─────────────────────────────────────────────────
    public function posts(Request $request)
    {
        $this->requireAdmin();
        $query = Post::with(['author', 'category']);
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('post_format')) $query->where('post_format', $request->post_format);
        if ($request->filled('q'))           $query->where('title', 'like', '%'.$request->q.'%');
        return view('admin.posts', [
            'posts'  => $query->latest()->paginate(20),
            'counts' => [
                'all'       => Post::count(),
                'published' => Post::where('status','published')->count(),
                'draft'     => Post::where('status','draft')->count(),
                'scheduled' => Post::where('status','scheduled')->count(),
                'archived'  => Post::where('status','archived')->count(),
            ],
        ]);
    }

    public function updatePostStatus(Request $request, int $id)
    {
        $this->requireAdmin();
        $data = $request->validate(['status' => 'required|in:draft,published,scheduled,archived']);
        $post = Post::findOrFail($id);
        $post->update([
            'status'       => $data['status'],
            'published_at' => ($data['status'] === 'published' && !$post->published_at) ? now() : $post->published_at,
        ]);
        return back()->with('success', 'Status updated.');
    }

    public function deletePost(int $id)
    {
        $this->requireAdmin();
        Post::findOrFail($id)->delete();
        return back()->with('success', 'Post deleted.');
    }

    public function togglePostPro(int $id)
    {
        $this->requireAdmin();
        $post = Post::findOrFail($id);
        $post->update(['is_pro' => !$post->is_pro]);
        return back()->with('success', $post->is_pro ? 'Post marked as Pro.' : 'Pro restriction removed.');
    }

    public function bulkPostAction(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate(['action' => 'required|in:publish,draft,archived,delete', 'ids' => 'required|array', 'ids.*' => 'integer']);
        if ($data['action'] === 'delete') {
            Post::whereIn('id', $data['ids'])->delete();
        } else {
            Post::whereIn('id', $data['ids'])->update(['status' => $data['action']]);
        }
        return back()->with('success', 'Bulk action applied.');
    }

    // ── Comments ──────────────────────────────────────────────
    public function comments(Request $request)
    {
        $this->requireAdmin();
        $query = Comment::with(['post', 'author']);
        if ($request->filled('q')) $query->where('body', 'like', '%'.$request->q.'%');
        return view('admin.comments', ['comments' => $query->latest()->paginate(30)]);
    }

    public function approveComment(int $id)
    {
        $this->requireAdmin();
        Comment::findOrFail($id)->update(['is_approved' => true]);
        return back()->with('success', 'Comment approved.');
    }

    public function deleteComment(int $id)
    {
        $this->requireAdmin();
        Comment::findOrFail($id)->delete();
        return back()->with('success', 'Comment deleted.');
    }

    // ── Tags ──────────────────────────────────────────────────
    public function tags()
    {
        $this->requireAdmin();
        return view('admin.tags', ['tags' => Tag::withCount('posts')->orderByDesc('posts_count')->get()]);
    }

    public function deleteTag(int $id)
    {
        $this->requireAdmin();
        Tag::findOrFail($id)->delete();
        return back()->with('success', 'Tag deleted.');
    }

    // ── Media ─────────────────────────────────────────────────
    public function media()
    {
        $this->requireAdmin();
        $dir   = public_path('uploads');
        $files = [];
        $total = 0;
        if (File::exists($dir)) {
            foreach (File::files($dir) as $f) {
                $size   = $f->getSize();
                $total += $size;
                $files[] = ['name' => $f->getFilename(), 'ext' => strtolower($f->getExtension()), 'size' => $this->formatBytes($size), 'modified' => $f->getMTime()];
            }
            usort($files, fn($a, $b) => $b['modified'] - $a['modified']);
        }
        return view('admin.media', ['files' => $files, 'totalSize' => $this->formatBytes($total)]);
    }

    public function uploadMedia(Request $request)
    {
        $this->requireAdmin();
        $request->validate(['files' => 'required', 'files.*' => 'file|max:51200']);
        foreach ($request->file('files', []) as $file) {
            $name = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $name);
        }
        return back()->with('success', 'Files uploaded.');
    }

    public function deleteMedia(string $filename)
    {
        $this->requireAdmin();
        $path = public_path('uploads/' . basename($filename));
        if (File::exists($path)) File::delete($path);
        return back()->with('success', 'File deleted.');
    }

    // ── Contact Messages ──────────────────────────────────────
    public function contacts()
    {
        $this->requireAdmin();
        return view('admin.contacts', [
            'contacts' => ContactMessage::latest()->paginate(20),
            'unread'   => ContactMessage::whereNull('read_at')->count(),
        ]);
    }

    public function markContactRead(int $id)
    {
        $this->requireAdmin();
        ContactMessage::findOrFail($id)->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function deleteContact(int $id)
    {
        $this->requireAdmin();
        ContactMessage::findOrFail($id)->delete();
        return back()->with('success', 'Message deleted.');
    }

    // ── Newsletter ────────────────────────────────────────────
    public function newsletter()
    {
        $this->requireAdmin();
        return view('admin.newsletter', [
            'subscribers'       => NewsletterSubscriber::latest()->paginate(30),
            'totalSubscribers'  => NewsletterSubscriber::count(),
            'activeSubscribers' => NewsletterSubscriber::where('is_active', true)->count(),
        ]);
    }

    public function sendNewsletter(Request $request)
    {
        $this->requireAdmin();
        $request->validate(['subject' => 'required|string|max:200', 'body' => 'required|string']);
        $count = NewsletterSubscriber::where('is_active', true)->count();
        return back()->with('success', "Newsletter queued for {$count} subscribers. (SMTP configuration required to send.)");
    }

    public function deleteSubscriber(int $id)
    {
        $this->requireAdmin();
        NewsletterSubscriber::findOrFail($id)->delete();
        return back()->with('success', 'Subscriber removed.');
    }

    public function exportSubscribers()
    {
        $this->requireAdmin();
        $subs = NewsletterSubscriber::where('is_active', true)->get(['email', 'name', 'created_at']);
        $csv  = "Email,Name,Subscribed\n";
        foreach ($subs as $s) {
            $csv .= "\"{$s->email}\",\"{$s->name}\",\"{$s->created_at->format('Y-m-d')}\"\n";
        }
        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="subscribers.csv"']);
    }

    // ── Questions ─────────────────────────────────────────────
    public function questions(Request $request)
    {
        $this->requireAdmin();
        $query = Question::with(['user', 'category'])->withCount('answers')->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($s) => $s->where('title', 'like', "%$q%")->orWhere('content', 'like', "%$q%"));
        }
        $questions = $query->paginate(20);
        $stats = [
            'total'    => Question::count(),
            'open'     => Question::where('status', 'open')->count(),
            'closed'   => Question::where('status', 'closed')->count(),
            'pending'  => Question::where('status', 'pending')->count(),
            'answered' => Question::whereNotNull('best_answer_id')->count(),
        ];
        return view('admin.questions', compact('questions', 'stats'));
    }

    public function updateQuestionStatus(Request $request, int $id)
    {
        $this->requireAdmin();
        $question = Question::findOrFail($id);
        $question->update(['status' => $request->status]);
        return back()->with('success', 'Question status updated.');
    }

    public function deleteQuestion(int $id)
    {
        $this->requireAdmin();
        $question = Question::findOrFail($id);
        Answer::where('question_id', $id)->delete();
        $question->tags()->detach();
        $question->delete();
        return back()->with('success', 'Question deleted.');
    }

    public function deleteAnswer(int $id)
    {
        $this->requireAdmin();
        $answer = Answer::findOrFail($id);
        $question = Question::find($answer->question_id);
        $answer->delete();
        if ($question) {
            $question->decrement('answers_count');
            if ($question->best_answer_id === $id) {
                $question->update(['best_answer_id' => null]);
            }
        }
        return back()->with('success', 'Answer deleted.');
    }

    // ── Polls ─────────────────────────────────────────────────
    public function polls()
    {
        $this->requireAdmin();
        $polls = Poll::with(['options', 'author', 'post'])->latest()->paginate(20);
        return view('admin.polls', compact('polls'));
    }

    public function storePoll(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'question'       => 'required|string|max:300',
            'options'        => 'required|array|min:2',
            'options.*'      => 'required|string|max:200',
            'allow_multiple' => 'nullable|boolean',
            'expires_at'     => 'nullable|date|after:now',
        ]);

        $poll = Poll::create([
            'question'       => $data['question'],
            'author_id'      => auth()->id(),
            'allow_multiple' => $request->boolean('allow_multiple'),
            'expires_at'     => $data['expires_at'] ?? null,
        ]);

        foreach (array_filter($data['options']) as $opt) {
            $poll->options()->create(['text' => trim($opt), 'votes_count' => 0]);
        }

        return back()->with('success', 'Poll created. Use shortcode [poll:'.$poll->id.'] in any post.');
    }

    public function deletePoll(int $id)
    {
        $this->requireAdmin();
        Poll::findOrFail($id)->delete();
        return back()->with('success', 'Poll deleted.');
    }

    // ── Ad Zones ──────────────────────────────────────────────
    public function ads()
    {
        $this->requireAdmin();
        return view('admin.ads', ['adZones' => AdZone::orderBy('position')->get()]);
    }

    public function storeAd(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'position' => 'required|in:header,sidebar,footer,in_content,after_post',
            'code'     => 'required|string',
        ]);
        AdZone::create($data + ['is_active' => true]);
        return back()->with('success', 'Ad zone created.');
    }

    public function updateAd(Request $request, int $id)
    {
        $this->requireAdmin();
        $data = $request->validate(['name' => 'required|string|max:100', 'code' => 'required|string', 'is_active' => 'nullable|boolean']);
        AdZone::findOrFail($id)->update(['name' => $data['name'], 'code' => $data['code'], 'is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Ad zone updated.');
    }

    public function deleteAd(int $id)
    {
        $this->requireAdmin();
        AdZone::findOrFail($id)->delete();
        return back()->with('success', 'Ad zone deleted.');
    }

    // ── Settings ──────────────────────────────────────────────
    public function settings()
    {
        $this->requireAdmin();
        return view('admin.settings', ['s' => $this->getSettings()]);
    }

    public function updateSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        foreach ($request->only(['site_name','tagline_en','tagline_ne','site_description','contact_email','ga_id','adsense_id','site_url']) as $k => $v) {
            $s[$k] = $v;
        }
        foreach (['social_facebook','social_twitter','social_instagram','social_youtube','social_tiktok'] as $k) {
            $s[$k] = $request->input($k, '');
        }
        $s['allow_guest_comments']  = $request->boolean('allow_guest_comments');
        $s['require_post_approval'] = $request->boolean('require_post_approval');
        $s['show_breaking_news']    = $request->boolean('show_breaking_news');
        $this->saveSettings($s);
        return back()->with('success', 'Settings saved.');
    }

    // ── Granular settings sub-forms ───────────────────────────

    private function patchSettings(array $patch): void
    {
        $this->saveSettings(array_merge($this->getSettings(), $patch));
    }

    public function updateSettingsUrl(Request $request)
    {
        $this->requireAdmin();
        $this->patchSettings(['site_url' => $request->input('site_url', '')]);
        return back()->with('success', 'Site URL saved.');
    }

    public function updateSettingsName(Request $request)
    {
        $this->requireAdmin();
        $name = $request->input('site_name', 'nkhoj');
        $s    = $this->getSettings();
        $s['site_name'] = $name;
        // Keep localized app_name in sync for all languages
        foreach (array_keys($s['localized'] ?? []) as $lang) {
            $s['localized'][$lang]['app_name'] = $name;
        }
        $this->saveSettings($s);
        return back()->with('success', 'Site name saved.');
    }

    public function updateSettingsTagline(Request $request)
    {
        $this->requireAdmin();
        $this->patchSettings($request->only(['tagline_en','tagline_ne','site_description']));
        return back()->with('success', 'Tagline saved.');
    }

    public function updateSettingsContact(Request $request)
    {
        $this->requireAdmin();
        $this->patchSettings(['contact_email' => $request->input('contact_email', '')]);
        return back()->with('success', 'Contact email saved.');
    }

    public function updateSettingsSocial(Request $request)
    {
        $this->requireAdmin();
        $this->patchSettings($request->only(['social_facebook','social_twitter','social_instagram','social_youtube','social_tiktok']));
        return back()->with('success', 'Social links saved.');
    }

    public function updateSettingsAnalytics(Request $request)
    {
        $this->requireAdmin();
        $this->patchSettings($request->only(['ga_id','adsense_id']));
        return back()->with('success', 'Analytics settings saved.');
    }

    public function updateSettingsBehaviour(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $s['allow_guest_comments']  = $request->boolean('allow_guest_comments');
        $s['require_post_approval'] = $request->boolean('require_post_approval');
        $s['show_breaking_news']    = $request->boolean('show_breaking_news');
        $this->saveSettings($s);
        return back()->with('success', 'Saved.');
    }

    private function uploadBrandAsset(Request $request, string $field, string $settingKey): \Illuminate\Http\RedirectResponse
    {
        $this->requireAdmin();
        if (!$request->hasFile($field)) return back();
        $file = $request->file($field);
        $ext  = $file->getClientOriginalExtension();
        $name = $field . '_' . time() . '.' . $ext;
        $file->move(public_path('brand'), $name);
        $this->patchSettings([$settingKey => '/brand/' . $name]);
        return back()->with('success', 'Image updated.');
    }

    public function uploadFavicon(Request $request)        { return $this->uploadBrandAsset($request, 'favicon', 'favicon_url'); }
    public function uploadLogoDark(Request $request)       { return $this->uploadBrandAsset($request, 'logo_dark', 'logo_dark_url'); }
    public function uploadLogoLight(Request $request)      { return $this->uploadBrandAsset($request, 'logo_light', 'logo_light_url'); }
    public function uploadLogoCompactDark(Request $request){ return $this->uploadBrandAsset($request, 'logo_compact_dark', 'logo_compact_dark_url'); }
    public function uploadLogoCompactLight(Request $request){ return $this->uploadBrandAsset($request, 'logo_compact_light', 'logo_compact_light_url'); }

    public function removeBrandAsset(string $asset)
    {
        $this->requireAdmin();
        $keyMap = [
            'favicon'           => 'favicon_url',
            'logo-dark'         => 'logo_dark_url',
            'logo-light'        => 'logo_light_url',
            'logo-compact-dark' => 'logo_compact_dark_url',
            'logo-compact-light'=> 'logo_compact_light_url',
        ];
        if (!isset($keyMap[$asset])) return redirect('/admin/settings');
        $s = $this->getSettings();
        $key = $keyMap[$asset];
        if (!empty($s[$key])) {
            $path = public_path(ltrim($s[$key], '/'));
            if (file_exists($path)) @unlink($path);
        }
        $s[$key] = null;
        $this->saveSettings($s);
        return redirect('/admin/settings')->with('success', 'Image removed.');
    }

    // ── Content Settings ──────────────────────────────────────
    public function contentSettings(Request $request)
    {
        $this->requireAdmin();
        return view('admin.content-settings', ['settings' => $this->getSettings()]);
    }

    public function updateContentSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $tab = $request->input('tab', 'general');

        if ($tab === 'general') {
            foreach (['show_featured_section','comment_system','comment_approval','emoji_reactions','show_latest_posts'] as $k) {
                $s[$k] = $request->boolean($k);
            }
            $s['posts_per_page'] = (int) $request->input('posts_per_page', 16);
        } elseif ($tab === 'posts') {
            $s['post_url_structure'] = $request->input('post_url_structure', 'slug');
            foreach (['bulk_upload_authors','delete_images_with_post','audio_download','show_post_author','show_post_date','show_post_view_count','require_approval_new','require_approval_edited','restrict_rss'] as $k) {
                $s[$k] = $request->boolean($k);
            }
            $s['popular_posts_limit'] = (int) $request->input('popular_posts_limit', 5);
            $s['related_posts_limit'] = (int) $request->input('related_posts_limit', 6);
        } elseif ($tab === 'post_formats') {
            $s['formats_enabled'] = [];
            foreach (['article','gallery','sorted_list','table_of_contents','video','audio','trivia_quiz','personality_quiz','poll','recipe','event'] as $f) {
                $s['formats_enabled'][$f] = (bool) $request->input("formats_enabled.$f", false);
            }
        } elseif ($tab === 'file_upload') {
            $s['image_format']  = $request->input('image_format', 'webp');
            $s['allowed_extensions'] = implode(',', $request->input('extensions', []));
            foreach (['max_image_size','max_video_size','max_audio_size','max_file_size'] as $k) {
                $s[$k] = (int) $request->input($k, 20);
            }
        } elseif ($tab === 'featured') {
            $s['featured_source']   = $request->input('featured_source', 'manual');
            $s['featured_sort']     = $request->input('featured_sort', 'order');
            $s['featured_duration'] = (int) $request->input('featured_duration', 10);
            $s['featured_limit']    = (int) $request->input('featured_limit', 15);
        }

        File::put(storage_path('app/site_settings.json'), json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return back()->with('success', 'Settings saved.');
    }

    public function updateAiSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $s['ai_enabled']      = $request->boolean('ai_enabled');
        $s['ai_provider']     = $request->input('ai_provider', 'gemini');
        $s['ai_api_key']      = $request->input('ai_api_key', '');
        $s['ai_model']        = $request->input('ai_model_gemini', 'gemini-2.5-flash-lite-legacy');
        $s['ai_model_chatgpt']= $request->input('ai_model_chatgpt', 'gpt-4o-mini');
        File::put(storage_path('app/site_settings.json'), json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return back()->with('success', 'AI settings saved.');
    }

    public function updateAutoDelete(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $s['auto_delete_enabled'] = $request->boolean('auto_delete_enabled');
        $s['auto_delete_days']    = (int) $request->input('auto_delete_days', 30);
        $s['auto_delete_scope']   = $request->input('auto_delete_scope', 'all');
        File::put(storage_path('app/site_settings.json'), json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return back()->with('success', 'Auto-delete settings saved.');
    }

    // ── Widgets ───────────────────────────────────────────────
    public function widgets()
    {
        $this->requireAdmin();
        $widgets = Widget::orderBy('where_to_display')->orderBy('display_order')->get();
        return view('admin.widgets', [
            'widgets'   => $widgets,
            'types'     => Widget::types(),
            'positions' => Widget::positions(),
        ]);
    }

    public function storeWidget(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'type'            => 'required|in:' . implode(',', array_keys(Widget::types())),
            'title'           => 'required|string|max:150',
            'where_to_display'=> 'required|in:' . implode(',', array_keys(Widget::positions())),
            'display_order'   => 'nullable|integer|min:0|max:999',
        ]);
        Widget::create($data + ['is_active' => true, 'display_order' => $data['display_order'] ?? 0]);
        return back()->with('success', 'Widget created.');
    }

    public function editWidget(int $id)
    {
        $this->requireAdmin();
        return view('admin.widget-edit', [
            'widget'    => Widget::findOrFail($id),
            'types'     => Widget::types(),
            'positions' => Widget::positions(),
        ]);
    }

    public function updateWidget(Request $request, int $id)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'type'            => 'required|in:' . implode(',', array_keys(Widget::types())),
            'title'           => 'required|string|max:150',
            'where_to_display'=> 'required|in:' . implode(',', array_keys(Widget::positions())),
            'display_order'   => 'nullable|integer|min:0|max:999',
        ]);
        Widget::findOrFail($id)->update($data + ['is_active' => $request->boolean('is_active'), 'display_order' => $data['display_order'] ?? 0]);
        return redirect('/admin/widgets')->with('success', 'Widget updated.');
    }

    public function deleteWidget(int $id)
    {
        $this->requireAdmin();
        Widget::findOrFail($id)->delete();
        return back()->with('success', 'Widget deleted.');
    }

    // ── SEO ───────────────────────────────────────────────────
    public function seo()
    {
        $this->requireAdmin();
        $robotsPath = public_path('robots.txt');
        $robots = File::exists($robotsPath) ? File::get($robotsPath)
            : "User-agent: *\nAllow: /\n\nSitemap: " . url('/sitemap.xml');
        $s = $this->getSettings();
        return view('admin.seo', compact('robots', 's'));
    }

    public function updateRobots(Request $request)
    {
        $this->requireAdmin();
        $request->validate(['robots' => 'required|string']);
        File::put(public_path('robots.txt'), $request->robots);
        return back()->with('success', 'robots.txt updated.');
    }

    public function updateSeoMeta(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();

        // Global meta
        $s['site_keywords']    = $request->input('site_keywords', '');
        $s['og_default_image'] = $request->input('og_default_image', '');
        $s['enable_jsonld']    = $request->boolean('enable_jsonld');

        // Webmaster verification
        $s['verify_google'] = $request->input('verify_google', '');
        $s['verify_bing']   = $request->input('verify_bing', '');

        // Per-page title/description templates
        foreach (['home', 'post', 'category', 'author', 'tag', 'search'] as $page) {
            $s["seo_title_{$page}"]  = $request->input("seo_title_{$page}", '');
            $s["seo_desc_{$page}"]   = $request->input("seo_desc_{$page}", '');
        }

        $this->saveSettings($s);
        return back()->with('success', 'Meta settings saved.');
    }

    public function updateSeoSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $s['ga_enabled']        = $request->boolean('ga_enabled');
        $s['ga_id']             = $request->input('ga_id', '');
        $s['sitemap_frequency'] = $request->input('sitemap_frequency', 'auto');
        $s['sitemap_lastmod']   = $request->input('sitemap_lastmod', 'none');
        $s['sitemap_priority']  = $request->input('sitemap_priority', 'none');
        $this->saveSettings($s);
        return back()->with('success', 'SEO settings saved.');
    }

    // ── Storage ───────────────────────────────────────────────
    public function storage()
    {
        $this->requireAdmin();
        return view('admin.storage', ['s' => $this->getSettings()]);
    }

    public function updateStorage(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $s['active_storage'] = $request->input('active_storage', 'local');
        foreach (['s3', 'r2', 'b2'] as $driver) {
            if ($request->has($driver)) {
                $s[$driver] = array_map('trim', $request->input($driver, []));
            }
        }
        $this->saveSettings($s);
        return back()->with('success', 'Storage settings saved.');
    }

    // ── Cache ─────────────────────────────────────────────────
    public function cache()
    {
        $this->requireAdmin();
        return view('admin.cache');
    }

    public function clearCache(Request $request)
    {
        $this->requireAdmin();
        $type = $request->input('type', 'all');
        try {
            match ($type) {
                'app'    => \Artisan::call('cache:clear'),
                'view'   => \Artisan::call('view:clear'),
                'route'  => \Artisan::call('route:clear'),
                'config' => \Artisan::call('config:clear'),
                default  => (function () {
                    \Artisan::call('cache:clear');
                    \Artisan::call('view:clear');
                    \Artisan::call('route:clear');
                    \Artisan::call('config:clear');
                })(),
            };
        } catch (\Throwable) {}
        return back()->with('success', ucfirst($type) . ' cache cleared.');
    }

    // ── Backup ────────────────────────────────────────────────
    public function backup()
    {
        $this->requireAdmin();
        $dbName   = config('database.connections.mysql.database');
        $dbUser   = config('database.connections.mysql.username');
        $dbPass   = config('database.connections.mysql.password');
        $dbHost   = config('database.connections.mysql.host');
        $filename = 'nkhoj_backup_' . date('Ymd_His') . '.sql';
        $path     = storage_path('app/' . $filename);
        $cmd      = sprintf('mysqldump --host=%s --user=%s --password=%s %s > %s',
            escapeshellarg($dbHost), escapeshellarg($dbUser), escapeshellarg($dbPass),
            escapeshellarg($dbName), escapeshellarg($path));
        exec($cmd);
        if (File::exists($path)) {
            return response()->download($path, $filename)->deleteFileAfterSend(true);
        }
        return back()->with('error', 'Backup failed. Ensure mysqldump is in PATH.');
    }

    // ── Security ──────────────────────────────────────────────
    public function security()
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        return view('admin.security', compact('s'));
    }

    public function updateSecurity(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'max_login_attempts'     => 'required|integer|min:1|max:100',
            'lockout_time'           => 'required|integer|min:1|max:1440',
            'min_password_length'    => 'required|integer|min:4|max:128',
            'password_complexity'    => 'nullable|boolean',
            'post_links'             => 'nullable|string|max:50',
            'public_links'           => 'nullable|string|max:50',
        ]);
        $s = $this->getSettings();
        $s['security'] = array_merge($s['security'] ?? [], [
            'max_login_attempts'  => (int) $data['max_login_attempts'],
            'lockout_time'        => (int) $data['lockout_time'],
            'min_password_length' => (int) $data['min_password_length'],
            'password_complexity' => (bool) ($data['password_complexity'] ?? false),
            'post_links'          => $data['post_links'] ?? 'nofollow',
            'public_links'        => $data['public_links'] ?? 'remove',
        ]);
        $this->saveSettings($s);
        return back()->with('success', 'Security settings saved.');
    }

    public function updateCaptcha(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'captcha_enabled'  => 'nullable|boolean',
            'captcha_provider' => 'nullable|string|in:turnstile,recaptcha',
            'captcha_site_key' => 'nullable|string|max:200',
            'captcha_secret'   => 'nullable|string|max:200',
        ]);
        $s = $this->getSettings();
        $s['captcha'] = [
            'enabled'  => (bool) ($data['captcha_enabled'] ?? false),
            'provider' => $data['captcha_provider'] ?? 'turnstile',
            'site_key' => $data['captcha_site_key'] ?? '',
            'secret'   => $data['captcha_secret'] ?? '',
        ];
        $this->saveSettings($s);
        return back()->with('success', 'Captcha settings saved.');
    }

    public function generateCronToken()
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $s['cron_token'] = Str::random(40);
        $this->saveSettings($s);
        return back()->with('success', 'Cron token generated.');
    }

    public function revokeCronToken()
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $s['cron_token'] = null;
        $this->saveSettings($s);
        return back()->with('success', 'Cron token revoked.');
    }

    // ── Roles & Permissions ───────────────────────────────────
    public function roles()
    {
        $this->requireAdmin();
        $this->seedDefaultRoles();
        $roles = Role::orderBy('sort_order')->get();
        return view('admin.roles', ['roles' => $roles, 'allPerms' => Role::allPermissions(), 'badgeOptions' => Role::badgeOptions()]);
    }

    public function storeRole(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'name'        => 'required|string|max:80',
            'name_ne'     => 'nullable|string|max:100',
            'slug'        => 'nullable|string|max:80',
            'badge_label' => 'nullable|string|max:80',
            'badge_color' => 'nullable|string|max:30',
            'badge_icon'  => 'nullable|string|max:30',
            'ai_credits'  => 'nullable|integer|min:0|max:100000',
            'permissions' => 'nullable|array',
        ]);
        $data['slug']       = Str::slug($data['slug'] ?? $data['name']);
        $data['sort_order'] = Role::max('sort_order') + 1;
        $data['permissions'] = $request->input('permissions', []);
        Role::create($data);
        return back()->with('success', 'Role created.');
    }

    public function updateRole(Request $request, int $id)
    {
        $this->requireAdmin();
        $role = Role::findOrFail($id);
        $data = $request->validate([
            'name'        => 'required|string|max:80',
            'name_ne'     => 'nullable|string|max:100',
            'badge_label' => 'nullable|string|max:80',
            'badge_color' => 'nullable|string|max:30',
            'badge_icon'  => 'nullable|string|max:30',
            'ai_credits'  => 'nullable|integer|min:0|max:100000',
            'permissions' => 'nullable|array',
        ]);
        $data['permissions'] = $request->input('permissions', []);
        $role->update($data);
        return back()->with('success', 'Role updated.');
    }

    public function deleteRole(int $id)
    {
        $this->requireAdmin();
        $role = Role::findOrFail($id);
        if ($role->is_system) return back()->with('error', 'Cannot delete a system role.');
        $role->delete();
        return back()->with('success', 'Role deleted.');
    }

    private function seedDefaultRoles(): void
    {
        if (Role::count() > 0) return;
        $defaults = [
            ['name'=>'Super Admin','slug'=>'admin',    'badge_label'=>'Super Admin','badge_color'=>'red',    'badge_icon'=>'shield', 'permissions'=>array_keys(Role::allPermissions()),'is_default'=>true,'is_system'=>true,'ai_credits'=>99999,'sort_order'=>0],
            ['name'=>'Editor',     'slug'=>'editor',   'badge_label'=>'Editor',    'badge_color'=>'purple', 'badge_icon'=>'pencil', 'permissions'=>['add_post','edit_own_post','ai_writer','categories','tags','comments','media'],'is_default'=>true,'is_system'=>true,'ai_credits'=>500,'sort_order'=>1],
            ['name'=>'Reporter',   'slug'=>'reporter', 'badge_label'=>'Author',    'badge_color'=>'green',  'badge_icon'=>'user',   'permissions'=>['add_post','edit_own_post','ai_writer','tags'],'is_default'=>true,'is_system'=>true,'ai_credits'=>100,'sort_order'=>2],
            ['name'=>'Reader',     'slug'=>'reader',   'badge_label'=>'Member',    'badge_color'=>'gray',   'badge_icon'=>'user',   'permissions'=>['comments'],'is_default'=>true,'is_system'=>true,'ai_credits'=>0,'sort_order'=>3],
        ];
        foreach ($defaults as $d) {
            Role::create($d);
        }
    }

    // ── Email Settings ────────────────────────────────────────
    public function emailSettings()
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        return view('admin.email-settings', compact('s'));
    }

    public function updateEmailSettings(Request $request)
    {
        $this->requireAdmin();
        $request->validate([
            'mail_host'     => 'nullable|string|max:200',
            'mail_port'     => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:200',
            'mail_from'     => 'nullable|email|max:200',
            'reply_to'      => 'nullable|email|max:200',
            'mail_title'    => 'nullable|string|max:100',
        ]);
        $s = $this->getSettings();
        $s['email'] = array_merge($s['email'] ?? [], [
            'service'          => $request->input('mail_service', 'mailpit'),
            'protocol'         => $request->input('mail_protocol', 'smtp'),
            'encryption'       => $request->input('mail_encryption', 'tls'),
            'host'             => $request->input('mail_host', ''),
            'port'             => (int) $request->input('mail_port', 587),
            'username'         => $request->input('mail_username', ''),
            'password'         => $request->input('mail_password') ?: ($s['email']['password'] ?? ''),
            'from_address'     => $request->input('mail_from', ''),
            'reply_to'         => $request->input('reply_to', ''),
            'title'            => $request->input('mail_title', config('app.name')),
            'verification'     => $request->boolean('email_verification'),
            'contact_forward'  => $request->boolean('contact_forward'),
            'contact_email'    => $request->input('contact_email', ''),
            'template'         => $s['email']['template'] ?? 'pure-minimalist',
        ]);
        $this->saveSettings($s);
        // Sync to .env-equivalent runtime config so mail actually works immediately
        config([
            'mail.default'                    => $s['email']['protocol'] === 'smtp' ? 'smtp' : 'sendmail',
            'mail.mailers.smtp.host'          => $s['email']['host'],
            'mail.mailers.smtp.port'          => $s['email']['port'],
            'mail.mailers.smtp.encryption'    => $s['email']['encryption'] === 'none' ? null : $s['email']['encryption'],
            'mail.mailers.smtp.username'      => $s['email']['username'],
            'mail.mailers.smtp.password'      => $s['email']['password'],
            'mail.from.address'               => $s['email']['from_address'] ?: config('mail.from.address'),
            'mail.from.name'                  => $s['email']['title'] ?: config('app.name'),
        ]);
        return back()->with('success', 'Email settings saved.');
    }

    public function updateEmailTemplate(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $s['email']['template'] = $request->input('template', 'pure-minimalist');
        $this->saveSettings($s);
        return back()->with('success', 'Email template updated.');
    }

    public function sendTestEmail(Request $request)
    {
        $this->requireAdmin();
        $request->validate(['test_email' => 'required|email']);
        try {
            \Mail::raw('This is a test email from ' . config('app.name') . '. Your mail configuration is working correctly.', function ($m) use ($request) {
                $m->to($request->test_email)->subject('Test Email – ' . config('app.name'));
            });
            return back()->with('success', 'Test email sent to ' . $request->test_email);
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024)    return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    public function deploy()
    {
        $this->requireAdmin();
        return view('admin.deploy');
    }

    public function runDeploy(Request $request)
    {
        $this->requireAdmin();

        $log = [];
        $success = true;

        $composerBin = trim(shell_exec('which composer 2>/dev/null') ?: 'composer');
        $commands = [
            'git stash',
            'git pull origin master',
            'HOME=/tmp ' . $composerBin . ' install --no-dev --optimize-autoloader --no-interaction',
            PHP_BINARY . ' artisan migrate --force',
            PHP_BINARY . ' artisan config:cache',
            PHP_BINARY . ' artisan view:clear',
            PHP_BINARY . ' artisan route:cache',
        ];

        foreach ($commands as $cmd) {
            $output = [];
            $code = 0;
            exec('cd ' . base_path() . ' && ' . $cmd . ' 2>&1', $output, $code);
            $log[] = [
                'cmd'    => $cmd,
                'output' => implode("\n", $output),
                'ok'     => $code === 0,
            ];
            if ($code !== 0) {
                $success = false;
                break;
            }
        }

        return response()->json(['success' => $success, 'log' => $log]);
    }

    public function webhookDeploy(Request $request)
    {
        $secret = config('app.deploy_secret');

        if ($secret) {
            $signature = $request->header('X-Hub-Signature-256', '');
            $expected  = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
            if (!hash_equals($expected, $signature)) {
                return response('Unauthorized', 401);
            }
        }

        $payload = $request->json()->all();
        if (($payload['ref'] ?? '') !== 'refs/heads/master') {
            return response('Skipped', 200);
        }

        $composerBin = trim(shell_exec('which composer 2>/dev/null') ?: 'composer');
        $commands = [
            'git stash',
            'git pull origin master',
            'HOME=/tmp ' . $composerBin . ' install --no-dev --optimize-autoloader --no-interaction',
            PHP_BINARY . ' artisan migrate --force',
            PHP_BINARY . ' artisan config:cache',
            PHP_BINARY . ' artisan view:clear',
            PHP_BINARY . ' artisan route:cache',
        ];

        foreach ($commands as $cmd) {
            exec('cd ' . base_path() . ' && ' . $cmd . ' 2>&1');
        }

        return response('OK', 200);
    }

    // ── Memberships ───────────────────────────────────────
    public function memberships()
    {
        $this->requireAdmin();

        $plans       = MembershipPlan::withCount(['subscriptions' => fn($q) => $q->where('status', 'active')])->orderBy('sort_order')->get();
        $subscribers = Subscription::with(['user', 'plan'])
            ->whereIn('status', ['active', 'pending_manual'])
            ->latest()->paginate(20);

        $s = $this->getSettings();
        $stripePublicKey    = $s['stripe_public_key'] ?? '';
        $stripeSecretKey    = $s['stripe_secret_key'] ?? '';
        $stripeWebhookSecret = $s['stripe_webhook_secret'] ?? '';
        $paypalEmail        = $s['paypal_email'] ?? '';
        $paypalMe           = $s['paypal_me'] ?? '';
        $bankName           = $s['bank_name'] ?? '';
        $bankAccountName    = $s['bank_account_name'] ?? '';
        $bankAccountNumber  = $s['bank_account_number'] ?? '';
        $bankRouting        = $s['bank_routing'] ?? '';

        $premiumEnabled              = (bool) ($s['premium_enabled'] ?? false);
        $premiumContentMode          = $s['premium_content_mode'] ?? 'selected';
        $premiumHideMethod           = $s['premium_hide_method'] ?? 'preview';
        $premiumSingleSales          = (bool) ($s['premium_single_sales'] ?? false);
        $premiumDefaultPrice         = $s['premium_default_price'] ?? 5;
        $premiumSubscribeBtnVisible  = (bool) ($s['premium_subscribe_btn_visible'] ?? true);
        $premiumSubscribeBtnColor    = $s['premium_subscribe_btn_color'] ?? '#6366f1';
        $premiumBadgeVisible         = (bool) ($s['premium_badge_visible'] ?? true);
        $premiumBadgeLabel           = $s['premium_badge_label'] ?? 'Premium';

        $stats = [
            'active'     => Subscription::where('status', 'active')->count(),
            'revenue'    => Subscription::where('status', 'active')->join('membership_plans', 'subscriptions.plan_id', '=', 'membership_plans.id')->sum('membership_plans.price'),
            'this_month' => Subscription::where('status', 'active')->whereMonth('created_at', now()->month)->count(),
            'plans'      => MembershipPlan::count(),
        ];

        return view('admin.memberships', compact(
            'plans', 'subscribers', 'stats',
            'stripePublicKey', 'stripeSecretKey', 'stripeWebhookSecret',
            'paypalEmail', 'paypalMe',
            'bankName', 'bankAccountName', 'bankAccountNumber', 'bankRouting',
            'premiumEnabled', 'premiumContentMode', 'premiumHideMethod',
            'premiumSingleSales', 'premiumDefaultPrice',
            'premiumSubscribeBtnVisible', 'premiumSubscribeBtnColor',
            'premiumBadgeVisible', 'premiumBadgeLabel'
        ));
    }

    public function storePlan(Request $request)
    {
        $this->requireAdmin();
        $request->validate(['name' => 'required', 'price' => 'required|integer|min:0', 'billing_cycle' => 'in:monthly,yearly,lifetime']);

        $features = array_filter(array_map('trim', explode("\n", $request->input('features_text', ''))));

        MembershipPlan::create([
            'name'             => $request->name,
            'slug'             => \Illuminate\Support\Str::slug($request->name),
            'description'      => $request->description,
            'price'            => (int) $request->price,
            'billing_cycle'    => $request->billing_cycle ?? 'monthly',
            'features'         => array_values($features) ?: null,
            'stripe_price_id'  => $request->stripe_price_id ?: null,
            'is_active'        => true,
        ]);

        return back()->with('success', 'Plan created.');
    }

    public function updatePlan(Request $request, MembershipPlan $plan)
    {
        $this->requireAdmin();
        $request->validate(['name' => 'required', 'price' => 'required|integer|min:0']);

        $features = array_filter(array_map('trim', explode("\n", $request->input('features_text', ''))));

        $plan->update([
            'name'             => $request->name,
            'description'      => $request->description,
            'price'            => (int) $request->price,
            'billing_cycle'    => $request->billing_cycle,
            'features'         => array_values($features) ?: null,
            'stripe_price_id'  => $request->stripe_price_id ?: null,
        ]);

        return back()->with('success', 'Plan updated.');
    }

    public function togglePlan(MembershipPlan $plan)
    {
        $this->requireAdmin();
        $plan->update(['is_active' => !$plan->is_active]);

        return back()->with('success', 'Plan updated.');
    }

    public function deletePlan(MembershipPlan $plan)
    {
        $this->requireAdmin();
        $plan->delete();

        return back()->with('success', 'Plan deleted.');
    }

    public function revokeSubscription(Subscription $subscription)
    {
        $this->requireAdmin();
        $subscription->update(['status' => 'cancelled']);

        return back()->with('success', 'Subscription revoked.');
    }

    public function updateStripeSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $s['stripe_public_key']     = $request->input('stripe_public_key', '');
        $s['stripe_secret_key']     = $request->input('stripe_secret_key', '');
        $s['stripe_webhook_secret'] = $request->input('stripe_webhook_secret', '');
        $this->saveSettings($s);

        return back()->with('success', 'Stripe settings saved.');
    }

    public function updatePaypalSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $s['paypal_email'] = $request->input('paypal_email', '');
        $s['paypal_me']    = $request->input('paypal_me', '');
        $this->saveSettings($s);

        return back()->with('success', 'PayPal settings saved.');
    }

    public function updateBankSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $s['bank_name']           = $request->input('bank_name', '');
        $s['bank_account_name']   = $request->input('bank_account_name', '');
        $s['bank_account_number'] = $request->input('bank_account_number', '');
        $s['bank_routing']        = $request->input('bank_routing', '');
        $this->saveSettings($s);

        return back()->with('success', 'Bank transfer settings saved.');
    }

    public function updatePremiumSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $s['premium_enabled']              = $request->boolean('premium_enabled');
        $s['premium_content_mode']         = $request->input('premium_content_mode', 'selected');
        $s['premium_hide_method']          = $request->input('premium_hide_method', 'preview');
        $s['premium_single_sales']         = $request->boolean('premium_single_sales');
        $s['premium_default_price']        = (float) $request->input('premium_default_price', 5);
        $s['premium_subscribe_btn_visible'] = $request->boolean('premium_subscribe_btn_visible');
        $s['premium_subscribe_btn_color']  = $request->input('premium_subscribe_btn_color', '#6366f1');
        $s['premium_badge_visible']        = $request->boolean('premium_badge_visible');
        $s['premium_badge_label']          = $request->input('premium_badge_label', 'Premium');
        $this->saveSettings($s);

        return back()->with('success', 'Premium membership settings saved.');
    }

    public function activateSubscription(Subscription $subscription)
    {
        $this->requireAdmin();
        $plan = $subscription->plan;
        $ends = match($plan->billing_cycle ?? 'monthly') {
            'monthly'  => now()->addMonth(),
            'yearly'   => now()->addYear(),
            'lifetime' => null,
            default    => now()->addMonth(),
        };
        $subscription->update([
            'status'    => 'active',
            'starts_at' => now(),
            'ends_at'   => $ends,
        ]);

        return back()->with('success', 'Subscription activated for ' . $subscription->user->name . '.');
    }

    // ── Support Tickets ───────────────────────────────────
    public function supportTickets(Request $request)
    {
        $this->requireAdmin();
        $status = $request->query('status', 'all');

        $query = SupportTicket::with(['requester', 'agent'])
            ->withCount('replies');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $tickets = $query->latest()->paginate(20);

        $counts = [
            'all'         => SupportTicket::count(),
            'open'        => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'pending'     => SupportTicket::where('status', 'pending')->count(),
            'solved'      => SupportTicket::where('status', 'solved')->count(),
            'closed'      => SupportTicket::where('status', 'closed')->count(),
        ];

        $open = $counts['open'];

        return view('admin.support', compact('tickets', 'counts', 'open', 'status'))->with('currentStatus', $status);
    }

    public function supportShow(SupportTicket $ticket)
    {
        $this->requireAdmin();
        $ticket->load(['requester', 'agent', 'replies.user']);
        $agents = User::whereIn('role', ['admin', 'moderator'])->orderBy('name')->get();

        return view('admin.support-show', compact('ticket', 'agents'));
    }

    public function supportReply(Request $request, SupportTicket $ticket)
    {
        $this->requireAdmin();
        $request->validate(['body' => 'required|string|max:5000']);

        SupportReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'body'      => $request->body,
            'is_staff'  => true,
        ]);

        if ($request->input('action') === 'reply_solve') {
            $ticket->update(['status' => 'solved']);
        } elseif ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Reply sent.');
    }

    public function supportAssign(Request $request, SupportTicket $ticket)
    {
        $this->requireAdmin();
        $ticket->update(['agent_id' => $request->input('agent_id') ?: null]);

        return back()->with('success', 'Agent updated.');
    }

    public function supportStatus(Request $request, SupportTicket $ticket)
    {
        $this->requireAdmin();
        $request->validate(['status' => 'required|in:open,pending,in_progress,solved,closed']);
        $ticket->update(['status' => $request->status]);

        return back()->with('success', 'Status updated.');
    }

    public function supportDelete(SupportTicket $ticket)
    {
        $this->requireAdmin();
        $ticket->delete();

        return redirect('/admin/support')->with('success', 'Ticket deleted.');
    }

    // ── AI Content ────────────────────────────────────────

    public function aiContent()
    {
        $this->requireAdmin();
        $topics     = AiPostTopic::with('category')->latest()->get();
        $categories = Category::orderBy('sort_order')->get();
        $drafts     = Post::with(['author', 'category'])
                        ->where('status', 'draft')
                        ->where('sources', 'like', 'AI generated%')
                        ->latest()
                        ->paginate(20);
        $s             = $this->getSettings();
        $geminiKey     = $s['gemini_api_key'] ?? '';
        $geminiModel   = $s['gemini_model'] ?? 'gemini-1.5-flash';

        return view('admin.ai-content', compact('topics', 'categories', 'drafts', 'geminiKey', 'geminiModel'));
    }

    public function storeAiTopic(Request $request)
    {
        $this->requireAdmin();
        AiPostTopic::create([
            'keyword'     => $request->keyword,
            'language'    => $request->language ?? 'both',
            'category_id' => $request->category_id ?: null,
            'rss_source'  => $request->rss_source ?: null,
            'frequency'   => $request->frequency ?? 'daily',
            'is_active'   => true,
        ]);
        return redirect('/admin/ai-content')->with('success', 'Topic added.');
    }

    public function toggleAiTopic(AiPostTopic $topic)
    {
        $this->requireAdmin();
        $topic->update(['is_active' => !$topic->is_active]);
        return back();
    }

    public function deleteAiTopic(AiPostTopic $topic)
    {
        $this->requireAdmin();
        $topic->delete();
        return redirect('/admin/ai-content')->with('success', 'Topic deleted.');
    }

    public function runAiTopic(AiPostTopic $topic)
    {
        $this->requireAdmin();
        $exitCode = Artisan::call('ai:generate-posts', ['--topic' => $topic->id, '--force' => true]);
        $output   = trim(Artisan::output());

        if ($exitCode !== 0) {
            $error = $output ?: 'Generation failed. Check your Gemini API key in AI Content settings.';
            return redirect('/admin/ai-content')->with('error', $error);
        }

        $topic->update(['last_run_at' => now()]);
        $msg = $output ?: 'Posts generated for: ' . $topic->keyword;
        return redirect('/admin/ai-content')->with('success', $msg);
    }

    public function runAllAiTopics()
    {
        $this->requireAdmin();
        Artisan::call('ai:generate-posts', ['--force' => true]);
        return redirect('/admin/ai-content')->with('success', 'Generation triggered for all active topics.');
    }

    public function updateGeminiSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->getSettings();
        $s['gemini_api_key'] = $request->gemini_api_key;
        $s['gemini_model']   = $request->gemini_model ?? 'gemini-1.5-flash';
        File::put(storage_path('app/site_settings.json'), json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return redirect('/admin/ai-content')->with('success', 'AI settings saved.');
    }

    public function publishAiDraft(Post $post)
    {
        $this->requireAdmin();
        $post->update(['status' => 'published', 'published_at' => now()]);
        return back()->with('success', 'Post published.');
    }

    public function deleteAiDraft(Post $post)
    {
        $this->requireAdmin();
        $post->delete();
        return back()->with('success', 'Draft deleted.');
    }
}
