<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\BookmarkCollection;
use App\Models\Post;
use App\Models\SocialPage;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    private array $typeMap = [
        'post'  => Post::class,
        'page'  => SocialPage::class,
    ];

    public function index(Request $request)
    {
        $filter     = $request->query('type', 'all');
        $collection = $request->query('collection');

        $query = auth()->user()->bookmarks()->with('bookmarkable')->latest();

        if ($filter !== 'all' && isset($this->typeMap[$filter])) {
            $query->where('bookmarkable_type', $this->typeMap[$filter]);
        }
        if ($collection) {
            $query->where('collection_id', $collection);
        }

        $bookmarks   = $query->paginate(18)->withQueryString();
        $collections = BookmarkCollection::where('user_id', auth()->id())
            ->withCount('bookmarks')->get();

        return view('bookmarks.index', compact('bookmarks', 'filter', 'collections'));
    }

    public function createCollection(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $col = BookmarkCollection::create([
            'user_id' => auth()->id(),
            'name'    => $request->name,
            'slug'    => \Str::slug($request->name . '-' . auth()->id()),
        ]);
        return response()->json(['id' => $col->id, 'name' => $col->name]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'type' => 'required|in:post,page',
            'id'   => 'required|integer',
        ]);

        $modelClass = $this->typeMap[$request->type];
        $model      = $modelClass::findOrFail($request->id);
        $userId     = auth()->id();

        $existing = Bookmark::where('user_id', $userId)
            ->where('bookmarkable_type', $modelClass)
            ->where('bookmarkable_id', $model->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $saved = false;
        } else {
            Bookmark::create([
                'user_id'           => $userId,
                'bookmarkable_type' => $modelClass,
                'bookmarkable_id'   => $model->id,
                'collection_id'     => $request->collection_id ?: null,
            ]);
            $saved = true;
        }

        return response()->json(['saved' => $saved]);
    }

    public function destroy(Bookmark $bookmark)
    {
        abort_if($bookmark->user_id !== auth()->id(), 403);
        $bookmark->delete();
        return response()->json(['success' => true]);
    }

    // Legacy: keep old /bookmark/{postId} POST working
    public function togglePost(int $postId)
    {
        $post   = Post::findOrFail($postId);
        $userId = auth()->id();

        $existing = Bookmark::where('user_id', $userId)
            ->where('bookmarkable_type', Post::class)
            ->where('bookmarkable_id', $postId)
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            Bookmark::create([
                'user_id'           => $userId,
                'bookmarkable_type' => Post::class,
                'bookmarkable_id'   => $postId,
            ]);
            $action = 'saved';
        }

        $count = Bookmark::where('bookmarkable_type', Post::class)
            ->where('bookmarkable_id', $postId)
            ->count();

        return response()->json(['action' => $action, 'count' => $count]);
    }
}
