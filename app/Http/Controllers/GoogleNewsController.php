<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class GoogleNewsController extends Controller
{
    private function requireAdmin()
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'editor'])) {
            abort(403);
        }
    }

    private function getSettings(): array
    {
        $path = storage_path('app/site_settings.json');
        return file_exists($path) ? (json_decode(file_get_contents($path), true) ?? []) : [];
    }

    private function saveSettings(array $data): void
    {
        file_put_contents(
            storage_path('app/site_settings.json'),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public function index()
    {
        $this->requireAdmin();
        $settings   = $this->getSettings();
        $categories = Category::orderBy('name_en')->get();
        $appUrl     = config('app.url', url('/'));
        return view('admin.google-news', compact('settings', 'categories', 'appUrl'));
    }

    public function update(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'google_news_enabled'          => 'nullable|boolean',
            'google_news_publication_name' => 'nullable|string|max:200',
            'google_news_rss_content'      => 'in:full,excerpt',
            'google_news_feed_limit'       => 'required|integer|min:1|max:100',
        ]);

        $s = $this->getSettings();
        $s['google_news_enabled']          = $request->boolean('google_news_enabled');
        $s['google_news_publication_name'] = $data['google_news_publication_name'] ?? 'nkhoj';
        $s['google_news_rss_content']      = $data['google_news_rss_content'] ?? 'full';
        $s['google_news_feed_limit']       = (int)($data['google_news_feed_limit'] ?? 50);
        $this->saveSettings($s);

        return back()->with('success', 'Google News settings saved.');
    }
}
