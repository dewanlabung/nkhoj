<?php

namespace App\Domains\QnA\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Answer;
use App\Models\AnswerComment;
use App\Models\QuestionFlag;
use App\Models\QuestionRevision;
use App\Models\AnswerRevision;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Models\Bookmark;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionController extends Controller
{
    private function sidebarData(): array
    {
        return [
            'totalQuestions'   => Question::where('status', '!=', 'pending')->count(),
            'totalAnswers'     => Answer::count(),
            'bestAnswers'      => Answer::where('is_best', true)->count(),
            'totalUsers'       => User::count(),
            'popularQuestions' => Question::where('status', 'open')
                ->withCount('answers')
                ->orderByDesc('views_count')
                ->limit(5)
                ->get(),
            'recentAnswered'   => Question::where('status', 'open')
                ->where('answers_count', '>', 0)
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(),
            'topMembers'       => User::withCount(['questions', 'answers'])
                ->orderByDesc('questions_count')
                ->limit(5)
                ->get(),
            'questionCategories' => Category::withCount(['questions' => fn($q) => $q->where('status', 'open')])
                ->having('questions_count', '>', 0)
                ->where('is_active', true)
                ->orderByDesc('questions_count')
                ->limit(10)
                ->get(),
            'popularTags' => Tag::withCount('questions')
                ->having('questions_count', '>', 0)
                ->orderByDesc('questions_count')
                ->limit(20)
                ->get(),
        ];
    }

    public function index(Request $request)
    {
        $tab    = $request->get('tab', 'new');
        $search = $request->get('q', '');
        $tags   = array_filter((array) $request->get('tag', []));

        $query = Question::with(['user', 'category', 'tags'])
            ->withCount('answers')
            ->where('status', '!=', 'pending');

        if ($request->category) {
            $cat = Category::where('slug', $request->category)->first();
            if ($cat) $query->where('category_id', $cat->id);
        }

        if ($tags) {
            $tagIds = Tag::whereIn('slug', $tags)->pluck('id');
            $query->whereHas('tags', fn($q) => $q->whereIn('tags.id', $tagIds));
        } elseif ($request->tag) {
            // single tag string
            $tag = Tag::where('slug', $request->tag)->first();
            if ($tag) $query->whereHas('tags', fn($q) => $q->where('tags.id', $tag->id));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhereHas('answers', fn($a) => $a->where('content', 'like', "%{$search}%"));
            });
        }

        if ($tab === 'unanswered') {
            $query->where('answers_count', 0);
        }

        $questions = match($tab) {
            'trending'   => $query->orderByDesc('views_count')->paginate(15),
            'must-read'  => $query->where('answers_count', '>', 0)->orderByDesc('answers_count')->paginate(15),
            'top'        => $query->orderByDesc('votes')->paginate(15),
            'unanswered' => $query->latest()->paginate(15),
            'hot'        => $query->get()->sortByDesc(function ($q) {
                                $ageHours = max(1, $q->created_at->diffInHours(now()));
                                return $q->votes / pow($ageHours + 2, 1.5);
                            })->values()->pipe(fn($col) => new \Illuminate\Pagination\LengthAwarePaginator(
                                $col->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), 15),
                                $col->count(), 15,
                                \Illuminate\Pagination\Paginator::resolveCurrentPage(),
                                ['path' => $request->url(), 'query' => $request->query()]
                            )),
            default      => $query->latest()->paginate(15),
        };

        return view('questions.index', array_merge(compact('questions', 'tab', 'search'), $this->sidebarData()));
    }

    public function show(string $slug)
    {
        $question = Question::with(['user', 'category', 'tags', 'answers.user', 'answers.comments.user', 'bestAnswer'])
            ->where('slug', $slug)
            ->where('status', '!=', 'pending')
            ->firstOrFail();

        $question->increment('views_count');

        // Related by shared tags, fall back to same category
        $tagIds = $question->tags->pluck('id');
        if ($tagIds->count()) {
            $relatedQuestions = Question::whereHas('tags', fn($q) => $q->whereIn('tags.id', $tagIds))
                ->where('id', '!=', $question->id)
                ->where('status', 'open')
                ->withCount('answers')
                ->orderByDesc('votes')
                ->limit(5)
                ->get();
        } else {
            $relatedQuestions = Question::where('category_id', $question->category_id)
                ->where('id', '!=', $question->id)
                ->where('status', 'open')
                ->withCount('answers')
                ->latest()
                ->limit(5)
                ->get();
        }

        $userId = auth()->id();

        // Vote states
        $userQuestionVote = null;
        $userAnswerVotes  = [];
        if ($userId) {
            $userQuestionVote = DB::table('question_votes')
                ->where('question_id', $question->id)->where('user_id', $userId)->value('vote');
            $userAnswerVotes = DB::table('answer_votes')
                ->whereIn('answer_id', $question->answers->pluck('id'))
                ->where('user_id', $userId)
                ->pluck('vote', 'answer_id')->toArray();
        }

        // Follow / bookmark / flag state
        $isFollowing = $userId && DB::table('question_follows')
            ->where('question_id', $question->id)->where('user_id', $userId)->exists();
        $isBookmarked = $userId && DB::table('bookmarks')
            ->where('bookmarkable_type', Question::class)
            ->where('bookmarkable_id', $question->id)
            ->where('user_id', $userId)->exists();
        $hasFlagged = $userId && DB::table('question_flags')
            ->where('question_id', $question->id)->where('user_id', $userId)->exists();

        return view('questions.show', array_merge(
            compact('question', 'relatedQuestions', 'userQuestionVote', 'userAnswerVotes',
                    'isFollowing', 'isBookmarked', 'hasFlagged'),
            $this->sidebarData()
        ));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $tags       = Tag::orderBy('name_en')->get();
        return view('questions.ask', array_merge(compact('categories', 'tags'), $this->sidebarData()));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|min:5|max:300',
            'content'     => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags'        => 'nullable|string',
            'agree'       => 'accepted',
        ]);

        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to ask a question.');
        }

        $slug = Str::slug($request->title);
        $base = $slug; $i = 1;
        while (Question::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}"; $i++;
        }

        $imagePath = null;
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('questions', 'public');
        }

        $question = Question::create([
            'user_id'        => auth()->id(),
            'title'          => $request->title,
            'slug'           => $slug,
            'content'        => $request->content,
            'category_id'    => $request->category_id ?: null,
            'featured_image' => $imagePath,
            'is_poll'        => $request->boolean('is_poll'),
            'is_anonymous'   => $request->boolean('is_anonymous'),
            'is_private'     => $request->boolean('is_private'),
            'notify_email'   => $request->boolean('notify_email', true),
            'status'         => 'open',
        ]);

        if ($request->tags) {
            $tagNames = array_filter(array_map('trim', explode(',', $request->tags)));
            $tagIds   = [];
            foreach ($tagNames as $name) {
                $tag      = Tag::firstOrCreate(['slug' => Str::slug($name)], ['name_en' => $name]);
                $tagIds[] = $tag->id;
            }
            $question->tags()->sync($tagIds);
        }

        // Auto-follow own question
        DB::table('question_follows')->insertOrIgnore([
            'question_id' => $question->id,
            'user_id'     => auth()->id(),
            'created_at'  => now(),
        ]);

        // +2 reputation for asking
        auth()->user()->increment('reputation', 2);

        return redirect("/questions/{$question->slug}")->with('success', 'Your question has been published!');
    }

    public function storeAnswer(Request $request, int $questionId)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to answer.');
        }

        $request->validate(['content' => 'required|string|min:10']);

        $question = Question::findOrFail($questionId);

        $answer = Answer::create([
            'question_id'  => $questionId,
            'user_id'      => auth()->id(),
            'content'      => $request->content,
            'is_anonymous' => $request->boolean('is_anonymous'),
        ]);

        $question->increment('answers_count');

        // +1 reputation for answering
        auth()->user()->increment('reputation', 1);

        if ($request->expectsJson()) {
            return response()->json(['answers_count' => $question->fresh()->answers_count]);
        }

        return back()->with('success', 'Your answer has been posted!');
    }

    public function markBestAnswer(int $questionId, int $answerId)
    {
        $question = Question::findOrFail($questionId);
        if ($question->user_id !== auth()->id() && !in_array(auth()->user()?->role, ['admin', 'editor'])) {
            abort(403);
        }

        Answer::where('question_id', $questionId)->update(['is_best' => false]);
        $answer = Answer::where('id', $answerId)->first();
        $answer->update(['is_best' => true]);
        $question->update(['best_answer_id' => $answerId]);

        // +15 reputation for the answerer
        if ($answer->user_id) {
            User::where('id', $answer->user_id)->increment('reputation', 15);
        }

        return back()->with('success', 'Best answer marked!');
    }

    // ── Comments on answers ──────────────────────────────────────────────────

    public function storeComment(Request $request, int $answerId)
    {
        if (!auth()->check()) abort(401);

        $request->validate(['content' => 'required|string|min:5|max:600']);

        $comment = AnswerComment::create([
            'answer_id'    => $answerId,
            'user_id'      => auth()->id(),
            'content'      => $request->content,
            'is_anonymous' => $request->boolean('is_anonymous'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'id'         => $comment->id,
                'content'    => $comment->content,
                'user'       => $comment->is_anonymous ? 'Anonymous' : auth()->user()->name,
                'created_at' => $comment->created_at->diffForHumans(),
            ]);
        }

        return back()->with('success', 'Comment added.');
    }

    // ── Follow / unfollow question ───────────────────────────────────────────

    public function toggleFollow(int $questionId)
    {
        if (!auth()->check()) return response()->json(['error' => 'Login required'], 401);

        $exists = DB::table('question_follows')
            ->where('question_id', $questionId)->where('user_id', auth()->id())->exists();

        if ($exists) {
            DB::table('question_follows')->where('question_id', $questionId)->where('user_id', auth()->id())->delete();
            return response()->json(['following' => false]);
        }

        DB::table('question_follows')->insertOrIgnore([
            'question_id' => $questionId,
            'user_id'     => auth()->id(),
            'created_at'  => now(),
        ]);
        return response()->json(['following' => true]);
    }

    // ── Bookmark question ────────────────────────────────────────────────────

    public function toggleBookmark(int $questionId)
    {
        if (!auth()->check()) return response()->json(['error' => 'Login required'], 401);

        $existing = DB::table('bookmarks')
            ->where('bookmarkable_type', Question::class)
            ->where('bookmarkable_id', $questionId)
            ->where('user_id', auth()->id())->first();

        if ($existing) {
            DB::table('bookmarks')->where('id', $existing->id)->delete();
            return response()->json(['bookmarked' => false]);
        }

        DB::table('bookmarks')->insert([
            'bookmarkable_type' => Question::class,
            'bookmarkable_id'   => $questionId,
            'user_id'           => auth()->id(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
        return response()->json(['bookmarked' => true]);
    }

    // ── Flag question ────────────────────────────────────────────────────────

    public function flagQuestion(Request $request, int $questionId)
    {
        if (!auth()->check()) return response()->json(['error' => 'Login required'], 401);

        $request->validate(['reason' => 'required|in:spam,off-topic,duplicate,inappropriate', 'note' => 'nullable|string|max:300']);

        DB::table('question_flags')->updateOrInsert(
            ['question_id' => $questionId, 'user_id' => auth()->id()],
            ['reason' => $request->reason, 'note' => $request->note, 'resolved' => false, 'created_at' => now(), 'updated_at' => now()]
        );

        return response()->json(['flagged' => true]);
    }

    // ── Close question ───────────────────────────────────────────────────────

    public function closeQuestion(Request $request, int $questionId)
    {
        $question = Question::findOrFail($questionId);
        if (!in_array(auth()->user()?->role, ['admin', 'editor'])) abort(403);

        $request->validate(['reason' => 'required|string|max:255', 'duplicate_of' => 'nullable|integer']);

        $question->update([
            'status'        => 'closed',
            'closed_reason' => $request->reason,
            'duplicate_of'  => $request->duplicate_of,
        ]);

        return back()->with('success', 'Question closed.');
    }

    public function reopenQuestion(int $questionId)
    {
        $question = Question::findOrFail($questionId);
        if (!in_array(auth()->user()?->role, ['admin', 'editor'])) abort(403);

        $question->update(['status' => 'open', 'closed_reason' => null, 'duplicate_of' => null]);

        return back()->with('success', 'Question reopened.');
    }

    // ── Vote on question ─────────────────────────────────────────────────────

    public function voteQuestion(Request $request, int $questionId)
    {
        $question = Question::findOrFail($questionId);
        $newVote  = $request->input('vote') === 'up' ? 1 : -1;
        $userId   = auth()->id();

        $existing = DB::table('question_votes')
            ->where('question_id', $questionId)->where('user_id', $userId)->first();

        if ($existing) {
            if ($existing->vote === $newVote) {
                DB::table('question_votes')->where('id', $existing->id)->delete();
                $question->decrement('votes', $newVote);
                if ($question->user_id) User::where('id', $question->user_id)->decrement('reputation', $newVote > 0 ? 10 : -2);
                $newVote = 0;
            } else {
                DB::table('question_votes')->where('id', $existing->id)->update(['vote' => $newVote]);
                $question->increment('votes', $newVote * 2);
                if ($question->user_id) User::where('id', $question->user_id)->increment('reputation', $newVote > 0 ? 12 : -12);
            }
        } else {
            DB::table('question_votes')->insert(['question_id' => $questionId, 'user_id' => $userId, 'vote' => $newVote, 'created_at' => now()]);
            $question->increment('votes', $newVote);
            if ($question->user_id) User::where('id', $question->user_id)->increment('reputation', $newVote > 0 ? 10 : -2);
        }

        return response()->json(['votes' => $question->fresh()->votes, 'userVote' => $newVote]);
    }

    // ── Vote on answer ───────────────────────────────────────────────────────

    public function voteAnswer(Request $request, int $answerId)
    {
        $answer  = Answer::findOrFail($answerId);
        $newVote = $request->input('vote') === 'up' ? 1 : -1;
        $userId  = auth()->id();

        $existing = DB::table('answer_votes')
            ->where('answer_id', $answerId)->where('user_id', $userId)->first();

        if ($existing) {
            if ($existing->vote === $newVote) {
                DB::table('answer_votes')->where('id', $existing->id)->delete();
                $answer->decrement('votes', $newVote);
                if ($answer->user_id) User::where('id', $answer->user_id)->decrement('reputation', $newVote > 0 ? 10 : -2);
                $newVote = 0;
            } else {
                DB::table('answer_votes')->where('id', $existing->id)->update(['vote' => $newVote]);
                $answer->increment('votes', $newVote * 2);
                if ($answer->user_id) User::where('id', $answer->user_id)->increment('reputation', $newVote > 0 ? 12 : -12);
            }
        } else {
            DB::table('answer_votes')->insert(['answer_id' => $answerId, 'user_id' => $userId, 'vote' => $newVote, 'created_at' => now()]);
            $answer->increment('votes', $newVote);
            if ($answer->user_id) User::where('id', $answer->user_id)->increment('reputation', $newVote > 0 ? 10 : -2);
        }

        return response()->json(['votes' => $answer->fresh()->votes, 'userVote' => $newVote]);
    }

    // ── Edit question ────────────────────────────────────────────────────────

    public function edit(int $id)
    {
        $question = Question::findOrFail($id);
        if ($question->user_id !== auth()->id() && !in_array(auth()->user()?->role, ['admin', 'editor'])) {
            abort(403);
        }
        $categories   = Category::where('is_active', true)->orderBy('sort_order')->get();
        $tags         = Tag::orderBy('name_en')->get();
        $selectedTags = $question->tags->pluck('name_en')->implode(', ');
        return view('questions.edit', compact('question', 'categories', 'tags', 'selectedTags'));
    }

    public function update(Request $request, int $id)
    {
        $question = Question::findOrFail($id);
        if ($question->user_id !== auth()->id() && !in_array(auth()->user()?->role, ['admin', 'editor'])) {
            abort(403);
        }

        $request->validate([
            'title'       => 'required|string|min:5|max:300',
            'content'     => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // Save revision before overwriting
        QuestionRevision::create([
            'question_id' => $question->id,
            'user_id'     => auth()->id(),
            'title'       => $question->title,
            'content'     => $question->content,
        ]);

        if ($request->title !== $question->title) {
            $slug = Str::slug($request->title);
            $base = $slug; $i = 1;
            while (Question::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = "{$base}-{$i}"; $i++;
            }
            $question->slug = $slug;
        }

        if ($request->hasFile('featured_image')) {
            $question->featured_image = $request->file('featured_image')->store('questions', 'public');
        }

        $question->fill([
            'title'        => $request->title,
            'content'      => $request->content,
            'category_id'  => $request->category_id ?: null,
            'is_anonymous' => $request->boolean('is_anonymous'),
            'edited_at'    => now(),
        ])->save();

        if ($request->filled('tags')) {
            $tagNames = array_filter(array_map('trim', explode(',', $request->tags)));
            $tagIds   = [];
            foreach ($tagNames as $name) {
                $tag      = Tag::firstOrCreate(['slug' => Str::slug($name)], ['name_en' => $name]);
                $tagIds[] = $tag->id;
            }
            $question->tags()->sync($tagIds);
        }

        return redirect("/questions/{$question->slug}")->with('success', 'Question updated!');
    }

    // ── Edit answer ──────────────────────────────────────────────────────────

    public function updateAnswer(Request $request, int $answerId)
    {
        $answer = Answer::findOrFail($answerId);
        if ($answer->user_id !== auth()->id() && !in_array(auth()->user()?->role, ['admin', 'editor'])) {
            abort(403);
        }

        $request->validate(['content' => 'required|string|min:10']);

        // Save revision
        AnswerRevision::create([
            'answer_id' => $answer->id,
            'user_id'   => auth()->id(),
            'content'   => $answer->content,
        ]);

        $answer->update(['content' => $request->content, 'edited_at' => now()]);

        return back()->with('success', 'Answer updated!');
    }
}
