<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
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
        ];
    }

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'new');
        $query = Question::with(['user', 'category'])
            ->withCount('answers')
            ->where('status', '!=', 'pending');

        if ($request->category) {
            $cat = Category::where('slug', $request->category)->first();
            if ($cat) $query->where('category_id', $cat->id);
        }

        $questions = match($tab) {
            'trending' => $query->orderByDesc('views_count')->paginate(15),
            'must-read'=> $query->where('answers_count', '>', 2)->orderByDesc('answers_count')->paginate(15),
            'hot'      => $query->orderByDesc('answers_count')->paginate(15),
            default    => $query->latest()->paginate(15),
        };

        return view('questions.index', array_merge(compact('questions', 'tab'), $this->sidebarData()));
    }

    public function show(string $slug)
    {
        $question = Question::with(['user', 'category', 'tags', 'answers.user', 'bestAnswer'])
            ->where('slug', $slug)
            ->where('status', '!=', 'pending')
            ->firstOrFail();

        $question->increment('views_count');

        $relatedQuestions = Question::where('category_id', $question->category_id)
            ->where('id', '!=', $question->id)
            ->where('status', 'open')
            ->latest()
            ->limit(5)
            ->get();

        return view('questions.show', array_merge(compact('question', 'relatedQuestions'), $this->sidebarData()));
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
        $base = $slug;
        $i    = 1;
        while (Question::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        $imagePath = null;
        if ($request->hasFile('featured_image')) {
            $file      = $request->file('featured_image');
            $imagePath = $file->store('questions', 'public');
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

        // Attach tags
        if ($request->tags) {
            $tagNames = array_filter(array_map('trim', explode(',', $request->tags)));
            $tagIds   = [];
            foreach ($tagNames as $name) {
                $tagSlug = Str::slug($name);
                $tag     = Tag::firstOrCreate(['slug' => $tagSlug], ['name_en' => $name]);
                $tagIds[]= $tag->id;
            }
            $question->tags()->sync($tagIds);
        }

        return redirect("/questions/{$question->slug}")->with('success', 'Your question has been published!');
    }

    public function storeAnswer(Request $request, int $questionId)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to answer.');
        }

        $request->validate([
            'content' => 'required|string|min:10',
        ]);

        $question = Question::findOrFail($questionId);

        Answer::create([
            'question_id'  => $questionId,
            'user_id'      => auth()->id(),
            'content'      => $request->content,
            'is_anonymous' => $request->boolean('is_anonymous'),
        ]);

        $question->increment('answers_count');

        return back()->with('success', 'Your answer has been posted!');
    }

    public function markBestAnswer(int $questionId, int $answerId)
    {
        $question = Question::findOrFail($questionId);
        if ($question->user_id !== auth()->id() && !in_array(auth()->user()?->role, ['admin', 'editor'])) {
            abort(403);
        }

        Answer::where('question_id', $questionId)->update(['is_best' => false]);
        Answer::where('id', $answerId)->update(['is_best' => true]);
        $question->update(['best_answer_id' => $answerId]);

        return back()->with('success', 'Best answer marked!');
    }

    public function voteAnswer(Request $request, int $answerId)
    {
        $answer = Answer::findOrFail($answerId);
        $delta  = $request->input('vote') === 'up' ? 1 : -1;
        $answer->increment('votes', $delta);
        return response()->json(['votes' => $answer->votes]);
    }

    public function voteQuestion(Request $request, int $questionId)
    {
        $question = Question::findOrFail($questionId);
        $delta    = $request->input('vote') === 'up' ? 1 : -1;
        $question->increment('votes', $delta);
        return response()->json(['votes' => $question->votes]);
    }

    public function edit(int $id)
    {
        $question = Question::findOrFail($id);
        if ($question->user_id !== auth()->id() && !in_array(auth()->user()?->role, ['admin', 'editor'])) {
            abort(403);
        }
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $tags       = Tag::orderBy('name_en')->get();
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
        ])->save();

        if ($request->filled('tags')) {
            $tagNames = array_filter(array_map('trim', explode(',', $request->tags)));
            $tagIds   = [];
            foreach ($tagNames as $name) {
                $tag = Tag::firstOrCreate(['slug' => Str::slug($name)], ['name_en' => $name]);
                $tagIds[] = $tag->id;
            }
            $question->tags()->sync($tagIds);
        }

        return redirect("/questions/{$question->slug}")->with('success', 'Question updated!');
    }
}
