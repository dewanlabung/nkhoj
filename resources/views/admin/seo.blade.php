@extends('layouts.admin')
@section('title', 'SEO Tools')

@section('content')
@if(session('success'))
<div class="mb-5 text-sm text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl px-4 py-3 flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Tab nav --}}
<div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-6 -mx-px overflow-x-auto scrollbar-hide"
    x-data="{ tab: new URLSearchParams(window.location.search).get('tab') || 'meta' }">
    @php $tabs = [
        'meta'      => ['icon' => '🏷️', 'label' => 'Meta Tags'],
        'sitemap'   => ['icon' => '🗺️', 'label' => 'Sitemap'],
        'robots'    => ['icon' => '🤖', 'label' => 'Robots.txt'],
        'analytics' => ['icon' => '📊', 'label' => 'Analytics'],
        'tools'     => ['icon' => '🔧', 'label' => 'Tools'],
    ]; @endphp
    @foreach($tabs as $key => $t)
    <button @click="tab = '{{ $key }}'; history.replaceState(null,'','/admin/seo?tab={{ $key }}')"
        :class="tab === '{{ $key }}' ? 'border-b-2 border-brand-500 text-brand-600 dark:text-brand-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
        class="flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium whitespace-nowrap transition-colors">
        <span>{{ $t['icon'] }}</span>{{ $t['label'] }}
    </button>
    @endforeach
</div>

<div x-data="{ tab: new URLSearchParams(window.location.search).get('tab') || 'meta' }">

