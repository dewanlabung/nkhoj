<?php

namespace App\Domains\Pages\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Core\NavigationItem;
use App\Models\Blog\Category;
use App\Models\SocialPages\Page;
use App\Models\Blog\Tag;
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

        $settings  = $this->getSettings();
        $mobileNav = $settings['mobile_nav'] ?? self::$defaultMobileNav;
        $mobileNavIcons = array_keys(self::$mobileNavIcons);
        $mobileNavIconPaths = self::$mobileNavIcons;

        return view('admin.navigation.index', compact('items', 'categories', 'pages', 'tags', 'lang', 'settings', 'mobileNav', 'mobileNavIcons', 'mobileNavIconPaths'));
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

    // ── Mobile Bottom Nav ──────────────────────────────────────

    public static array $mobileNavIcons = [
        'home'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'search'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
        'compass'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/><circle cx="12" cy="11" r="3"/>',
        'bell'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
        'bookmark'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>',
        'fire'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>',
        'grid'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>',
        'user'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        'story'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>',
        'trending'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>',
        'write'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>',
    ];

    public static array $defaultMobileNav = [
        ['slot' => 0, 'icon' => 'home',     'label' => 'Home',    'url' => '/',              'type' => 'regular'],
        ['slot' => 1, 'icon' => 'search',   'label' => 'Explore', 'url' => '/search',        'type' => 'regular'],
        ['slot' => 2, 'icon' => 'write',    'label' => 'Write',   'url' => '/write',         'type' => 'center'],
        ['slot' => 3, 'icon' => 'bell',     'label' => 'Inbox',   'url' => '/notifications', 'type' => 'regular'],
        ['slot' => 4, 'icon' => 'user',     'label' => 'Me',      'url' => '/profile',       'type' => 'regular'],
    ];

    public function updateMobileNav(Request $request)
    {
        $this->requireAdmin();

        $incoming = $request->input('slots', []);
        $nav = [];

        foreach ($incoming as $i => $slot) {
            $type = ($i == 2) ? 'center' : 'regular';
            $nav[] = [
                'slot'  => (int) $i,
                'icon'  => $slot['icon']  ?? 'home',
                'label' => substr(trim($slot['label'] ?? ''), 0, 30),
                'url'   => substr(trim($slot['url']   ?? '/'), 0, 255),
                'type'  => $type,
            ];
        }

        if (count($nav) !== 5) {
            return back()->with('error', 'Mobile nav must have exactly 5 slots.');
        }

        $s = $this->getSettings();
        $s['mobile_nav'] = $nav;
        $this->saveSettings($s);

        return back()->with('success', 'Mobile navigation saved.')->withFragment('mobile-nav');
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
