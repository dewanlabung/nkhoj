<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Models\Widget;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SettingsController extends BaseAdminController
{
    public function __construct(private SiteSettingsService $settings) {}

    // ── General Settings ───────────────────────────────────────

    public function settingsHub()
    {
        $this->requireAdmin();
        return view('admin.settings-hub');
    }

    public function settings()
    {
        $this->requireAdmin();
        return view('admin.settings', ['s' => $this->settings->get()]);
    }

    public function updateSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        foreach ($request->only(['site_name', 'tagline_en', 'tagline_ne', 'site_description', 'contact_email', 'ga_id', 'adsense_id', 'site_url']) as $k => $v) {
            $s[$k] = $v;
        }
        foreach (['social_facebook', 'social_twitter', 'social_instagram', 'social_youtube', 'social_tiktok'] as $k) {
            $s[$k] = $request->input($k, '');
        }
        $s['allow_guest_comments']  = $request->boolean('allow_guest_comments');
        $s['require_post_approval'] = $request->boolean('require_post_approval');
        $s['show_breaking_news']    = $request->boolean('show_breaking_news');
        $this->settings->save($s);
        return back()->with('success', 'Settings saved.');
    }

    private function patchSettings(array $patch): void
    {
        $this->settings->save(array_merge($this->settings->get(), $patch));
    }

    public function updateSettingsUrl(Request $request)
    {
        $this->requireAdmin();
        $this->patchSettings(['site_url' => $request->input('site_url', '')]);
        return back()->with('success', 'Site URL saved.');
    }

    public function updateSettingsName(Request $request)
    {
        $this->requireAdmin();
        $name = $request->input('site_name', 'nkhoj');
        $s    = $this->settings->get();
        $s['site_name'] = $name;
        foreach (array_keys($s['localized'] ?? []) as $lang) {
            $s['localized'][$lang]['app_name'] = $name;
        }
        $this->settings->save($s);
        return back()->with('success', 'Site name saved.');
    }

    public function updateSettingsTagline(Request $request)
    {
        $this->requireAdmin();
        $this->patchSettings($request->only(['tagline_en', 'tagline_ne', 'site_description']));
        return back()->with('success', 'Tagline saved.');
    }

    public function updateSettingsContact(Request $request)
    {
        $this->requireAdmin();
        $this->patchSettings(['contact_email' => $request->input('contact_email', '')]);
        return back()->with('success', 'Contact email saved.');
    }

    public function updateSettingsSocial(Request $request)
    {
        $this->requireAdmin();
        $this->patchSettings($request->only(['social_facebook', 'social_twitter', 'social_instagram', 'social_youtube', 'social_tiktok', 'social_linkedin', 'social_newsletter', 'reader_count', 'reader_label']));
        return back()->with('success', 'Social links saved.');
    }

    public function updateSettingsAnalytics(Request $request)
    {
        $this->requireAdmin();
        $this->patchSettings($request->only(['ga_id', 'adsense_id']));
        return back()->with('success', 'Analytics settings saved.');
    }

    public function updateSettingsBehaviour(Request $request)
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['allow_guest_comments']  = $request->boolean('allow_guest_comments');
        $s['require_post_approval'] = $request->boolean('require_post_approval');
        $s['show_breaking_news']    = $request->boolean('show_breaking_news');
        $this->settings->save($s);
        return back()->with('success', 'Saved.');
    }

    private function uploadBrandAsset(Request $request, string $field, string $settingKey): \Illuminate\Http\RedirectResponse
    {
        $this->requireAdmin();
        if (!$request->hasFile($field)) return back();
        $file = $request->file($field);
        $ext  = $file->getClientOriginalExtension();
        $name = $field . '_' . time() . '.' . $ext;
        $file->move(public_path('brand'), $name);
        $this->patchSettings([$settingKey => '/brand/' . $name]);
        return back()->with('success', 'Image updated.');
    }

    public function uploadFavicon(Request $request)         { return $this->uploadBrandAsset($request, 'favicon', 'favicon_url'); }
    public function uploadLogoDark(Request $request)        { return $this->uploadBrandAsset($request, 'logo_dark', 'logo_dark_url'); }
    public function uploadLogoLight(Request $request)       { return $this->uploadBrandAsset($request, 'logo_light', 'logo_light_url'); }
    public function uploadLogoCompactDark(Request $request) { return $this->uploadBrandAsset($request, 'logo_compact_dark', 'logo_compact_dark_url'); }
    public function uploadLogoCompactLight(Request $request){ return $this->uploadBrandAsset($request, 'logo_compact_light', 'logo_compact_light_url'); }

    public function removeBrandAsset(string $asset)
    {
        $this->requireAdmin();
        $keyMap = [
            'favicon'            => 'favicon_url',
            'logo-dark'          => 'logo_dark_url',
            'logo-light'         => 'logo_light_url',
            'logo-compact-dark'  => 'logo_compact_dark_url',
            'logo-compact-light' => 'logo_compact_light_url',
        ];
        if (!isset($keyMap[$asset])) return redirect('/admin/settings');
        $s   = $this->settings->get();
        $key = $keyMap[$asset];
        if (!empty($s[$key])) {
            $path = public_path(ltrim($s[$key], '/'));
            if (file_exists($path)) @unlink($path);
        }
        $s[$key] = null;
        $this->settings->save($s);
        return redirect('/admin/settings')->with('success', 'Image removed.');
    }

    // ── Content Settings ───────────────────────────────────────

    public function contentSettings()
    {
        $this->requireAdmin();
        return view('admin.content-settings', ['settings' => $this->settings->get()]);
    }

    public function updateContentSettings(Request $request)
    {
        $this->requireAdmin();
        $s   = $this->settings->get();
        $tab = $request->input('tab', 'general');

        if ($tab === 'general') {
            foreach (['show_featured_section', 'comment_system', 'comment_approval', 'emoji_reactions', 'show_latest_posts'] as $k) {
                $s[$k] = $request->boolean($k);
            }
            $s['posts_per_page'] = (int) $request->input('posts_per_page', 16);
        } elseif ($tab === 'posts') {
            $s['post_url_structure'] = $request->input('post_url_structure', 'slug');
            foreach (['bulk_upload_authors', 'delete_images_with_post', 'audio_download', 'show_post_author', 'show_post_date', 'show_post_view_count', 'require_approval_new', 'require_approval_edited', 'restrict_rss'] as $k) {
                $s[$k] = $request->boolean($k);
            }
            $s['popular_posts_limit'] = (int) $request->input('popular_posts_limit', 5);
            $s['related_posts_limit'] = (int) $request->input('related_posts_limit', 6);
        } elseif ($tab === 'post_formats') {
            $s['formats_enabled'] = [];
            foreach (['article', 'gallery', 'sorted_list', 'table_of_contents', 'video', 'audio', 'trivia_quiz', 'personality_quiz', 'poll', 'recipe', 'event'] as $f) {
                $s['formats_enabled'][$f] = (bool) $request->input("formats_enabled.$f", false);
            }
        } elseif ($tab === 'file_upload') {
            $s['image_format']        = $request->input('image_format', 'webp');
            $s['allowed_extensions']  = implode(',', $request->input('extensions', []));
            foreach (['max_image_size', 'max_video_size', 'max_audio_size', 'max_file_size'] as $k) {
                $s[$k] = (int) $request->input($k, 20);
            }
        } elseif ($tab === 'featured') {
            $s['featured_source']   = $request->input('featured_source', 'manual');
            $s['featured_sort']     = $request->input('featured_sort', 'order');
            $s['featured_duration'] = (int) $request->input('featured_duration', 10);
            $s['featured_limit']    = (int) $request->input('featured_limit', 15);
        }

        $this->settings->save($s);
        return back()->with('success', 'Settings saved.');
    }

    public function updateAiSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['ai_enabled']       = $request->boolean('ai_enabled');
        $s['ai_provider']      = $request->input('ai_provider', 'gemini');
        $s['ai_api_key']       = $request->input('ai_api_key', '');
        $s['ai_model']         = $request->input('ai_model_gemini', 'gemini-2.5-flash-lite-legacy');
        $s['ai_model_chatgpt'] = $request->input('ai_model_chatgpt', 'gpt-4o-mini');
        $this->settings->save($s);
        return back()->with('success', 'AI settings saved.');
    }

    public function updateAutoDelete(Request $request)
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['auto_delete_enabled'] = $request->boolean('auto_delete_enabled');
        $s['auto_delete_days']    = (int) $request->input('auto_delete_days', 30);
        $s['auto_delete_scope']   = $request->input('auto_delete_scope', 'all');
        $this->settings->save($s);
        return back()->with('success', 'Auto-delete settings saved.');
    }

    // ── Widgets ────────────────────────────────────────────────

    public function widgets()
    {
        $this->requireAdmin();
        $widgets = Widget::orderBy('where_to_display')->orderBy('display_order')->get();
        return view('admin.widgets', [
            'widgets'   => $widgets,
            'types'     => Widget::types(),
            'positions' => Widget::positions(),
        ]);
    }

    public function storeWidget(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'type'             => 'required|in:' . implode(',', array_keys(Widget::types())),
            'title'            => 'required|string|max:150',
            'where_to_display' => 'required|in:' . implode(',', array_keys(Widget::positions())),
            'display_order'    => 'nullable|integer|min:0|max:999',
        ]);
        Widget::create($data + ['is_active' => true, 'display_order' => $data['display_order'] ?? 0]);
        $this->flushWidgetCaches();
        return back()->with('success', 'Widget created.');
    }

    public function editWidget(int $id)
    {
        $this->requireAdmin();
        return view('admin.widget-edit', [
            'widget'    => Widget::findOrFail($id),
            'types'     => Widget::types(),
            'positions' => Widget::positions(),
        ]);
    }

    public function updateWidget(Request $request, int $id)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'type'             => 'required|in:' . implode(',', array_keys(Widget::types())),
            'title'            => 'required|string|max:150',
            'where_to_display' => 'required|in:' . implode(',', array_keys(Widget::positions())),
            'display_order'    => 'nullable|integer|min:0|max:999',
        ]);
        Widget::findOrFail($id)->update($data + ['is_active' => $request->boolean('is_active'), 'display_order' => $data['display_order'] ?? 0]);
        $this->flushWidgetCaches();
        return redirect('/admin/widgets')->with('success', 'Widget updated.');
    }

    public function deleteWidget(int $id)
    {
        $this->requireAdmin();
        Widget::findOrFail($id)->delete();
        $this->flushWidgetCaches();
        return back()->with('success', 'Widget deleted.');
    }

    // ── SEO ────────────────────────────────────────────────────

    public function seo()
    {
        $this->requireAdmin();
        $robotsPath = public_path('robots.txt');
        $robots = File::exists($robotsPath) ? File::get($robotsPath)
            : "User-agent: *\nAllow: /\n\nSitemap: " . url('/sitemap.xml');
        $s = $this->settings->get();
        return view('admin.seo', compact('robots', 's'));
    }

    public function updateRobots(Request $request)
    {
        $this->requireAdmin();
        $request->validate(['robots' => 'required|string']);
        File::put(public_path('robots.txt'), $request->robots);
        return back()->with('success', 'robots.txt updated.');
    }

    public function updateSeoMeta(Request $request)
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['site_keywords']    = $request->input('site_keywords', '');
        $s['og_default_image'] = $request->input('og_default_image', '');
        $s['enable_jsonld']    = $request->boolean('enable_jsonld');
        $s['verify_google']    = $request->input('verify_google', '');
        $s['verify_bing']      = $request->input('verify_bing', '');
        foreach (['home', 'post', 'category', 'author', 'tag', 'search'] as $page) {
            $s["seo_title_{$page}"] = $request->input("seo_title_{$page}", '');
            $s["seo_desc_{$page}"]  = $request->input("seo_desc_{$page}", '');
        }
        $this->settings->save($s);
        return back()->with('success', 'Meta settings saved.');
    }

    public function updateSeoSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['ga_enabled']        = $request->boolean('ga_enabled');
        $s['ga_id']             = $request->input('ga_id', '');
        $s['sitemap_frequency'] = $request->input('sitemap_frequency', 'auto');
        $s['sitemap_lastmod']   = $request->input('sitemap_lastmod', 'none');
        $s['sitemap_priority']  = $request->input('sitemap_priority', 'none');
        $this->settings->save($s);
        return back()->with('success', 'SEO settings saved.');
    }

    // ── Storage & Cache ────────────────────────────────────────

    public function storage()
    {
        $this->requireAdmin();
        return view('admin.storage', ['s' => $this->settings->get()]);
    }

    public function updateStorage(Request $request)
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['active_storage'] = $request->input('active_storage', 'local');
        foreach (['s3', 'r2', 'b2'] as $driver) {
            if ($request->has($driver)) {
                $s[$driver] = array_map('trim', $request->input($driver, []));
            }
        }
        $this->settings->save($s);
        return back()->with('success', 'Storage settings saved.');
    }

    public function cache()
    {
        $this->requireAdmin();
        return view('admin.cache');
    }

    public function queueSettings()
    {
        $this->requireAdmin();
        return view('admin.queue-settings', ['s' => $this->settings->get()]);
    }

    public function updateQueueSettings(Request $request)
    {
        $this->requireAdmin();
        $request->validate(['queue_connection' => 'required|in:sync,database,redis,beanstalkd,sqs']);
        $s = $this->settings->get();
        $s['queue']['connection'] = $request->input('queue_connection');
        $this->settings->save($s);
        config(['queue.default' => $s['queue']['connection']]);
        return back()->with('success', 'Queue driver set to "' . $s['queue']['connection'] . '". No restart needed.');
    }

    public function clearCache(Request $request)
    {
        $this->requireAdmin();
        $type = $request->input('type', 'all');
        try {
            match ($type) {
                'app'    => \Artisan::call('cache:clear'),
                'view'   => \Artisan::call('view:clear'),
                'route'  => \Artisan::call('route:clear'),
                'config' => \Artisan::call('config:clear'),
                default  => (function () {
                    \Artisan::call('cache:clear');
                    \Artisan::call('view:clear');
                    \Artisan::call('route:clear');
                    \Artisan::call('config:clear');
                })(),
            };
        } catch (\Throwable) {}
        return back()->with('success', ucfirst($type) . ' cache cleared.');
    }

    public function backup()
    {
        $this->requireAdmin();
        $dbName   = config('database.connections.mysql.database');
        $dbUser   = config('database.connections.mysql.username');
        $dbPass   = config('database.connections.mysql.password');
        $dbHost   = config('database.connections.mysql.host');
        $filename = 'nkhoj_backup_' . date('Ymd_His') . '.sql';
        $path     = storage_path('app/' . $filename);
        $cmd      = sprintf('mysqldump --host=%s --user=%s --password=%s %s > %s',
            escapeshellarg($dbHost), escapeshellarg($dbUser), escapeshellarg($dbPass),
            escapeshellarg($dbName), escapeshellarg($path));
        exec($cmd);
        if (File::exists($path)) {
            return response()->download($path, $filename)->deleteFileAfterSend(true);
        }
        return back()->with('error', 'Backup failed. Ensure mysqldump is in PATH.');
    }

    // ── Security ───────────────────────────────────────────────

    public function security()
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        return view('admin.security', compact('s'));
    }

    public function updateSecurity(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'max_login_attempts'  => 'required|integer|min:1|max:100',
            'lockout_time'        => 'required|integer|min:1|max:1440',
            'min_password_length' => 'required|integer|min:4|max:128',
            'password_complexity' => 'nullable|boolean',
            'post_links'          => 'nullable|string|max:50',
            'public_links'        => 'nullable|string|max:50',
        ]);
        $s = $this->settings->get();
        $s['security'] = array_merge($s['security'] ?? [], [
            'max_login_attempts'  => (int) $data['max_login_attempts'],
            'lockout_time'        => (int) $data['lockout_time'],
            'min_password_length' => (int) $data['min_password_length'],
            'password_complexity' => (bool) ($data['password_complexity'] ?? false),
            'post_links'          => $data['post_links'] ?? 'nofollow',
            'public_links'        => $data['public_links'] ?? 'remove',
        ]);
        $this->settings->save($s);
        return back()->with('success', 'Security settings saved.');
    }

    public function updateCaptcha(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'captcha_enabled'  => 'nullable|boolean',
            'captcha_provider' => 'nullable|string|in:turnstile,recaptcha',
            'captcha_site_key' => 'nullable|string|max:200',
            'captcha_secret'   => 'nullable|string|max:200',
        ]);
        $s = $this->settings->get();
        $s['captcha'] = [
            'enabled'  => (bool) ($data['captcha_enabled'] ?? false),
            'provider' => $data['captcha_provider'] ?? 'turnstile',
            'site_key' => $data['captcha_site_key'] ?? '',
            'secret'   => $data['captcha_secret'] ?? '',
        ];
        $this->settings->save($s);
        return back()->with('success', 'Captcha settings saved.');
    }

    public function generateCronToken()
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['cron_token'] = Str::random(40);
        $this->settings->save($s);
        return back()->with('success', 'Cron token generated.');
    }

    public function revokeCronToken()
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['cron_token'] = null;
        $this->settings->save($s);
        return back()->with('success', 'Cron token revoked.');
    }

    // ── Email Settings ─────────────────────────────────────────

    public function emailSettings()
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        return view('admin.email-settings', compact('s'));
    }

    public function updateEmailSettings(Request $request)
    {
        $this->requireAdmin();
        $request->validate([
            'mail_host'     => 'nullable|string|max:200',
            'mail_port'     => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:200',
            'mail_from'     => 'nullable|email|max:200',
            'reply_to'      => 'nullable|email|max:200',
            'mail_title'    => 'nullable|string|max:100',
        ]);
        $s = $this->settings->get();
        $s['email'] = array_merge($s['email'] ?? [], [
            'service'         => $request->input('mail_service', 'mailpit'),
            'protocol'        => $request->input('mail_protocol', 'smtp'),
            'encryption'      => $request->input('mail_encryption', 'tls'),
            'host'            => $request->input('mail_host', ''),
            'port'            => (int) $request->input('mail_port', 587),
            'username'        => $request->input('mail_username', ''),
            'password'        => $request->input('mail_password') ?: ($s['email']['password'] ?? ''),
            'from_address'    => $request->input('mail_from', ''),
            'reply_to'        => $request->input('reply_to', ''),
            'title'           => $request->input('mail_title', config('app.name')),
            'verification'    => $request->boolean('email_verification'),
            'contact_forward' => $request->boolean('contact_forward'),
            'contact_email'   => $request->input('contact_email', ''),
            'template'        => $s['email']['template'] ?? 'pure-minimalist',
        ]);
        $this->settings->save($s);
        config([
            'mail.default'                 => $s['email']['protocol'] === 'smtp' ? 'smtp' : 'sendmail',
            'mail.mailers.smtp.host'       => $s['email']['host'],
            'mail.mailers.smtp.port'       => $s['email']['port'],
            'mail.mailers.smtp.encryption' => $s['email']['encryption'] === 'none' ? null : $s['email']['encryption'],
            'mail.mailers.smtp.username'   => $s['email']['username'],
            'mail.mailers.smtp.password'   => $s['email']['password'],
            'mail.from.address'            => $s['email']['from_address'] ?: config('mail.from.address'),
            'mail.from.name'               => $s['email']['title'] ?: config('app.name'),
        ]);
        return back()->with('success', 'Email settings saved.');
    }

    public function updateEmailTemplate(Request $request)
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['email']['template'] = $request->input('template', 'pure-minimalist');
        $this->settings->save($s);
        return back()->with('success', 'Email template updated.');
    }

    public function sendTestEmail(Request $request)
    {
        $this->requireAdmin();
        $request->validate(['test_email' => 'required|email']);
        try {
            \Mail::raw('This is a test email from ' . config('app.name') . '. Your mail configuration is working correctly.', function ($m) use ($request) {
                $m->to($request->test_email)->subject('Test Email – ' . config('app.name'));
            });
            return back()->with('success', 'Test email sent to ' . $request->test_email);
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    // ── Deploy ─────────────────────────────────────────────────

    public function deploy()
    {
        $this->requireAdmin();
        return view('admin.deploy');
    }

    public function runDeploy(Request $request)
    {
        $this->requireAdmin();
        $log     = [];
        $success = true;

        $composerBin = trim(shell_exec('which composer 2>/dev/null') ?: 'composer');
        $commands = [
            'git fetch origin master',
            'git reset --hard origin/master',
            'HOME=/tmp ' . $composerBin . ' install --no-dev --optimize-autoloader --no-interaction',
            PHP_BINARY . ' artisan migrate --force',
            PHP_BINARY . ' artisan cache:clear',
            PHP_BINARY . ' artisan config:clear',
            PHP_BINARY . ' artisan config:cache',
            PHP_BINARY . ' artisan view:clear',
            PHP_BINARY . ' artisan route:cache',
        ];

        foreach ($commands as $cmd) {
            $output = [];
            $code   = 0;
            exec('cd ' . base_path() . ' && ' . $cmd . ' 2>&1', $output, $code);
            $log[] = ['cmd' => $cmd, 'output' => implode("\n", $output), 'ok' => $code === 0];
            if ($code !== 0) {
                $success = false;
                break;
            }
        }

        return response()->json(['success' => $success, 'log' => $log]);
    }

    public function webhookDeploy(Request $request)
    {
        $secret = config('app.deploy_secret');

        if ($secret) {
            $authorized = false;
            $bearer     = $request->bearerToken();
            if ($bearer && hash_equals($secret, $bearer)) $authorized = true;

            if (!$authorized) {
                $signature = $request->header('X-Hub-Signature-256', '');
                $expected  = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
                if ($signature && hash_equals($expected, $signature)) $authorized = true;
            }

            if (!$authorized) return response('Unauthorized', 401);
        }

        $composerBin = trim(shell_exec('which composer 2>/dev/null') ?: 'composer');
        $commands = [
            'git fetch origin master',
            'git reset --hard origin/master',
            'HOME=/tmp ' . $composerBin . ' install --optimize-autoloader --no-interaction',
            PHP_BINARY . ' artisan migrate --force',
            PHP_BINARY . ' artisan cache:clear',
            PHP_BINARY . ' artisan config:clear',
            PHP_BINARY . ' artisan config:cache',
            PHP_BINARY . ' artisan view:clear',
            PHP_BINARY . ' artisan route:cache',
        ];

        foreach ($commands as $cmd) {
            exec('cd ' . base_path() . ' && ' . $cmd . ' 2>&1');
        }

        return response('OK', 200);
    }
}
