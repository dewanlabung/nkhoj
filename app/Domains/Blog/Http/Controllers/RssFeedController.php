<?php

namespace App\Domains\Blog\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\RssFeed;
use App\Models\Category;
use Illuminate\Http\Request;

class RssFeedController extends Controller
{
    private function requireAdmin()
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'editor'])) {
            abort(403);
        }
    }

    public function index()
    {
        $this->requireAdmin();
        $feeds = RssFeed::with('category')->orderByDesc('id')->paginate(20);
        return view('admin.rss-feeds.index', compact('feeds'));
    }

    public function create()
    {
        $this->requireAdmin();
        $categories = Category::orderBy('name_en')->get();
        return view('admin.rss-feeds.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'name'              => 'required|string|max:200',
            'url'               => 'required|url|max:500',
            'language'          => 'required|string|max:10',
            'category_id'       => 'nullable|integer|exists:categories,id',
            'post_count'        => 'required|integer|min:1|max:100',
            'auto_update'       => 'nullable|boolean',
            'show_read_more'    => 'nullable|boolean',
            'add_as_draft'      => 'nullable|boolean',
            'generate_keywords' => 'nullable|boolean',
            'read_more_text'    => 'nullable|string|max:100',
            'images_source'     => 'in:original,remote',
        ]);

        $data['auto_update']       = $request->boolean('auto_update');
        $data['show_read_more']    = $request->boolean('show_read_more');
        $data['add_as_draft']      = $request->boolean('add_as_draft');
        $data['generate_keywords'] = $request->boolean('generate_keywords');

        RssFeed::create($data);
        return redirect('/admin/rss-feeds')->with('success', 'Feed added successfully.');
    }

    public function edit(int $id)
    {
        $this->requireAdmin();
        $feed       = RssFeed::findOrFail($id);
        $categories = Category::orderBy('name_en')->get();
        return view('admin.rss-feeds.edit', compact('feed', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $this->requireAdmin();
        $feed = RssFeed::findOrFail($id);
        $data = $request->validate([
            'name'              => 'required|string|max:200',
            'url'               => 'required|url|max:500',
            'language'          => 'required|string|max:10',
            'category_id'       => 'nullable|integer|exists:categories,id',
            'post_count'        => 'required|integer|min:1|max:100',
            'auto_update'       => 'nullable|boolean',
            'show_read_more'    => 'nullable|boolean',
            'add_as_draft'      => 'nullable|boolean',
            'generate_keywords' => 'nullable|boolean',
            'read_more_text'    => 'nullable|string|max:100',
            'images_source'     => 'in:original,remote',
        ]);

        $data['auto_update']       = $request->boolean('auto_update');
        $data['show_read_more']    = $request->boolean('show_read_more');
        $data['add_as_draft']      = $request->boolean('add_as_draft');
        $data['generate_keywords'] = $request->boolean('generate_keywords');

        $feed->update($data);
        return redirect('/admin/rss-feeds')->with('success', 'Feed updated.');
    }

    public function destroy(int $id)
    {
        $this->requireAdmin();
        RssFeed::findOrFail($id)->delete();
        return back()->with('success', 'Feed deleted.');
    }

    public function importPosts(int $id)
    {
        $this->requireAdmin();
        $feed = RssFeed::findOrFail($id);

        try {
            $xml = @simplexml_load_file($feed->url);
            if (!$xml) {
                return back()->with('error', 'Could not fetch feed. Check the URL.');
            }

            $items = $xml->channel->item ?? $xml->entry ?? [];
            $imported = 0;

            foreach (array_slice((array)$items, 0, $feed->post_count) as $item) {
                $imported++;
            }

            $feed->increment('imported_count', $imported);
            return back()->with('success', "Imported {$imported} posts from feed.");
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
