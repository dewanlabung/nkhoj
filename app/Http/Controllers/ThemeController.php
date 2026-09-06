<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ThemeController extends Controller
{
    private static array $themes = [
        'magazine' => [
            'name'        => 'Magazine',
            'description' => 'Bold hero layout with featured images and category ribbons.',
            'preview'     => 'magazine',
        ],
        'news' => [
            'name'        => 'News',
            'description' => 'Clean news-paper style with dense list layout and headlines.',
            'preview'     => 'news',
        ],
        'classic' => [
            'name'        => 'Classic',
            'description' => 'Traditional blog layout with sidebar and reading-focused design.',
            'preview'     => 'classic',
        ],
        'minimal' => [
            'name'        => 'Minimal',
            'description' => 'Clean white canvas, large typography, maximum readability.',
            'preview'     => 'minimal',
        ],
        'grid' => [
            'name'        => 'Grid',
            'description' => 'Pinterest-style masonry grid for image-heavy content.',
            'preview'     => 'grid',
        ],
    ];

    private function requireAdmin()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }
    }

    public function index()
    {
        $this->requireAdmin();
        $settings    = $this->getSettings();
        $activeTheme = $settings['active_theme'] ?? 'magazine';
        return view('admin.themes.index', ['themes' => self::$themes, 'activeTheme' => $activeTheme]);
    }

    public function activate(string $theme)
    {
        $this->requireAdmin();
        if (!array_key_exists($theme, self::$themes)) {
            return back()->with('error', 'Unknown theme.');
        }
        $s = $this->getSettings();
        $s['active_theme'] = $theme;
        $this->saveSettings($s);
        return back()->with('success', ucfirst($theme) . ' theme activated.');
    }

    private function getSettings(): array
    {
        $path = storage_path('app/site_settings.json');
        return File::exists($path) ? (json_decode(File::get($path), true) ?? []) : [];
    }

    private function saveSettings(array $data): void
    {
        File::put(storage_path('app/site_settings.json'), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