{{-- ═══ META TAGS TAB ═══ --}}
<div x-show="tab === 'meta'" class="space-y-px">

    {{-- Global SEO --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row items-start gap-4 sm:gap-8">
        <div class="w-full sm:w-72 flex-shrink-0">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Global Meta</p>
            <p class="text-xs text-gray-400 leading-relaxed">Keywords and default OG image used when a page doesn't have its own image.</p>
        </div>
        <form method="POST" action="/admin/seo/meta" class="flex-1 max-w-xl space-y-4">
            @csrf
            <input type="hidden" name="section" value="global">
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Site Keywords <span class="font-normal text-gray-400">(comma-separated)</span></label>
                <input type="text" name="site_keywords" value="{{ $s['site_keywords'] ?? '' }}"
                    placeholder="नेपाल, समाचार, ब्लग, news, nepal"
                    class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <p class="text-xs text-gray-400 mt-1">Used in &lt;meta name="keywords"&gt; — keep under 10 terms.</p>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Default OG Image URL</label>
                <input type="url" name="og_default_image" value="{{ $s['og_default_image'] ?? '' }}"
                    placeholder="https://example.com/og-cover.jpg"
                    class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <p class="text-xs text-gray-400 mt-1">Shown when sharing pages that have no thumbnail. Recommended 1200×630 px.</p>
            </div>
            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">JSON-LD Structured Data</p>
                    <p class="text-xs text-gray-400">Outputs Article/NewsArticle schema on post pages for Google rich results.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer ml-4">
                    <input type="checkbox" name="enable_jsonld" value="1" class="sr-only peer"
                        {{ ($s['enable_jsonld'] ?? true) ? 'checked' : '' }}>
                    <div class="w-9 h-5 rounded-full transition-colors peer-checked:bg-brand-500 bg-gray-200 dark:bg-gray-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4"></div>
                </label>
            </div>
            <button class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg transition-colors">Save</button>
        </form>
    </div>

    {{-- Per-page title templates --}}
    @php
    $pages = [
        'home'     => ['label' => 'Home / Landing Page',    'icon' => '🏠', 'vars' => '{site_name}, {tagline}'],
        'post'     => ['label' => 'Article / Post Page',    'icon' => '📰', 'vars' => '{title}, {site_name}, {category}, {author}'],
        'category' => ['label' => 'Category Page',          'icon' => '📂', 'vars' => '{category}, {site_name}'],
        'author'   => ['label' => 'Author / Profile Page',  'icon' => '👤', 'vars' => '{author}, {site_name}'],
        'tag'      => ['label' => 'Tag Page',               'icon' => '🏷️', 'vars' => '{tag}, {site_name}'],
        'search'   => ['label' => 'Search Page',            'icon' => '🔍', 'vars' => '{query}, {site_name}'],
    ];
    $defaults = [
        'home'     => [
            'title'  => '{site_name} — {tagline}',
            'desc'   => 'नेपालको अग्रणी समाचार र ब्लग प्लेटफर्म। ताजा समाचार, विचार र विश्लेषण पढ्नुहोस्।',
        ],
        'post'     => [
            'title'  => '{title} — {site_name}',
            'desc'   => '{excerpt}',
        ],
        'category' => [
            'title'  => '{category} — {site_name}',
            'desc'   => '{category} विषयका सबै लेखहरू {site_name} मा।',
        ],
        'author'   => [
            'title'  => '{author} — {site_name}',
            'desc'   => '{author} द्वारा लेखिएका लेखहरू {site_name} मा।',
        ],
        'tag'      => [
            'title'  => '#{tag} — {site_name}',
            'desc'   => '#{tag} ट्याग भएका लेखहरू {site_name} मा।',
        ],
        'search'   => [
            'title'  => '"{query}" — खोज — {site_name}',
            'desc'   => '{site_name} मा "{query}" को खोज नतिजाहरू।',
        ],
    ];
    @endphp

    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5">
        <div class="mb-5">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Per-Page Title & Description Templates</p>
            <p class="text-xs text-gray-400">Customize the &lt;title&gt; and meta description for each page type. Use the listed variables — they are replaced at render time.</p>
        </div>

        <form method="POST" action="/admin/seo/meta" class="space-y-6">
            @csrf
            <input type="hidden" name="section" value="pages">
            @foreach($pages as $key => $page)
            <div class="border border-gray-100 dark:border-gray-700 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-base">{{ $page['icon'] }}</span>
                    <p class="text-sm font-bold text-gray-800 dark:text-white">{{ $page['label'] }}</p>
                    <span class="ml-auto text-xs text-gray-400 bg-gray-50 dark:bg-gray-700 px-2 py-0.5 rounded font-mono">{{ $page['vars'] }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Title Template</label>
                        <input type="text" name="seo_title_{{ $key }}"
                            value="{{ $s['seo_title_'.$key] ?? $defaults[$key]['title'] }}"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono text-xs">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Description Template</label>
                        <input type="text" name="seo_desc_{{ $key }}"
                            value="{{ $s['seo_desc_'.$key] ?? $defaults[$key]['desc'] }}"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 text-xs">
                    </div>
                </div>
            </div>
            @endforeach
            <button class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg transition-colors">Save Page Templates</button>
        </form>
    </div>

    {{-- Google Search Preview --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5">
        <p class="text-sm font-bold text-gray-900 dark:text-white mb-3">Google Search Preview</p>
        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-white dark:bg-gray-900 max-w-lg">
            <p class="text-xs text-green-700 dark:text-green-500 mb-0.5 truncate">{{ url('/') }}</p>
            <p class="text-blue-700 dark:text-blue-400 text-base leading-tight hover:underline cursor-pointer mb-1">
                {{ $s['site_name'] ?? config('app.name') }} — {{ $s['tagline_en'] ?? $s['tagline_ne'] ?? 'नेपाली समाचार र ब्लग' }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                {{ $s['site_description'] ?? 'नेपालको अग्रणी बहु-ब्लग र समाचार प्लेटफर्म। ताजा समाचार, विचार र विश्लेषण पढ्नुहोस्।' }}
            </p>
        </div>
        <p class="text-xs text-gray-400 mt-3">Title & description come from <a href="/admin/settings" class="text-brand-600 hover:underline">Settings → Site name / Tagline / Description</a>.</p>
    </div>
</div>

{{-- ═══ SITEMAP TAB ═══ --}}
<div x-show="tab === 'sitemap'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 max-w-2xl">
    <h3 class="font-bold text-gray-900 dark:text-white mb-1">Sitemap</h3>
    <p class="text-xs text-gray-400 mb-5">Configure XML sitemap settings for search engines.</p>

    <form method="POST" action="/admin/seo/settings" class="space-y-5">
        @csrf
        <div>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-2">Frequency</label>
            <p class="text-xs text-gray-400 mb-2">How frequently content at a URL is likely to change.</p>
            <div class="flex gap-5">
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="radio" name="sitemap_frequency" value="auto" {{ ($s['sitemap_frequency'] ?? 'auto') === 'auto' ? 'checked' : '' }} class="accent-brand-500">
                    Automatically Calculated
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="radio" name="sitemap_frequency" value="none" {{ ($s['sitemap_frequency'] ?? '') === 'none' ? 'checked' : '' }} class="accent-brand-500">
                    None
                </label>
            </div>
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-2">Last Modification</label>
            <div class="flex gap-5">
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="radio" name="sitemap_lastmod" value="auto" {{ ($s['sitemap_lastmod'] ?? '') === 'auto' ? 'checked' : '' }} class="accent-brand-500">
                    Automatically Calculated
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="radio" name="sitemap_lastmod" value="none" {{ ($s['sitemap_lastmod'] ?? 'none') === 'none' ? 'checked' : '' }} class="accent-brand-500">
                    None
                </label>
            </div>
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-2">Priority</label>
            <div class="flex gap-5">
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="radio" name="sitemap_priority" value="auto" {{ ($s['sitemap_priority'] ?? '') === 'auto' ? 'checked' : '' }} class="accent-brand-500">
                    Automatically Calculated
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="radio" name="sitemap_priority" value="none" {{ ($s['sitemap_priority'] ?? 'none') === 'none' ? 'checked' : '' }} class="accent-brand-500">
                    None
                </label>
            </div>
        </div>
        <button class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Generate Sitemap</button>
    </form>

    <div class="mt-6 border-t border-gray-100 dark:border-gray-700 pt-5">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Generated Sitemaps</h4>
        <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    <a href="/sitemap.xml" target="_blank" class="text-sm text-brand-600 hover:underline">sitemap.xml</a>
                </div>
                <a href="/sitemap.xml" target="_blank"
                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 hover:bg-green-100">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </a>
            </div>
        </div>
    </div>

    <div class="mt-4 flex gap-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-lg p-3">
        <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 mb-0.5">Submit to Search Consoles</p>
            <p class="text-xs text-blue-600 dark:text-blue-400">Submit to <a href="https://search.google.com/search-console" target="_blank" class="underline">Google Search Console</a> and <a href="https://www.bing.com/webmasters" target="_blank" class="underline">Bing Webmaster Tools</a>.</p>
            <p class="text-xs font-mono text-blue-600 dark:text-blue-400 mt-1 select-all">{{ url('/sitemap.xml') }}</p>
        </div>
    </div>
</div>

{{-- ═══ ROBOTS.TXT TAB ═══ --}}
<div x-show="tab === 'robots'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 max-w-2xl">
    <h3 class="font-bold text-gray-900 dark:text-white mb-1">Robots.txt</h3>
    <p class="text-xs text-gray-400 mb-4">Control how search engines crawl your site.</p>
    <form method="POST" action="/admin/seo/robots" class="space-y-3">
        @csrf
        <textarea name="robots" rows="10"
            class="w-full text-sm font-mono border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none bg-gray-50"
        >{{ $robots }}</textarea>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg">Save</button>
            <a href="/robots.txt" target="_blank" class="text-sm text-brand-600 hover:underline">View robots.txt ↗</a>
        </div>
    </form>
</div>

{{-- ═══ ANALYTICS TAB ═══ --}}
<div x-show="tab === 'analytics'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 max-w-2xl">
    <h3 class="font-bold text-gray-900 dark:text-white mb-5">Google Analytics</h3>
    <form method="POST" action="/admin/seo/settings" class="space-y-5"
        x-data="{ ga: {{ ($s['ga_enabled'] ?? false) ? 'true' : 'false' }} }">
        @csrf
        <input type="hidden" name="sitemap_frequency" value="{{ $s['sitemap_frequency'] ?? 'auto' }}">
        <input type="hidden" name="sitemap_lastmod"   value="{{ $s['sitemap_lastmod'] ?? 'none' }}">
        <input type="hidden" name="sitemap_priority"  value="{{ $s['sitemap_priority'] ?? 'none' }}">
        <div class="flex items-center justify-between">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Status</label>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-400" x-text="ga ? 'Enabled' : 'Disabled'"></span>
                <button type="button" @click="ga = !ga"
                    :class="ga ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200">
                    <span :class="ga ? 'translate-x-5' : 'translate-x-0.5'"
                        class="inline-block h-5 w-5 translate-y-0.5 transform rounded-full bg-white shadow transition duration-200"></span>
                </button>
                <input type="hidden" name="ga_enabled" :value="ga ? '1' : '0'">
            </div>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200 block mb-1.5">Measurement ID</label>
            <input type="text" name="ga_id" value="{{ $s['ga_id'] ?? '' }}" placeholder="G-XXXXXXXXXX"
                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
            <p class="text-xs text-gray-400 mt-1.5">Find in <a href="https://analytics.google.com" target="_blank" class="text-brand-600 hover:underline">Google Analytics → Admin → Data Streams</a>.</p>
        </div>
        <button class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save Changes</button>
    </form>
</div>

{{-- ═══ TOOLS TAB ═══ --}}
<div x-show="tab === 'tools'" class="grid grid-cols-1 sm:grid-cols-2 gap-5 max-w-3xl">

    {{-- RSS Feed --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">RSS Feed</h3>
        <p class="text-xs text-gray-400 mb-4">Submit to Google News for wider reach.</p>
        <a href="/feed.xml" target="_blank"
            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors mb-3">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Main RSS Feed</span>
            <span class="text-xs text-brand-600">↗</span>
        </a>
        <p class="text-xs font-mono text-gray-400 select-all mb-2">{{ url('/feed.xml') }}</p>
        <p class="text-xs text-gray-400">Submit to <a href="https://publishercenter.google.com" target="_blank" class="text-brand-600 hover:underline">Google News Publisher Center</a>.</p>
    </div>

    {{-- Verification codes --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Webmaster Verification</h3>
        <p class="text-xs text-gray-400 mb-4">HTML meta tag verification codes for search consoles.</p>
        <form method="POST" action="/admin/seo/meta" class="space-y-3">
            @csrf
            <input type="hidden" name="section" value="verification">
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Google Search Console</label>
                <input type="text" name="verify_google" value="{{ $s['verify_google'] ?? '' }}"
                    placeholder="google-site-verification content value"
                    class="w-full text-xs font-mono bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Bing Webmaster Tools</label>
                <input type="text" name="verify_bing" value="{{ $s['verify_bing'] ?? '' }}"
                    placeholder="msvalidate.01 content value"
                    class="w-full text-xs font-mono bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <button class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save</button>
        </form>
    </div>

    {{-- Quick links --}}
    <div class="sm:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white mb-3">Quick Submit Links</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach([
                ['label' => 'Google Search Console', 'url' => 'https://search.google.com/search-console', 'color' => 'text-blue-600'],
                ['label' => 'Bing Webmaster', 'url' => 'https://www.bing.com/webmasters', 'color' => 'text-orange-600'],
                ['label' => 'Google News', 'url' => 'https://publishercenter.google.com', 'color' => 'text-red-600'],
                ['label' => 'Schema Validator', 'url' => 'https://validator.schema.org', 'color' => 'text-green-600'],
            ] as $link)
            <a href="{{ $link['url'] }}" target="_blank"
                class="p-3 border border-gray-100 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <p class="text-xs font-semibold {{ $link['color'] }} mb-0.5">{{ $link['label'] }}</p>
                <p class="text-xs text-gray-400">↗ Open</p>
            </a>
            @endforeach
        </div>
    </div>
</div>

</div>{{-- end x-data --}}
@endsection
