<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocalizedSettingsController extends Controller
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

    public function index(Request $request)
    {
        $this->requireAdmin();
        $lang     = $request->get('lang', 'en');
        $settings = $this->getSettings();
        $localized = $settings['localized'][$lang] ?? [];
        return view('admin.localized-settings', compact('settings', 'localized', 'lang'));
    }

    public function updateGeneral(Request $request)
    {
        $this->requireAdmin();
        $lang = $request->input('lang', 'en');
        $data = $request->validate([
            'app_name'        => 'nullable|string|max:200',
            'date_format'     => 'nullable|string|max:30',
            'site_title'      => 'nullable|string|max:300',
            'home_title'      => 'nullable|string|max:200',
            'site_description'=> 'nullable|string|max:500',
            'keywords'        => 'nullable|string',
            'footer_about'    => 'nullable|string|max:1000',
            'post_url_button' => 'nullable|string|max:100',
            'copyright'       => 'nullable|string|max:200',
        ]);

        $s = $this->getSettings();
        $s['localized'][$lang] = array_merge($s['localized'][$lang] ?? [], $data);
        // Keep global site_name in sync with the English app_name
        if ($lang === 'en' && !empty($data['app_name'])) {
            $s['site_name'] = $data['app_name'];
        }
        $this->saveSettings($s);
        return back()->with('success', 'General settings saved.');
    }

    public function updateContact(Request $request)
    {
        $this->requireAdmin();
        $lang = $request->input('lang', 'en');
        $data = $request->validate([
            'contact_email'   => 'nullable|email|max:200',
            'contact_phone'   => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:300',
            'contact_text'    => 'nullable|string',
        ]);

        $s = $this->getSettings();
        $s['localized'][$lang] = array_merge($s['localized'][$lang] ?? [], $data);
        $this->saveSettings($s);
        return back()->with('success', 'Contact settings saved.');
    }

    public function updateSocial(Request $request)
    {
        $this->requireAdmin();
        $lang = $request->input('lang', 'en');

        $platforms = ['twitter', 'instagram', 'facebook', 'youtube', 'whatsapp', 'linkedin', 'tiktok', 'pinterest', 'snapchat'];
        $social_links = [];
        $social_profile = [];
        foreach ($platforms as $p) {
            $social_links[$p]   = $request->input("social_link_{$p}", '');
            $social_profile[$p] = $request->boolean("social_profile_{$p}");
        }

        $s = $this->getSettings();
        $s['localized'][$lang] = array_merge($s['localized'][$lang] ?? [], [
            'social_links'          => $social_links,
            'social_profile_options'=> $social_profile,
        ]);
        $this->saveSettings($s);
        return back()->with('success', 'Social media settings saved.');
    }

    public function updateCookies(Request $request)
    {
        $this->requireAdmin();
        $lang = $request->input('lang', 'en');
        $data = $request->validate([
            'cookies_enabled'      => 'nullable|boolean',
            'cookies_title'        => 'nullable|string|max:200',
            'cookies_description'  => 'nullable|string|max:1000',
            'cookies_policy_label' => 'nullable|string|max:100',
            'cookies_policy_url'   => 'nullable|string|max:300',
        ]);
        $data['cookies_enabled'] = $request->boolean('cookies_enabled');

        $s = $this->getSettings();
        $s['localized'][$lang] = array_merge($s['localized'][$lang] ?? [], $data);
        $this->saveSettings($s);
        return back()->with('success', 'Cookie settings saved.');
    }
}
