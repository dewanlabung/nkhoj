<?php

namespace App\Http\Controllers;

use App\Models\SocialPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SocialPageController extends Controller
{
    // GET /pages — discover
    public function index()
    {
        $pages = SocialPage::where('is_active', true)
            ->orderByDesc('followers_count')
            ->paginate(20);

        $myPages = auth()->check()
            ? SocialPage::where('user_id', auth()->id())->latest()->get()
            : collect();

        return view('social-pages.discover', compact('pages', 'myPages'));
    }

    // GET /pages/start — intro splash
    public function intro()
    {
        return view('social-pages.intro');
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
            'name'      => 'required|string|max:150',
            'page_type' => 'required|in:creator,business',
            'categories'=> 'nullable|array|max:3',
            'categories.*' => 'nullable|string|max:100',
            'bio'       => 'nullable|string|max:500',
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
        ]);

        return redirect("/pages/{$page->slug}/dashboard")
            ->with('success', 'Your page has been created!');
    }

    // GET /pages/{slug} — public profile
    public function show(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $isOwner = $page->isOwnedBy(auth()->user());
        $isFollowing = $page->isFollowedBy(auth()->user());

        $posts = \App\Models\Post::where('author_id', $page->user_id)
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(12);

        return view('social-pages.show', compact('page', 'isOwner', 'isFollowing', 'posts'));
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

    // GET /pages/{slug}/dashboard — professional dashboard (owner only)
    public function dashboard(string $slug)
    {
        $page = SocialPage::where('slug', $slug)->where('user_id', auth()->id())->firstOrFail();
        return view('social-pages.dashboard', compact('page'));
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
            'name'       => 'required|string|max:150',
            'bio'        => 'nullable|string|max:500',
            'categories' => 'nullable|array|max:3',
            'categories.*' => 'nullable|string|max:100',
            'website'    => 'nullable|url|max:300',
            'email'      => 'nullable|email|max:150',
            'phone'      => 'nullable|string|max:50',
            'location'   => 'nullable|string|max:200',
            'avatar'     => 'nullable|image|max:2048',
            'cover'      => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_avatar.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pages'), $filename);
            $data['avatar_url'] = '/uploads/pages/' . $filename;
        }

        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            $filename = time() . '_cover.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pages'), $filename);
            $data['cover_url'] = '/uploads/pages/' . $filename;
        }

        $page->update(array_filter([
            'name'       => $data['name'],
            'bio'        => $data['bio'] ?? null,
            'categories' => array_filter($data['categories'] ?? []),
            'website'    => $data['website'] ?? null,
            'email'      => $data['email'] ?? null,
            'phone'      => $data['phone'] ?? null,
            'location'   => $data['location'] ?? null,
            'avatar_url' => $data['avatar_url'] ?? $page->avatar_url,
            'cover_url'  => $data['cover_url'] ?? $page->cover_url,
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
