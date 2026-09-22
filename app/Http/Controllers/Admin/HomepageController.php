<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\HomeController;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class HomepageController extends Controller
{
    public function __construct(private SiteSettingsService $settings) {}

    private function requireAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Admin access only.');
        }
    }

    public function index()
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $sections = $s['homepage_sections'] ?? HomeController::getDefaultSections();

        // Ensure any newly added default sections not yet in saved config are appended
        $savedKeys = array_column($sections, 'key');
        foreach (HomeController::getDefaultSections() as $default) {
            if (!in_array($default['key'], $savedKeys)) {
                $sections[] = $default;
            }
        }

        return view('admin.homepage', compact('sections'));
    }

    public function update(Request $request)
    {
        $this->requireAdmin();
        $incoming = $request->input('sections', []);

        // Rebuild ordered array from submitted data
        $sections = [];
        foreach ($incoming as $item) {
            $key = $item['key'] ?? null;
            if (!$key) continue;
            $sections[] = [
                'key'     => $key,
                'label'   => $item['label'] ?? $key,
                'enabled' => isset($item['enabled']) && $item['enabled'] === '1',
            ];
        }

        if (empty($sections)) {
            return back()->with('error', 'No sections received — layout not saved.');
        }

        $s = $this->settings->get();
        $s['homepage_sections'] = $sections;
        $this->settings->save($s);

        // Bust all homepage caches so changes are reflected immediately
        foreach (['home_hero_strip', 'home_editors_pick', 'home_trending', 'home_categories',
                  'widgets_sidebar', 'widgets_home_top', 'widgets_home_bottom'] as $key) {
            Cache::forget($key);
        }

        return back()->with('success', 'Homepage layout saved.');
    }
}
