<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public static function icons(): array
    {
        return [
            'none'        => '',
            'award'       => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.686 2 6 4.686 6 8s2.686 6 6 6 6-2.686 6-6-2.686-6-6-6zm0 10c-2.206 0-4-1.794-4-4s1.794-4 4-4 4 1.794 4 4-1.794 4-4 4zm-1 4v6l1-1 1 1v-6c-.33.04-.66.06-1 .06s-.67-.02-1-.06z"/></svg>',
            'crown'       => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm2 3a1 1 0 000 2h10a1 1 0 000-2H7z"/></svg>',
            'flame'       => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 23a7.5 7.5 0 01-5.138-12.963C8.204 8.774 11.5 6.5 11 1.5c6 4 9 8 3 14 1 0 2.5 0 3-1.5.5 1.5.5 2.5.5 3 0 2.485-2.015 6-5.5 6z"/></svg>',
            'diamond'     => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.5 2h11l4.5 6-10 14L2 8l4.5-6zm1.25 2L5 7h14l-2.75-3h-8.5zM4.06 9L12 20.39 19.94 9H4.06z"/></svg>',
            'star'        => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
            'target'      => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 110-16 8 8 0 010 16zm0-12a4 4 0 100 8 4 4 0 000-8zm0 6a2 2 0 110-4 2 2 0 010 4z"/></svg>',
            'ghost'       => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a8 8 0 018 8v12l-3-3-3 3-3-3-3 3-3-3-3 3V10A8 8 0 0112 2zm0 2a6 6 0 00-6 6v10l1-1 3 3 3-3 3 3 3-3 1 1V10a6 6 0 00-6-6zm-2 7a1 1 0 110 2 1 1 0 010-2zm4 0a1 1 0 110 2 1 1 0 010-2z"/></svg>',
            'shield-check'=> '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 11l-2-2 1.41-1.41L10 9.17l4.59-4.58L16 6l-6 6z"/></svg>',
            'shield'      => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>',
            'group'       => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
            'trophy'      => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0011 15.9V18H9v2h6v-2h-2v-2.1a5.01 5.01 0 003.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.86 10.4 5 9.3 5 8zm14 0c0 1.3-.86 2.4-2 2.82V7h2v1z"/></svg>',
            'bolt'        => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg>',
            'user'        => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>',
            'coffee'      => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M2 21h18v-2H2v2zm2-4h14l1-9H3l1 9zm9-15c-1.1 0-2 .9-2 2v4h2V4c0-.55.45-1 1-1s1 .45 1 1v3h2V4c0-2.21-1.79-4-4-4zm3 7H8V6h2v1h4V6h2v3z"/></svg>',
            'gear'        => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.14 12.94c.04-.3.06-.61.06-.94s-.02-.64-.07-.94l2.03-1.58a.49.49 0 00.12-.61l-1.92-3.32a.49.49 0 00-.59-.22l-2.39.96a6.97 6.97 0 00-1.62-.94l-.36-2.54A.484.484 0 0014 2h-4c-.25 0-.46.18-.49.42l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87a.48.48 0 00.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58a.49.49 0 00-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.37 1.04.7 1.62.94l.36 2.54c.05.24.24.41.49.41h4c.25 0 .46-.18.49-.42l.36-2.54c.59-.24 1.13-.57 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32a.48.48 0 00-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>',
        ];
    }

    private function requireAdmin()
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'editor'])) {
            abort(403);
        }
    }

    public function index()
    {
        $this->requireAdmin();
        $badges = Badge::orderBy('sort_order')->orderBy('id')->get();
        $icons  = self::icons();
        return view('admin.badges.index', compact('badges', 'icons'));
    }

    public function store(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'name'    => 'required|string|max:80',
            'name_ne' => 'nullable|string|max:80',
            'color'   => 'required|string|max:20',
            'icon'    => 'nullable|string|max:60',
        ]);
        $data['sort_order'] = Badge::max('sort_order') + 1;
        Badge::create($data);
        return back()->with('success', 'Badge created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $this->requireAdmin();
        $badge = Badge::findOrFail($id);
        $data = $request->validate([
            'name'    => 'required|string|max:80',
            'name_ne' => 'nullable|string|max:80',
            'color'   => 'required|string|max:20',
            'icon'    => 'nullable|string|max:60',
        ]);
        $badge->update($data);
        return back()->with('success', 'Badge updated.');
    }

    public function destroy(int $id)
    {
        $this->requireAdmin();
        Badge::findOrFail($id)->delete();
        return back()->with('success', 'Badge deleted.');
    }
}
