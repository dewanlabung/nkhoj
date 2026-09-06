<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    private function requireAdmin()
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'editor'])) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->requireAdmin();
        $query = Page::query();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('page_type', $request->type);
        }
        if ($request->filled('lang')) {
            $query->where('language', $request->lang);
        }
        $pages = $query->latest()->paginate(20)->withQueryString();
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        $this->requireAdmin();
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'slug'             => 'nullable|string|max:200',
            'content'          => 'nullable|string',
            'status'           => 'required|in:active,inactive',
            'page_type'        => 'required|in:default,custom',
            'menu_position'    => 'nullable|in:top_menu,main_menu,footer',
            'language'         => 'required|string|max:10',
            'meta_title'       => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data['slug'] = $data['slug']
            ? \Illuminate\Support\Str::slug($data['slug'])
            : Page::generateSlug($data['title']);

        Page::create($data);
        return redirect('/admin/pages')->with('success', 'Page created.');
    }

    public function edit(int $id)
    {
        $this->requireAdmin();
        $page = Page::findOrFail($id);
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, int $id)
    {
        $this->requireAdmin();
        $page = Page::findOrFail($id);
        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'slug'             => 'nullable|string|max:200',
            'content'          => 'nullable|string',
            'status'           => 'required|in:active,inactive',
            'page_type'        => 'required|in:default,custom',
            'menu_position'    => 'nullable|in:top_menu,main_menu,footer',
            'language'         => 'required|string|max:10',
            'meta_title'       => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
        ]);

        if (!empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['slug']);
        } else {
            unset($data['slug']);
        }

        $page->update($data);
        return redirect('/admin/pages')->with('success', 'Page updated.');
    }

    public function destroy(int $id)
    {
        $this->requireAdmin();
        Page::findOrFail($id)->delete();
        return back()->with('success', 'Page deleted.');
    }
}
