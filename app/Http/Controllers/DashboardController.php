<?php

namespace App\Http\Controllers;

use App\Jobs\CreateNotification;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DashboardController extends Controller
{
    public function index()
    {
        $posts = Post::where('author_id', auth()->id())->latest()->paginate(20);
        $stats = [
            'total'     => Post::where('author_id', auth()->id())->count(),
            'published' => Post::where('author_id', auth()->id())->where('status', 'published')->count(),
            'drafts'    => Post::where('author_id', auth()->id())->where('status', 'draft')->count(),
            'views'     => Post::where('author_id', auth()->id())->sum('view_count'),
        ];
        return view('dashboard.index', compact('posts', 'stats'));
    }

    public function analytics()
    {
        $userId = auth()->id();

        $totalViews = Post::where('author_id', $userId)->sum('view_count');
        $totalPosts = Post::where('author_id', $userId)->where('status', 'published')->count();

        $topPosts = Post::where('author_id', $userId)
            ->where('status', 'published')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get(['id', 'title', 'slug', 'view_count', 'published_at']);

        // Views by month (last 6 months)
        $viewsByMonth = Post::where('author_id', $userId)
            ->where('status', 'published')
            ->where('published_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(published_at, "%Y-%m") as month'),
                DB::raw('SUM(view_count) as views'),
                DB::raw('COUNT(*) as posts')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $commentCount = 0;
        try {
            $commentCount = Comment::whereIn('post_id',
                Post::where('author_id', $userId)->pluck('id')
            )->count();
        } catch (\Throwable) {}

        $scheduled = Post::where('author_id', $userId)->where('status', 'scheduled')->count();
        $drafts    = Post::where('author_id', $userId)->where('status', 'draft')->count();

        return view('dashboard.analytics', compact(
            'totalViews', 'totalPosts', 'topPosts',
            'viewsByMonth', 'commentCount', 'scheduled', 'drafts'
        ));
    }

    public function create()
    {
        $categories  = Category::orderBy('sort_order')->get();
        $allTags     = Tag::orderBy('name_en')->get();
        $postFormat  = request('format', 'article');
        $validFormats = ['article','sorted_list','table_of_contents','trivia_quiz','personality_quiz','poll','recipe','event'];
        if (!in_array($postFormat, $validFormats)) $postFormat = 'article';

        if ($postFormat === 'event') {
            return view('dashboard.create-event', compact('categories', 'allTags'));
        }
        if ($postFormat === 'recipe') {
            return view('dashboard.create-recipe', compact('categories', 'allTags'));
        }
        if ($postFormat === 'poll') {
            return view('dashboard.create-poll', compact('categories', 'allTags'));
        }
        return view('dashboard.create', compact('categories', 'allTags', 'postFormat'));
    }

    public function store(Request $request)
    {
        $isEvent = $request->input('post_format') === 'event';

        $rules = [
            'title'         => 'required|string|max:300',
            'excerpt'       => 'nullable|string|max:1000',
            'body'          => 'nullable|string',
            'category_id'   => 'required|exists:categories,id',
            'status'        => 'in:draft,published',
            'post_format'   => 'nullable|string|max:50',
            'seo_title'     => 'nullable|string|max:160',
            'seo_desc'      => 'nullable|string|max:320',
            'seo_keywords'  => 'nullable|string|max:300',
            'thumbnail_url'    => 'nullable|string|max:500',
            'thumbnail'        => 'nullable|image|max:4096',
            'tags'             => 'nullable|string',
            'sources'          => 'nullable|array',
            'sources.*.label'  => 'nullable|string|max:200',
            'sources.*.url'    => 'nullable|string|max:500',
            'article_faq_q'   => 'nullable|array',
            'article_faq_a'   => 'nullable|array',
        ];

        if ($isEvent) {
            $rules = array_merge($rules, [
                'event_start_at'          => 'nullable|date',
                'event_end_at'            => 'nullable|date',
                'event_organizer'         => 'nullable|string|max:200',
                'event_venue'             => 'nullable|string|max:200',
                'event_address'           => 'nullable|string',
                'event_lat'               => 'nullable|numeric',
                'event_lng'               => 'nullable|numeric',
                'event_registration_type' => 'nullable|string|max:50',
                'event_schedule'          => 'nullable|array',
                'event_schedule.*'        => 'nullable|string',
                'event_highlights'        => 'nullable|array',
                'event_highlights.*'      => 'nullable|string',
                'event_speakers'          => 'nullable|array',
                'event_speakers.*'        => 'nullable|string',
                'event_faq_q'             => 'nullable|array',
                'event_faq_a'             => 'nullable|array',
            ]);
        }

        $data = $request->validate($rules);

        $thumbnailUrl = $data['thumbnail_url'] ?? null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailUrl = $this->saveOptimizedThumbnail($request->file('thumbnail'));
        }

        $slug = Str::slug($data['title']);
        $base = $slug ?: 'post';
        $i = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}"; $i++;
        }

        $bodyBlocks = $this->textToBlocks($data['body'] ?? '');

        $faqQ = $request->input('article_faq_q', []);
        $faqA = $request->input('article_faq_a', []);
        $articleFaq = [];
        foreach ($faqQ as $idx => $q) {
            if (trim($q)) $articleFaq[] = ['q' => $q, 'a' => $faqA[$idx] ?? ''];
        }

        $sources = array_values(array_filter($request->input('sources', []), fn($s) => !empty($s['label']) || !empty($s['url'])));

        $postData = [
            'uuid'          => Str::uuid(),
            'author_id'     => auth()->id(),
            'category_id'   => $data['category_id'],
            'slug'          => $slug,
            'title'         => $data['title'],
            'excerpt'       => $data['excerpt'] ?? null,
            'body'          => $bodyBlocks,
            'status'        => $data['status'] ?? 'draft',
            'post_format'   => $data['post_format'] ?? 'article',
            'seo_title'     => $data['seo_title'] ?? null,
            'seo_desc'      => $data['seo_desc'] ?? null,
            'thumbnail_url' => $thumbnailUrl,
            'published_at'  => (($data['status'] ?? 'draft') === 'published') ? now() : null,
            'sources'       => $sources ?: null,
            'article_faq'   => $articleFaq ?: null,
        ];

        if ($isEvent) {
            // Build FAQ array from parallel q/a arrays
            $faqQ = $request->input('event_faq_q', []);
            $faqA = $request->input('event_faq_a', []);
            $faq  = [];
            foreach ($faqQ as $i => $q) {
                if (trim($q)) $faq[] = ['q' => $q, 'a' => $faqA[$i] ?? ''];
            }

            $postData = array_merge($postData, [
                'event_start_at'          => $data['event_start_at'] ?? null,
                'event_end_at'            => $data['event_end_at'] ?? null,
                'event_organizer'         => $data['event_organizer'] ?? null,
                'event_venue'             => $data['event_venue'] ?? null,
                'event_address'           => $data['event_address'] ?? null,
                'event_lat'               => $data['event_lat'] ?? null,
                'event_lng'               => $data['event_lng'] ?? null,
                'event_registration_type' => $data['event_registration_type'] ?? 'none',
                'event_schedule'          => array_filter($request->input('event_schedule', []), 'trim'),
                'event_highlights'        => array_filter($request->input('event_highlights', []), 'trim'),
                'event_speakers'          => array_filter($request->input('event_speakers', []), 'trim'),
                'event_faq'               => $faq ?: null,
            ]);
        }

        $post = Post::create($postData);
        $this->syncTags($post, $data['tags'] ?? '');

        if ($post->status === 'published') {
            $this->notifyFollowersOfNewPost($post);
        }

        $label = $isEvent ? 'Event' : 'Article';
        return redirect('/dashboard')->with('success', "{$label} saved!");
    }

    public function edit(int $id)
    {
        $post       = Post::with('tags')->where('author_id', auth()->id())->findOrFail($id);
        $categories = Category::orderBy('sort_order')->get();
        $allTags    = Tag::orderBy('name_en')->get();

        $bodyText = is_array($post->body)
            ? implode("\n\n", array_column($post->body, 'content'))
            : ($post->body ?? '');

        $currentTags = $post->tags->pluck('name_en')->implode(', ');

        return view('dashboard.edit', compact('post', 'categories', 'bodyText', 'allTags', 'currentTags'));
    }

    public function update(Request $request, int $id)
    {
        $post = Post::where('author_id', auth()->id())->findOrFail($id);

        $data = $request->validate([
            'title'          => 'required|string|max:300',
            'slug'           => 'nullable|string|max:300',
            'excerpt'        => 'nullable|string|max:500',
            'body'           => 'nullable|string',
            'category_id'   => 'required|exists:categories,id',
            'status'         => 'in:draft,published,scheduled,archived',
            'visibility'     => 'nullable|in:public,members,private',
            'scheduled_at'   => 'nullable|date',
            'seo_title'      => 'nullable|string|max:160',
            'seo_desc'       => 'nullable|string|max:320',
            'thumbnail_url'  => 'nullable|string|max:500',
            'thumbnail'      => 'nullable|image|max:4096',
            'image_caption'  => 'nullable|string|max:300',
            'tags'           => 'nullable|string',
            'is_featured'    => 'nullable|boolean',
            'is_breaking'    => 'nullable|boolean',
            'is_slider'      => 'nullable|boolean',
            'is_recommended' => 'nullable|boolean',
            'is_pro'         => 'nullable|boolean',
            'article_faq_q'    => 'nullable|array',
            'article_faq_a'    => 'nullable|array',
            'sources'          => 'nullable|array',
            'sources.*.label'  => 'nullable|string|max:200',
            'sources.*.url'    => 'nullable|string|max:500',
        ]);

        $thumbnailUrl = $data['thumbnail_url'] ?? $post->thumbnail_url;
        if ($request->hasFile('thumbnail')) {
            $thumbnailUrl = $this->saveOptimizedThumbnail($request->file('thumbnail'));
        }

        $faqQ = $request->input('article_faq_q', []);
        $faqA = $request->input('article_faq_a', []);
        $articleFaq = [];
        foreach ($faqQ as $i => $q) {
            if (trim($q)) $articleFaq[] = ['q' => $q, 'a' => $faqA[$i] ?? ''];
        }

        $sources = array_values(array_filter($request->input('sources', []), fn($s) => !empty($s['label']) || !empty($s['url'])));

        $slug = $data['slug'] ?? $post->slug;
        if ($slug !== $post->slug) {
            $slug = Str::slug($slug) ?: $post->slug;
            $base = $slug; $i = 1;
            while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = "{$base}-{$i}"; $i++;
            }
        }

        $post->update([
            'slug'           => $slug,
            'category_id'    => $data['category_id'],
            'title'          => $data['title'],
            'excerpt'        => $data['excerpt'] ?? null,
            'body'           => $this->textToBlocks($data['body'] ?? ''),
            'status'         => $data['status'],
            'visibility'     => $data['visibility'] ?? 'public',
            'scheduled_at'   => $data['scheduled_at'] ?? null,
            'seo_title'      => $data['seo_title'] ?? null,
            'seo_desc'       => $data['seo_desc'] ?? null,
            'thumbnail_url'  => $thumbnailUrl,
            'image_caption'  => $data['image_caption'] ?? null,
            'is_featured'    => (bool) ($request->input('is_featured', 0)),
            'is_breaking'    => (bool) ($request->input('is_breaking', 0)),
            'is_slider'      => (bool) ($request->input('is_slider', 0)),
            'is_recommended' => (bool) ($request->input('is_recommended', 0)),
            'is_pro'         => (bool) ($request->input('is_pro', 0)),
            'article_faq'    => $articleFaq ?: null,
            'sources'        => $sources ?: null,
            'published_at'   => ($data['status'] === 'published' && !$post->published_at) ? now() : $post->published_at,
        ]);

        $this->syncTags($post, $data['tags'] ?? '');

        if ($post->wasChanged('status') && $post->status === 'published') {
            $this->notifyFollowersOfNewPost($post);
        }

        return redirect('/dashboard')->with('success', 'Article updated!');
    }

    private function notifyFollowersOfNewPost(Post $post): void
    {
        $author   = auth()->user();
        $followers = $author->followers()->pluck('users.id');
        $payload  = [
            'post_slug'    => $post->slug,
            'post_title'   => $post->title,
            'author_name'  => $author->name,
            'author_username' => $author->username,
        ];
        foreach ($followers as $followerId) {
            CreateNotification::dispatch($followerId, 'new_post', $payload);
        }
    }

    private function textToBlocks(string $text): array
    {
        $blocks = [];
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line) {
                $blocks[] = ['type' => 'paragraph', 'content' => $line];
            }
        }
        return $blocks;
    }

    private function syncTags(Post $post, string $tagInput): void
    {
        $names = array_filter(array_map('trim', explode(',', $tagInput)));
        $tagIds = [];
        foreach ($names as $name) {
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name_en' => $name, 'name_ne' => $name]
            );
            $tagIds[] = $tag->id;
        }
        $post->tags()->sync($tagIds);
    }

    private function saveOptimizedThumbnail(\Illuminate\Http\UploadedFile $file): string
    {
        $filename  = time() . '_' . Str::random(8) . '.webp';
        $destPath  = public_path('uploads/' . $filename);

        try {
            $manager = new ImageManager(new Driver());
            $manager->read($file->getRealPath())
                ->scaleDown(1200, 675)   // max 16:9 at 1200px wide
                ->toWebp(80)
                ->save($destPath);
        } catch (\Throwable) {
            // Fallback: plain move without optimization
            $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $destPath = public_path('uploads/' . $filename);
            $file->move(public_path('uploads'), $filename);
        }

        return '/uploads/' . $filename;
    }
}
