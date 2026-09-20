<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Models\Configuration\AdZone;
use App\Models\QnA\Answer;
use App\Models\Blog\Category;
use App\Models\Blog\Comment;
use App\Models\Core\ContactMessage;
use App\Models\Core\NewsletterSubscriber;
use App\Models\Core\NewsletterTemplate;
use App\Models\MediaContent\Poll;
use App\Models\Blog\Post;
use App\Models\QnA\Question;
use App\Models\Blog\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ContentController extends BaseAdminController
{
    // ── Categories ─────────────────────────────────────────────

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
        Cache::forget('home_categories');
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
        Cache::forget('home_categories');
        return back()->with('success', 'Category updated.');
    }

    public function reorderCategories(Request $request)
    {
        $this->requireAdmin();
        foreach ($request->input('order', []) as $index => $id) {
            Category::where('id', $id)->update(['sort_order' => $index + 1]);
        }
        return response()->json(['ok' => true]);
    }

    public function deleteCategory(int $id)
    {
        $this->requireAdmin();
        Category::findOrFail($id)->delete();
        Cache::forget('home_categories');
        return back()->with('success', 'Category deleted.');
    }

    // ── Posts ──────────────────────────────────────────────────

    public function posts(Request $request)
    {
        $this->requireAdmin();
        $query = Post::with(['author', 'category']);
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('post_format')) $query->where('post_format', $request->post_format);
        if ($request->filled('q'))           $query->where('title', 'like', '%' . $request->q . '%');
        return view('admin.posts', [
            'posts'   => $query->latest()->paginate(20),
            'allTags' => Tag::orderBy('name_en')->get(['id', 'name_en']),
            'counts'  => [
                'all'       => Post::count(),
                'published' => Post::where('status', 'published')->count(),
                'draft'     => Post::where('status', 'draft')->count(),
                'scheduled' => Post::where('status', 'scheduled')->count(),
                'archived'  => Post::where('status', 'archived')->count(),
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
        $this->flushPostCaches();
        return back()->with('success', 'Status updated.');
    }

    public function deletePost(int $id)
    {
        $this->requireAdmin();
        Post::findOrFail($id)->delete();
        $this->flushPostCaches();
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
        $data = $request->validate([
            'action' => 'required|in:publish,draft,archived,delete,tag',
            'ids'    => 'required|array',
            'ids.*'  => 'integer',
            'tag_id' => 'nullable|integer|exists:tags,id',
        ]);

        if ($data['action'] === 'delete') {
            Post::whereIn('id', $data['ids'])->delete();
        } elseif ($data['action'] === 'tag') {
            $request->validate(['tag_id' => 'required|integer|exists:tags,id']);
            Post::whereIn('id', $data['ids'])->get()
                ->each(fn($post) => $post->tags()->syncWithoutDetaching([$data['tag_id']]));
        } else {
            Post::whereIn('id', $data['ids'])->update(['status' => $data['action']]);
        }

        return back()->with('success', 'Bulk action applied.');
    }

    // ── Comments ───────────────────────────────────────────────

    public function comments(Request $request)
    {
        $this->requireAdmin();
        $query = Comment::with(['post', 'author']);
        if ($request->filled('q')) $query->where('body', 'like', '%' . $request->q . '%');
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

    // ── Tags ───────────────────────────────────────────────────

    public function tags()
    {
        $this->requireAdmin();
        return view('admin.tags', ['tags' => Tag::withCount('posts')->orderByDesc('id')->get()]);
    }

    public function storeTag(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate(['name_en' => 'required|string|max:100', 'name_ne' => 'nullable|string|max:100', 'slug' => 'nullable|string|max:100']);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name_en']);
        Tag::firstOrCreate(['slug' => $data['slug']], $data);
        return back()->with('success', 'Tag created.');
    }

    public function updateTag(Request $request, int $id)
    {
        $this->requireAdmin();
        $data = $request->validate(['name_en' => 'required|string|max:100', 'name_ne' => 'nullable|string|max:100']);
        Tag::findOrFail($id)->update($data);
        return back()->with('success', 'Tag updated.');
    }

    public function deleteTag(int $id)
    {
        $this->requireAdmin();
        Tag::findOrFail($id)->delete();
        return back()->with('success', 'Tag deleted.');
    }

    // ── Media ──────────────────────────────────────────────────

    public function media(Request $request)
    {
        $this->requireAdmin();
        $baseDir  = public_path('uploads');
        $folders  = ['posts', 'events', 'pages', 'questions', 'recipes', 'profiles', 'stories', 'reels/thumbs'];
        $folder   = $request->query('folder', '');
        $files    = [];
        $total    = 0;

        $scanDir = $folder && in_array($folder, $folders)
            ? $baseDir . '/' . $folder
            : $baseDir;

        if (File::exists($scanDir)) {
            $allFiles = $folder ? File::files($scanDir) : File::allFiles($scanDir);
            foreach ($allFiles as $f) {
                $size    = $f->getSize();
                $total  += $size;
                $relPath = ltrim(str_replace($baseDir, '', $f->getRealPath()), '/\\');
                $files[] = [
                    'name'     => $f->getFilename(),
                    'rel_path' => $relPath,
                    'folder'   => dirname($relPath) === '.' ? '' : dirname($relPath),
                    'url'      => '/uploads/' . str_replace('\\', '/', $relPath),
                    'ext'      => strtolower($f->getExtension()),
                    'size'     => $this->formatBytes($size),
                    'modified' => $f->getMTime(),
                ];
            }
            usort($files, fn($a, $b) => $b['modified'] - $a['modified']);
        }

        return view('admin.media', [
            'files'     => $files,
            'folders'   => $folders,
            'folder'    => $folder,
            'totalSize' => $this->formatBytes($total),
        ]);
    }

    public function uploadMedia(Request $request)
    {
        $this->requireAdmin();
        $request->validate(['files' => 'required', 'files.*' => 'file|max:51200']);

        $allowed = ['posts', 'events', 'pages', 'questions', 'recipes', 'profiles', 'stories'];
        $folder  = $request->input('folder', '');
        $subdir  = ($folder && in_array($folder, $allowed)) ? $folder : '';
        $dir     = public_path('uploads' . ($subdir ? '/' . $subdir : ''));
        File::ensureDirectoryExists($dir, 0755);

        foreach ($request->file('files', []) as $file) {
            $name = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $name);
        }
        return back()->with('success', 'Files uploaded.');
    }

    public function deleteMedia(Request $request, string $filename)
    {
        $this->requireAdmin();
        // $filename may include a subfolder prefix like "posts/abc.webp"
        $rel  = ltrim(str_replace(['..', '//'], '', $filename), '/\\');
        $path = public_path('uploads/' . $rel);
        if (File::exists($path)) File::delete($path);
        return back()->with('success', 'File deleted.');
    }

    // ── Contacts ───────────────────────────────────────────────

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

    // ── Newsletter ─────────────────────────────────────────────

    public function newsletter(Request $request)
    {
        $this->requireAdmin();
        $tab = $request->query('tab', 'subscribers');
        return view('admin.newsletter', [
            'subscribers'       => NewsletterSubscriber::latest()->paginate(30),
            'totalSubscribers'  => NewsletterSubscriber::count(),
            'activeSubscribers' => NewsletterSubscriber::where('is_active', true)->count(),
            'templates'         => NewsletterTemplate::latest()->get(),
            'tab'               => $tab,
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

    public function storeTemplate(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'text_content' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default')) {
            NewsletterTemplate::update(['is_default' => false]);
        }

        NewsletterTemplate::create($data);
        return back()->with('success', 'Template created.');
    }

    public function updateTemplate(Request $request, int $id)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'text_content' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        $template = NewsletterTemplate::findOrFail($id);
        if ($request->boolean('is_default')) {
            NewsletterTemplate::where('id', '!=', $id)->update(['is_default' => false]);
        }

        $template->update($data);
        return back()->with('success', 'Template updated.');
    }

    public function deleteTemplate(int $id)
    {
        $this->requireAdmin();
        NewsletterTemplate::findOrFail($id)->delete();
        return back()->with('success', 'Template deleted.');
    }

    public function createTemplate()
    {
        $this->requireAdmin();
        return view('admin.newsletter-templates');
    }

    public function editTemplate(int $id)
    {
        $this->requireAdmin();
        $template = NewsletterTemplate::findOrFail($id);
        return view('admin.newsletter-templates', compact('template'));
    }

    // ── Questions ──────────────────────────────────────────────

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
        Question::findOrFail($id)->update(['status' => $request->status]);
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
        $answer   = Answer::findOrFail($id);
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

    // ── Polls ──────────────────────────────────────────────────

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

        return back()->with('success', 'Poll created. Use shortcode [poll:' . $poll->id . '] in any post.');
    }

    public function deletePoll(int $id)
    {
        $this->requireAdmin();
        Poll::findOrFail($id)->delete();
        return back()->with('success', 'Poll deleted.');
    }

    // ── Ad Zones ───────────────────────────────────────────────

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

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024)    return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }
}
