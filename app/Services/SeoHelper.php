<?php

namespace App\Services;

class SeoHelper
{
    private array $settings;

    public function __construct()
    {
        $path = storage_path('app/site_settings.json');
        $this->settings = file_exists($path)
            ? (json_decode(file_get_contents($path), true) ?? [])
            : [];
    }

    public function title(string $page, array $vars = []): string
    {
        $defaults = [
            'home'     => '{site_name} — {tagline}',
            'post'     => '{title} — {site_name}',
            'category' => '{category} — {site_name}',
            'author'   => '{author} — {site_name}',
            'tag'      => '#{tag} — {site_name}',
            'search'   => 'Search: {query} — {site_name}',
        ];

        $template = $this->settings["seo_title_{$page}"] ?? $defaults[$page] ?? '{site_name}';
        return $this->render($template, $vars);
    }

    public function description(string $page, array $vars = []): string
    {
        $siteName = $this->settings['site_name'] ?? config('app.name', 'nkhoj');
        $defaults = [
            'home'     => $this->settings['site_description'] ?? '',
            'post'     => '{excerpt}',
            'category' => '{category} विषयका सबै लेखहरू ' . $siteName . ' मा।',
            'author'   => '{author} द्वारा लेखिएका लेखहरू ' . $siteName . ' मा।',
            'tag'      => '#{tag} ट्याग भएका लेखहरू ' . $siteName . ' मा।',
            'search'   => '{query} को खोज नतिजा — ' . $siteName,
        ];

        $template = $this->settings["seo_desc_{$page}"] ?? $defaults[$page] ?? '';
        return $this->render($template, $vars);
    }

    private function render(string $template, array $vars): string
    {
        $vars['site_name'] ??= $this->settings['site_name'] ?? config('app.name', 'nkhoj');
        $vars['tagline']   ??= $this->settings['tagline_en']
                                ?? $this->settings['tagline_ne']
                                ?? '';

        $search  = array_map(fn($k) => "{{$k}}", array_keys($vars));
        $replace = array_values($vars);
        return trim(str_replace($search, $replace, $template));
    }
}
