<?php

namespace App\Http\Controllers;

use App\Models\NavigationItem;
use App\Models\Category;
use App\Models\Page;
use App\Models\Tag;
use Illuminate\Http\Request;

class NavigationController extends Controller
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
        $lang  = $request->get('lang', 'en');
        $items = NavigationItem::whereNull('parent_id')
            ->where('language', $lang)
            ->orderBy('sort_order')
            ->with('children')
            ->get();

        $categories = Category::orderBy('name_en')->get();
        $pages      = Page::where('status', 'active')->orderBy('title')->get();
        $tags       = Tag::orderBy('name_en')->get();

        $settings = $this->getSettings();

        return view('admin.navigation.index', compact('items', 'categories', 'pages', 'tags', 'lang', 'settings'));
    }

    public function store(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'label'     => 'required|string|max:120',
            'type'      => 'required|in:custom,category,page,tag,home',
            'url'       => 'nullable|string|max:500',
            'target_id' => 'nullable|integer',
            'language'  => 'required|string|max:10',
            'open_in'   => 'in:_self,_blank',
            'parent_id' => 'nullable|integer|exists:navigation_items,id',
        ]);

        $max = NavigationItem::where('language', $data['language'])
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('sort_order') ?? 0;

        NavigationItem::create(array_merge($data, ['sort_order' => $max + 1]));

        return back()->with('success', 'Menu item added.');
    }

    public function update(Request $request, int $id)
    {
        $this->requireAdmin();
        $item = NavigationItem::findOrFail($id);

        // Toggle-only call (from the active switch)
        if ($request->has('is_active') && count($request->all()) <= 2) {
            $item->update(['is_active' => (bool) $request->input('is_active')]);
            return response()->json(['ok' => true]);
        }

        $data = $request->validate([
            'label'     => 'required|string|max:120',
            'type'      => 'required|in:custom,category,page,tag,home',
            'url'       => 'nullable|string|max:500',
            'target_id' => 'nullable|integer',
            'open_in'   => 'in:_self,_blank',
        ]);
        $item->update($data);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Menu item updated.');
    }

    public function quickAdd(Request $request)
    {
        $this->requireAdmin();
        $request->validate([
            'type'      => 'required|in:category,page,tag',
            'target_id' => 'required|integer',
            'language'  => 'required|string|max:10',
        ]);

        // Build label from the target
        $label = match($request->type) {
            'category' => Category::find($request->target_id)?->name_en ?? 'Category',
            'page'     => Page::find($request->target_id)?->title ?? 'Page',
            'tag'      => Tag::find($request->target_id)?->name_en ?? 'Tag',
            default    => 'Link',
        };

        $max = NavigationItem::where('language', $request->language)->max('sort_order') ?? 0;

        NavigationItem::create([
            'label'      => $label,
            'type'       => $request->type,
            'target_id'  => $request->target_id,
            'language'   => $request->language,
            'sort_order' => $max + 1,
            'is_active'  => true,
            'open_in'    => '_self',
        ]);

        return back()->with('success', "\"{$label}\" added to navigation.");
    }

    public function destroy(int $id)
    {
        $this->requireAdmin();
        NavigationItem::where('parent_id', $id)->delete();
        NavigationItem::findOrFail($id)->delete();
        return back()->with('success', 'Menu item deleted.');
    }

    public function reorder(Request $request)
    {
        $this->requireAdmin();
        $order = $request->input('order', []);
        foreach ($order as $i => $id) {
            NavigationItem::where('id', $id)->update(['sort_order' => $i]);
        }
        return response()->json(['ok' => true]);
    }

    public function updateSettings(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate(['home_page_link' => 'in:show,hide']);
        $s = $this->getSettings();
        $s['nav_home_page_link'] = $data['home_page_link'] ?? 'show';
        $this->saveSettings($s);
        return back()->with('success', 'Navigation settings saved.');
    }

    private function getSettings(): array
    {
        $path = storage_path('app/site_settings.json');
        return file_exists($path) ? (json_decode(file_get_contents($path), true) ?? []) : [];
    }

    private function saveSettings(array $data): void
    {
        file_put_contents(storage_path('app/site_settings.json'), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
