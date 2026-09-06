<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
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
        file_put_contents(storage_path('app/site_settings.json'), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function index()
    {
        $this->requireAdmin();
        $languages = Language::orderBy('sort_order')->orderBy('id')->paginate(20);
        $settings  = $this->getSettings();
        return view('admin.languages.index', compact('languages', 'settings'));
    }

    public function store(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'short_form'      => 'required|string|max:10',
            'code'            => 'required|string|max:20',
            'editor_language' => 'nullable|string|max:30',
            'direction'       => 'required|in:ltr,rtl',
            'sort_order'      => 'required|integer|min:1',
            'is_active'       => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        Language::create($data);
        return back()->with('success', 'Language added.');
    }

    public function update(Request $request, int $id)
    {
        $this->requireAdmin();
        $lang = Language::findOrFail($id);
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'short_form'      => 'required|string|max:10',
            'code'            => 'required|string|max:20',
            'editor_language' => 'nullable|string|max:30',
            'direction'       => 'required|in:ltr,rtl',
            'sort_order'      => 'required|integer|min:1',
            'is_active'       => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $lang->update($data);
        return back()->with('success', 'Language updated.');
    }

    public function destroy(int $id)
    {
        $this->requireAdmin();
        Language::findOrFail($id)->delete();
        return back()->with('success', 'Language deleted.');
    }

    public function updateDefault(Request $request)
    {
        $this->requireAdmin();
        $request->validate(['default_language' => 'required|string|max:10']);
        $s = $this->getSettings();
        $s['default_language'] = $request->input('default_language');
        $this->saveSettings($s);
        return back()->with('success', 'Default language saved.');
    }

    // Preset languages that can be "imported"
    public static function presets(): array
    {
        return [
            ['name' => 'English',    'short_form' => 'en', 'code' => 'en-US', 'direction' => 'ltr', 'editor_language' => 'en'],
            ['name' => 'Nepali',     'short_form' => 'ne', 'code' => 'ne-NP', 'direction' => 'ltr', 'editor_language' => 'ne'],
            ['name' => 'Arabic',     'short_form' => 'ar', 'code' => 'ar-SA', 'direction' => 'rtl', 'editor_language' => 'ar'],
            ['name' => 'Hindi',      'short_form' => 'hi', 'code' => 'hi-IN', 'direction' => 'ltr', 'editor_language' => 'hi'],
            ['name' => 'French',     'short_form' => 'fr', 'code' => 'fr-FR', 'direction' => 'ltr', 'editor_language' => 'fr'],
            ['name' => 'Spanish',    'short_form' => 'es', 'code' => 'es-ES', 'direction' => 'ltr', 'editor_language' => 'es'],
            ['name' => 'German',     'short_form' => 'de', 'code' => 'de-DE', 'direction' => 'ltr', 'editor_language' => 'de'],
            ['name' => 'Chinese',    'short_form' => 'zh', 'code' => 'zh-CN', 'direction' => 'ltr', 'editor_language' => 'zh'],
            ['name' => 'Japanese',   'short_form' => 'ja', 'code' => 'ja-JP', 'direction' => 'ltr', 'editor_language' => 'ja'],
            ['name' => 'Portuguese', 'short_form' => 'pt', 'code' => 'pt-PT', 'direction' => 'ltr', 'editor_language' => 'pt'],
            ['name' => 'Russian',    'short_form' => 'ru', 'code' => 'ru-RU', 'direction' => 'ltr', 'editor_language' => 'ru'],
            ['name' => 'Turkish',    'short_form' => 'tr', 'code' => 'tr-TR', 'direction' => 'ltr', 'editor_language' => 'tr'],
            ['name' => 'Korean',     'short_form' => 'ko', 'code' => 'ko-KR', 'direction' => 'ltr', 'editor_language' => 'ko'],
            ['name' => 'Italian',    'short_form' => 'it', 'code' => 'it-IT', 'direction' => 'ltr', 'editor_language' => 'it'],
            ['name' => 'Urdu',       'short_form' => 'ur', 'code' => 'ur-PK', 'direction' => 'rtl', 'editor_language' => 'ur'],
            ['name' => 'Bengali',    'short_form' => 'bn', 'code' => 'bn-BD', 'direction' => 'ltr', 'editor_language' => 'bn'],
        ];
    }

    public function import(Request $request)
    {
        $this->requireAdmin();
        $request->validate(['preset' => 'required|string']);
        $preset = collect(self::presets())->firstWhere('short_form', $request->input('preset'));
        if (!$preset) {
            return back()->with('error', 'Unknown language preset.');
        }
        $exists = Language::where('short_form', $preset['short_form'])->exists();
        if ($exists) {
            return back()->with('error', 'This language already exists.');
        }
        $preset['sort_order'] = Language::max('sort_order') + 1;
        $preset['is_active']  = true;
        Language::create($preset);
        return back()->with('success', "Language \"{$preset['name']}\" imported.");
    }
}
