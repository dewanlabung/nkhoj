<?php

namespace App\Domains\Blog\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Domains\Blog\Services\BookmarkService;
use App\Models\Bookmark;
use App\Models\BookmarkCollection;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function __construct(private BookmarkService $bookmarkService) {}

    public function index(Request $request)
    {
        $filter     = $request->query('type', 'all');
        $collection = $request->query('collection');
        $typeMap    = $this->bookmarkService->getTypeMap();

        $query = auth()->user()->bookmarks()->with('bookmarkable')->latest();

        if ($filter !== 'all' && isset($typeMap[$filter])) {
            $query->where('bookmarkable_type', $typeMap[$filter]);
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
        $col = $this->bookmarkService->createCollection(auth()->id(), $request->name);
        return response()->json(['id' => $col->id, 'name' => $col->name]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'type' => 'required|in:post,page',
            'id'   => 'required|integer',
        ]);

        $saved = $this->bookmarkService->toggle(
            $request->type,
            $request->id,
            auth()->id(),
            $request->collection_id ?: null
        );

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
        $result = $this->bookmarkService->togglePost($postId, auth()->id());
        return response()->json($result);
    }
}
