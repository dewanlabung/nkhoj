@extends('layouts.admin')
@section('title', 'Settings')

@section('content')
@php
    $s        = $s ?? [];
    $auth     = $s['auth']       ?? [];
    $em       = $s['email']      ?? [];
    $sec      = $s['security']   ?? [];
    $cap      = $s['captcha']    ?? [];
    $ap       = $s['appearance'] ?? [];
    $brandColor = $ap['brand_color'] ?? '#6366f1';
    $customCss  = $ap['custom_css']  ?? '';
    $cronToken  = $s['cron_token'] ?? null;

    // helper macro for a pill toggle row (use inside a divide-y container)
    // call as a closure: $toggle($name, $label, $desc, $value)
    $toggle = fn(string $name, string $label, string $desc, bool $on) => [$name,$label,$desc,$on];
@endphp

{{-- ── Inline helpers ─────────────────────────────────── --}}
@php
// Reusable card header
function settingsCardHeader(string $title, string $sub = ''): string {
    return '<div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/30">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">' . e($title) . '</h2>'
        . ($sub ? '<p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">' . e($sub) . '</p>' : '')
        . '</div>';
}
@endphp

@if(session('success'))
<div class="mb-5 flex items-center gap-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm rounded-xl px-4 py-3">
    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 flex items-center gap-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm rounded-xl px-4 py-3">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    {{ session('error') }}
</div>
@endif

@php
$tabs = [
    'general'    => ['icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'General'],
    'branding'   => ['icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'label' => 'Branding'],
    'appearance' => ['icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01', 'label' => 'Appearance'],
    'auth'       => ['icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z', 'label' => 'Authentication'],
    'email'      => ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'label' => 'Email'],
    'content'    => ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'Content'],
    'seo'        => ['icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', 'label' => 'SEO'],
    'security'   => ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'label' => 'Security'],
    'infra'      => ['icon' => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01', 'label' => 'Infrastructure'],
];
@endphp

<div class="flex flex-col md:flex-row gap-4 md:gap-6" x-data="{ tab: new URLSearchParams(window.location.search).get('tab') || 'general' }">

    {{-- ── TAB NAV: horizontal strip on mobile, vertical sidebar on md+ ── --}}
    <div class="md:w-52 md:flex-shrink-0">

        {{-- Mobile: horizontal scrollable strip --}}
        <div class="md:hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-x-auto">
            <nav class="flex min-w-max">
                @foreach($tabs as $key => $t)
                <button @click="tab = '{{ $key }}'; history.replaceState(null,'','?tab={{ $key }}')"
                    :class="tab === '{{ $key }}'
                        ? 'text-brand-600 dark:text-brand-400 border-b-2 border-brand-500 font-semibold'
                        : 'text-gray-500 dark:text-gray-400 border-b-2 border-transparent'"
                    class="flex flex-col items-center gap-1 px-4 py-3 text-[11px] whitespace-nowrap transition-colors">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $t['icon'] }}"/>
                    </svg>
                    {{ $t['label'] }}
                </button>
                @endforeach
            </nav>
        </div>

        {{-- Desktop: sticky vertical sidebar --}}
        <div class="hidden md:block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden sticky top-6">
            <p class="px-4 pt-4 pb-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Configuration</p>
            <nav class="pb-2">
                @foreach($tabs as $key => $t)
                <button @click="tab = '{{ $key }}'; history.replaceState(null,'','?tab={{ $key }}')"
                    :class="tab === '{{ $key }}'
                        ? 'bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 border-l-2 border-brand-500 font-semibold'
                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/40 border-l-2 border-transparent'"
                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm transition-all text-left">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $t['icon'] }}"/>
                    </svg>
                    {{ $t['label'] }}
                </button>
                @endforeach
            </nav>
        </div>
    </div>

    {{-- ── RIGHT CONTENT ─────────────────────────────────────── --}}
    <div class="flex-1 min-w-0">

        {{-- ════════════════════════════════════════ GENERAL ════ --}}
        <div x-show="tab === 'general'" x-cloak class="space-y-4">

            {{-- Site Identity --}}
            <form method="POST" action="/admin/settings/name">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Site Identity</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Core information about your site.</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Site Name</p>
                        <p class="text-xs text-gray-400 mb-2">Appears in browser tabs, emails, and SEO tags.</p>
                        <input type="text" name="site_name" value="{{ $s['site_name'] ?? 'nkhoj' }}"
                            class="w-full max-w-sm text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save</button>
                </div>
            </div>
            </form>

            {{-- Tagline & Description --}}
            <form method="POST" action="/admin/settings/tagline">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Tagline & Description</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Used in meta tags and site header.</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Tagline (English)</p>
                        <input type="text" name="tagline_en" value="{{ $s['tagline_en'] ?? '' }}"
                            class="w-full max-w-sm text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Tagline (Nepali)</p>
                        <input type="text" name="tagline_ne" value="{{ $s['tagline_ne'] ?? '' }}"
                            class="w-full max-w-sm text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Site Description <span class="text-gray-400 font-normal">(SEO)</span></p>
                        <p class="text-xs text-gray-400 mb-2">Used in meta description tags. Max 160 characters.</p>
                        <textarea name="site_description" rows="2"
                            class="w-full max-w-sm text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none">{{ $s['site_description'] ?? '' }}</textarea>
                    </div>
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save</button>
                </div>
            </div>
            </form>

            {{-- Contact & URL --}}
            <form method="POST" action="/admin/settings/contact">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Contact</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Contact Email</p>
                        <p class="text-xs text-gray-400 mb-2">Shown publicly and used for system notifications.</p>
                        <input type="email" name="contact_email" value="{{ $s['contact_email'] ?? '' }}" placeholder="hello@nkhoj.com"
                            class="w-full max-w-sm text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save</button>
                </div>
            </div>
            </form>

            {{-- Social Links --}}
            <form method="POST" action="/admin/settings/social">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Social Media</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Links shown in site footer and profile pages.</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach(['social_facebook'=>'Facebook','social_twitter'=>'X (Twitter)','social_instagram'=>'Instagram','social_youtube'=>'YouTube','social_tiktok'=>'TikTok','social_linkedin'=>'LinkedIn'] as $k=>$lbl)
                    <div class="flex items-center gap-4 px-6 py-3.5">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 w-28 flex-shrink-0">{{ $lbl }}</p>
                        <input type="url" name="{{ $k }}" value="{{ $s[$k] ?? '' }}" placeholder="https://…"
                            class="flex-1 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    @endforeach
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save</button>
                </div>
            </div>
            </form>

            {{-- Analytics --}}
            <form method="POST" action="/admin/settings/analytics">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Analytics & Ads</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Google Analytics ID</p>
                        <p class="text-xs text-gray-400 mb-2">Format: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded text-xs">G-XXXXXXXXXX</code></p>
                        <input type="text" name="ga_id" value="{{ $s['ga_id'] ?? '' }}" placeholder="G-XXXXXXXXXX"
                            class="w-full max-w-xs text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Google AdSense ID</p>
                        <p class="text-xs text-gray-400 mb-2">Format: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded text-xs">ca-pub-XXXXXXXX</code></p>
                        <input type="text" name="adsense_id" value="{{ $s['adsense_id'] ?? '' }}" placeholder="ca-pub-XXXXXXXX"
                            class="w-full max-w-xs text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                    </div>
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save</button>
                </div>
            </div>
            </form>

            {{-- Behaviour Toggles --}}
            <form method="POST" action="/admin/settings/behaviour">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Site Behaviour</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Toggle site-wide features on or off.</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach([
                        ['allow_guest_comments',  'Guest Comments',     'Allow non-registered users to post comments.',       $s['allow_guest_comments']  ?? false],
                        ['require_post_approval', 'Post Approval',      'New posts require admin approval before publishing.', $s['require_post_approval'] ?? false],
                        ['show_breaking_news',    'Breaking News',      'Show the breaking news ticker on the homepage.',      $s['show_breaking_news']    ?? false],
                    ] as [$name, $label, $desc, $on])
                    <div class="flex items-center px-6 py-4" x-data="{ on: {{ $on ? 'true' : 'false' }} }">
                        <div class="flex-1 min-w-0 pr-6">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $label }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $desc }}</p>
                        </div>
                        <button type="button" @click="on = !on"
                            :class="on ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-600'"
                            class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full transition-colors duration-200">
                            <span :class="on ? 'translate-x-4' : 'translate-x-0.5'"
                                class="inline-block mt-0.5 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200"></span>
                        </button>
                        <input type="hidden" name="{{ $name }}" :value="on ? '1' : '0'">
                    </div>
                    @endforeach
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save</button>
                </div>
            </div>
            </form>
        </div>

        {{-- ═══════════════════════════════════════ BRANDING ════ --}}
        <div x-show="tab === 'branding'" x-cloak class="space-y-4">

            {{-- Favicon --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Favicon</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Browser tab icon. At least 512×512 px, PNG or ICO.</p>
                </div>
                <div class="px-6 py-5" x-data="{ preview: '{{ $s['favicon_url'] ?? '' }}' }">
                    <form method="POST" action="/admin/settings/favicon" enctype="multipart/form-data">@csrf
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 overflow-hidden flex items-center justify-center bg-gray-50 dark:bg-gray-700 flex-shrink-0">
                                <template x-if="preview"><img :src="preview" class="w-full h-full object-cover"></template>
                                <template x-if="!preview"><svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></template>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="cursor-pointer px-3 py-1.5 text-xs font-semibold border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    Choose file
                                    <input type="file" name="favicon" accept="image/*" class="hidden" @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                                </label>
                                @if(!empty($s['favicon_url']))<a href="/admin/settings/favicon/remove" onclick="return confirm('Remove favicon?')" class="text-xs text-red-500 hover:underline">Remove</a>@endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Logos --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Site Logo</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Upload separate logos for dark and light backgrounds. Recommended 516×117 px.</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach([
                        ['logo_dark',  'logo_dark_url',  'Logo on dark background',  'bg-gray-800', 'NKHOJ', 'text-gray-400'],
                        ['logo_light', 'logo_light_url', 'Logo on light background', 'bg-white',    'NKHOJ', 'text-gray-700'],
                    ] as [$field, $key, $label, $bg, $placeholder, $textClass])
                    <div class="flex items-start gap-6 px-6 py-5" x-data="{ preview: '{{ $s[$key] ?? '' }}' }">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-3">{{ $label }}</p>
                            <form method="POST" action="/admin/settings/{{ str_replace('_', '-', $field) }}" enctype="multipart/form-data">@csrf
                                <div class="h-16 max-w-xs rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 overflow-hidden flex items-center justify-center {{ $bg }} px-4 mb-2">
                                    <template x-if="preview"><img :src="preview" class="max-h-10 max-w-full object-contain"></template>
                                    <template x-if="!preview"><span class="text-xs font-bold tracking-widest {{ $textClass }}">{{ $placeholder }}</span></template>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="cursor-pointer px-3 py-1.5 text-xs font-semibold border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        Replace
                                        <input type="file" name="{{ $field }}" accept="image/*" class="hidden" @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                                    </label>
                                    @if(!empty($s[$key]))<a href="/admin/settings/{{ str_replace('_', '-', $field) }}/remove" onclick="return confirm('Remove?')" class="text-xs text-red-500 hover:underline">Remove</a>@endif
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Compact Logos --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Compact Logos</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Used on mobile and collapsed sidebars. Usually a square icon/symbol.</p>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach([
                        ['logo_compact_dark',  'logo_compact_dark_url',  'Dark background', 'bg-gray-800'],
                        ['logo_compact_light', 'logo_compact_light_url', 'Light background', 'bg-white'],
                    ] as [$field, $key, $label, $bg])
                    <div x-data="{ preview: '{{ $s[$key] ?? '' }}' }">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">{{ $label }}</p>
                        <form method="POST" action="/admin/settings/{{ str_replace('_', '-', $field) }}" enctype="multipart/form-data">@csrf
                            <div class="h-14 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 flex items-center justify-center {{ $bg }} mb-2">
                                <template x-if="preview"><img :src="preview" class="max-h-10 max-w-full object-contain px-2"></template>
                                <template x-if="!preview"><svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg></template>
                            </div>
                            <label class="cursor-pointer inline-flex items-center gap-1.5 text-xs font-semibold border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Choose
                                <input type="file" name="{{ $field }}" accept="image/*" class="hidden" @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                            </label>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════ APPEARANCE ═════ --}}
        <div x-show="tab === 'appearance'" x-cloak class="space-y-4">
            <form method="POST" action="/admin/appearance" x-data="appearanceData()">
            @csrf

            {{-- Brand Color --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Brand Color</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Primary color for buttons, links, and highlights.</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="px-6 py-5">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="relative w-12 h-12 rounded-xl overflow-hidden shadow border border-gray-200 dark:border-gray-600 cursor-pointer flex-shrink-0" @click="$refs.cp.click()">
                                <div class="absolute inset-0" :style="'background:'+color"></div>
                                <input x-ref="cp" type="color" x-model="color" @input="syncHex()" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">HEX Value</p>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-gray-400 font-mono text-sm">#</span>
                                    <input type="text" x-model="hexInput" @input="syncColor()" maxlength="6"
                                        class="font-mono text-sm border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-900 dark:text-white w-24 focus:ring-2 focus:ring-brand-500 outline-none uppercase">
                                </div>
                            </div>
                        </div>
                        {{-- Presets --}}
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Presets</p>
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach(['#6366f1'=>'Indigo','#8b5cf6'=>'Violet','#ec4899'=>'Pink','#ef4444'=>'Red','#f97316'=>'Orange','#22c55e'=>'Green','#14b8a6'=>'Teal','#3b82f6'=>'Blue','#06b6d4'=>'Cyan','#111827'=>'Dark'] as $hex=>$name)
                            <button type="button" @click="setColor('{{ $hex }}')"
                                class="w-7 h-7 rounded-lg shadow-sm border-2 transition-all hover:scale-110"
                                :class="color === '{{ $hex }}' ? 'border-gray-900 dark:border-white scale-110' : 'border-transparent'"
                                style="background:{{ $hex }}" title="{{ $name }}"></button>
                            @endforeach
                        </div>
                        {{-- Live preview --}}
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Preview</p>
                        <div class="flex flex-wrap gap-3 items-center p-4 bg-gray-50 dark:bg-gray-900 rounded-xl">
                            <button type="button" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" :style="'background:'+color">Button</button>
                            <button type="button" class="px-4 py-2 rounded-lg text-sm font-semibold border-2" :style="'color:'+color+';border-color:'+color">Outline</button>
                            <span class="text-sm font-medium cursor-pointer hover:underline" :style="'color:'+color">Link</span>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full" :style="'background:'+color+'22;color:'+color">Badge</span>
                        </div>
                        <input type="hidden" name="brand_color" :value="color">
                    </div>
                </div>
            </div>

            {{-- Custom CSS --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Custom CSS</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Injected into every frontend page. Use carefully.</p>
                </div>
                <div class="px-6 py-5">
                    <textarea name="custom_css" rows="12" spellcheck="false"
                        class="w-full font-mono text-xs border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-500 outline-none resize-y"
                        placeholder="/* Add custom CSS here */">{{ $customCss }}</textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">Save Appearance</button>
            </div>
            </form>
        </div>

        {{-- ═══════════════════════════════════════════ AUTH ════ --}}
        <div x-show="tab === 'auth'" x-cloak class="space-y-4">
            <form method="POST" action="/admin/auth-settings">
            @csrf

            {{-- Registration --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Registration</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach([
                        ['disable_registration',      'Disable Registration',      'Prevent new users from signing up.',                  $auth['disable_registration']      ?? false],
                        ['require_email_confirmation','Email Confirmation',         'Users must verify their email before logging in.',    $auth['require_email_confirmation'] ?? false],
                        ['social_login_require_account','Require Account for Social','Social login only works if account already exists.', $auth['social_login_require_account'] ?? false],
                        ['single_device_login',       'Single Device Login',       'Users can only be logged in on one device at a time.', $auth['single_device_login']        ?? false],
                    ] as [$name, $label, $desc, $on])
                    <div class="flex items-center px-6 py-4" x-data="{ on: {{ $on ? 'true' : 'false' }} }">
                        <div class="flex-1 min-w-0 pr-6">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $label }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $desc }}</p>
                        </div>
                        <button type="button" @click="on = !on"
                            :class="on ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-600'"
                            class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full transition-colors duration-200">
                            <span :class="on ? 'translate-x-4' : 'translate-x-0.5'" class="inline-block mt-0.5 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200"></span>
                        </button>
                        <input type="hidden" name="{{ $name }}" :value="on ? '1' : '0'">
                    </div>
                    @endforeach
                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Domain Blacklist</p>
                        <p class="text-xs text-gray-400 mb-2">One domain per line. Registration blocked for these domains.</p>
                        <textarea name="domain_blacklist" rows="4" spellcheck="false"
                            class="w-full text-sm font-mono bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"
                            placeholder="spam.com&#10;mailinator.com">{{ $auth['domain_blacklist'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Social Login --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Social Login Providers</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Enable OAuth login via third-party providers.</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">

                    {{-- Google --}}
                    <div x-data="{ open: {{ !empty($auth['google_client_id']) ? 'true' : 'false' }}, enabled: {{ ($auth['google_login_enabled'] ?? false) ? 'true' : 'false' }} }">
                        <div class="flex items-center px-6 py-4">
                            <div class="w-8 h-8 rounded-lg bg-white border border-gray-200 dark:border-gray-600 flex items-center justify-center flex-shrink-0 mr-3 shadow-sm">
                                <svg class="w-4 h-4" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                            </div>
                            <div class="flex-1 min-w-0 pr-6">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Google Login</p>
                                <button type="button" @click="open = !open" class="text-xs text-brand-500 hover:underline mt-0.5" x-text="open ? 'Hide credentials ▲' : 'Configure credentials ▼'"></button>
                            </div>
                            <button type="button" @click="enabled = !enabled"
                                :class="enabled ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-600'"
                                class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full transition-colors duration-200">
                                <span :class="enabled ? 'translate-x-4' : 'translate-x-0.5'" class="inline-block mt-0.5 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200"></span>
                            </button>
                            <input type="hidden" name="google_login_enabled" :value="enabled ? '1' : '0'">
                        </div>
                        <div x-show="open" x-cloak class="px-6 pb-5 space-y-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700">
                            <div class="pt-3">
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Client ID</label>
                                <input type="text" name="google_client_id" value="{{ $auth['google_client_id'] ?? '' }}"
                                    class="w-full text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Client Secret</label>
                                <input type="password" name="google_client_secret" value="{{ $auth['google_client_secret'] ?? '' }}"
                                    class="w-full text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                            </div>
                        </div>
                    </div>

                    {{-- Facebook --}}
                    <div x-data="{ open: {{ !empty($auth['facebook_client_id']) ? 'true' : 'false' }}, enabled: {{ ($auth['facebook_login_enabled'] ?? false) ? 'true' : 'false' }} }">
                        <div class="flex items-center px-6 py-4">
                            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0 mr-3 shadow-sm">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0 pr-6">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Facebook Login</p>
                                <button type="button" @click="open = !open" class="text-xs text-brand-500 hover:underline mt-0.5" x-text="open ? 'Hide credentials ▲' : 'Configure credentials ▼'"></button>
                            </div>
                            <button type="button" @click="enabled = !enabled"
                                :class="enabled ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-600'"
                                class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full transition-colors duration-200">
                                <span :class="enabled ? 'translate-x-4' : 'translate-x-0.5'" class="inline-block mt-0.5 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200"></span>
                            </button>
                            <input type="hidden" name="facebook_login_enabled" :value="enabled ? '1' : '0'">
                        </div>
                        <div x-show="open" x-cloak class="px-6 pb-5 space-y-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700">
                            <div class="pt-3">
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">App ID</label>
                                <input type="text" name="facebook_client_id" value="{{ $auth['facebook_client_id'] ?? '' }}"
                                    class="w-full text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">App Secret</label>
                                <input type="password" name="facebook_client_secret" value="{{ $auth['facebook_client_secret'] ?? '' }}"
                                    class="w-full text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">Save Authentication</button>
            </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════════ EMAIL ════ --}}
        <div x-show="tab === 'email'" x-cloak class="space-y-4">

            {{-- Options --}}
            <form method="POST" action="/admin/email-settings">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Mail Options</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach([
                        ['email_verification','Email Verification','Require users to verify their email address.', $em['email_verification'] ?? false],
                    ] as [$name, $label, $desc, $on])
                    <div class="flex items-center px-6 py-4" x-data="{ on: {{ $on ? 'true' : 'false' }} }">
                        <div class="flex-1 min-w-0 pr-6">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $label }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $desc }}</p>
                        </div>
                        <button type="button" @click="on = !on" :class="on ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-600'" class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full transition-colors"><span :class="on ? 'translate-x-4' : 'translate-x-0.5'" class="inline-block mt-0.5 h-4 w-4 rounded-full bg-white shadow-sm transition-transform"></span></button>
                        <input type="hidden" name="{{ $name }}" :value="on ? '1' : '0'">
                    </div>
                    @endforeach
                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Forward Contact Messages To</p>
                        <p class="text-xs text-gray-400 mb-2">Email address that receives contact form submissions.</p>
                        <input type="email" name="contact_email" value="{{ $em['contact_email'] ?? '' }}" placeholder="admin@nkhoj.com"
                            class="w-full max-w-sm text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save</button>
                </div>
            </div>
            </form>

            {{-- SMTP --}}
            <form method="POST" action="/admin/email-settings/smtp">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">SMTP Configuration</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Outgoing mail server settings.</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Driver</label>
                            <select name="mail_service" class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                @foreach(['smtp'=>'SMTP','gmail_api'=>'Gmail API','mailgun'=>'Mailgun','sendgrid'=>'SendGrid','ses'=>'Amazon SES','log'=>'Log (testing)'] as $v=>$l)
                                <option value="{{ $v }}" {{ ($em['mail_service'] ?? 'smtp') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Encryption</label>
                            <select name="mail_encryption" class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="tls" {{ ($em['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($em['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ ($em['mail_encryption'] ?? '') === '' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Host</label>
                            <input type="text" name="mail_host" value="{{ $em['mail_host'] ?? '' }}" placeholder="smtp.gmail.com"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Port</label>
                            <input type="number" name="mail_port" value="{{ $em['mail_port'] ?? 587 }}"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Username</label>
                            <input type="text" name="mail_username" value="{{ $em['mail_username'] ?? '' }}"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Password</label>
                            <input type="password" name="mail_password" value="{{ $em['mail_password'] ?? '' }}"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">From Address</label>
                            <input type="email" name="mail_from_address" value="{{ $em['mail_from_address'] ?? '' }}" placeholder="no-reply@nkhoj.com"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">From Name</label>
                            <input type="text" name="mail_from_name" value="{{ $em['mail_from_name'] ?? ($s['site_name'] ?? 'nkhoj') }}"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save SMTP</button>
                </div>
            </div>
            </form>

            {{-- Test Email --}}
            <form method="POST" action="/admin/email-settings/test">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Send Test Email</h2>
                </div>
                <div class="flex items-center gap-3 px-6 py-4">
                    <input type="email" name="test_email" value="{{ auth()->user()->email }}" placeholder="test@example.com"
                        class="flex-1 max-w-xs text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <button type="submit" class="px-4 py-2 bg-gray-700 hover:bg-gray-900 dark:bg-gray-600 dark:hover:bg-gray-500 text-white text-sm font-semibold rounded-lg transition-colors">Send Test</button>
                </div>
            </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════ CONTENT ══════ --}}
        <div x-show="tab === 'content'" x-cloak class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Content Settings</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manage posts, formats, uploads, and AI generation.</p>
                </div>
                <div class="px-6 py-5 flex items-center gap-3">
                    <a href="/admin/content-settings" class="px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                        Open Content Settings →
                    </a>
                    <p class="text-xs text-gray-400">Manage posts per page, URL structure, post formats, file uploads, and AI content generation.</p>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════ SEO ══════ --}}
        <div x-show="tab === 'seo'" x-cloak class="space-y-4">
            <form method="POST" action="/admin/seo/settings">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Global Meta Tags</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Default meta tags used across all pages.</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Meta Title</p>
                        <input type="text" name="meta_title" value="{{ $s['meta_title'] ?? ($s['site_name'] ?? '') }}"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Meta Description</p>
                        <textarea name="meta_description" rows="2"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none">{{ $s['meta_description'] ?? '' }}</textarea>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Keywords</p>
                        <input type="text" name="meta_keywords" value="{{ $s['meta_keywords'] ?? '' }}" placeholder="news, nepal, nkhoj"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <a href="/admin/seo" class="text-sm text-brand-500 hover:underline">Advanced SEO (Sitemap, Robots.txt, Analytics tools) →</a>
                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save</button>
                </div>
            </div>
            </form>
        </div>

        {{-- ═══════════════════════════════════════ SECURITY ════ --}}
        <div x-show="tab === 'security'" x-cloak class="space-y-4">

            {{-- Login + Password --}}
            <form method="POST" action="/admin/security">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Login Security</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Max Login Attempts</p>
                            <p class="text-xs text-gray-400 mb-2">Failed attempts before account is locked.</p>
                            <input type="number" name="max_login_attempts" value="{{ $sec['max_login_attempts'] ?? 5 }}" min="1" max="100"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Lockout Duration <span class="font-normal text-gray-400">(minutes)</span></p>
                            <p class="text-xs text-gray-400 mb-2">How long to block the account after too many attempts.</p>
                            <input type="number" name="lockout_time" value="{{ $sec['lockout_time'] ?? 15 }}" min="1"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Min Password Length</p>
                            <input type="number" name="min_password_length" value="{{ $sec['min_password_length'] ?? 8 }}" min="4" max="128"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div class="flex items-center pt-6" x-data="{ on: {{ ($sec['password_complexity'] ?? false) ? 'true' : 'false' }} }">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Require Complexity</p>
                                <p class="text-xs text-gray-400 mt-0.5">Uppercase, number, and symbol required.</p>
                            </div>
                            <button type="button" @click="on = !on" :class="on ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-600'" class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full transition-colors"><span :class="on ? 'translate-x-4' : 'translate-x-0.5'" class="inline-block mt-0.5 h-4 w-4 rounded-full bg-white shadow-sm transition-transform"></span></button>
                            <input type="hidden" name="password_complexity" :value="on ? '1' : '0'">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Links in Posts</p>
                            <p class="text-xs text-gray-400 mb-2">How to handle links in user posts.</p>
                            <select name="post_links" class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="nofollow" {{ ($sec['post_links'] ?? 'nofollow') === 'nofollow' ? 'selected' : '' }}>Add nofollow</option>
                                <option value="remove"   {{ ($sec['post_links'] ?? '') === 'remove'   ? 'selected' : '' }}>Remove links</option>
                                <option value="allow"    {{ ($sec['post_links'] ?? '') === 'allow'    ? 'selected' : '' }}>Allow all</option>
                            </select>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-0.5">Links in Public Pages</p>
                            <p class="text-xs text-gray-400 mb-2">How to handle links in public page content.</p>
                            <select name="public_links" class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="nofollow" {{ ($sec['public_links'] ?? 'nofollow') === 'nofollow' ? 'selected' : '' }}>Add nofollow</option>
                                <option value="remove"   {{ ($sec['public_links'] ?? '') === 'remove'   ? 'selected' : '' }}>Remove links</option>
                                <option value="allow"    {{ ($sec['public_links'] ?? '') === 'allow'    ? 'selected' : '' }}>Allow all</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save Security</button>
                </div>
            </div>
            </form>

            {{-- Cron Token --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Cron Job Token</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Authenticate scheduled cron job requests.</p>
                </div>
                <div class="px-6 py-5">
                    @if($cronToken)
                    <div class="flex items-center gap-3 mb-4">
                        <code class="flex-1 text-xs font-mono bg-gray-100 dark:bg-gray-900 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 break-all">{{ $cronToken }}</code>
                    </div>
                    <p class="text-xs text-gray-400 mb-3">Add to your cron command: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">?token={{ $cronToken }}</code></p>
                    <form method="POST" action="/admin/security/cron-token/revoke" class="inline">@csrf
                        <button class="px-3 py-1.5 text-xs font-semibold text-red-500 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">Revoke Token</button>
                    </form>
                    @else
                    <p class="text-sm text-gray-400 mb-3">No cron token has been generated yet.</p>
                    <form method="POST" action="/admin/security/cron-token/generate">@csrf
                        <button class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Generate Token</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════ INFRASTRUCTURE ════ --}}
        <div x-show="tab === 'infra'" x-cloak class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Infrastructure</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manage server-level operations.</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach([
                        ['/admin/cache',          'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',                           'Cache Management',  'Clear application cache, view cache stats, and manage cached data.'],
                        ['/admin/storage',        'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4', 'File Storage',      'Manage disk storage, uploaded files, and storage drivers.'],
                        ['/admin/queue-settings', 'M4 6h16M4 10h16M4 14h16M4 18h16',                                                                                                         'Queue Settings',    'Configure background job queues and worker settings.'],
                        ['/admin/deploy',         'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',                                                                         'Deployment',        'Run deployment scripts and apply pending updates.'],
                        ['/admin/backup',         'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',                                                                         'Database Backup',   'Create and download database backups.'],
                        ['/admin/logs',           'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',        'System Logs',       'View application error logs and activity records.'],
                    ] as [$url, $icon, $title, $desc])
                    <a href="{{ $url }}" class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors group">
                        <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0 group-hover:bg-brand-50 dark:group-hover:bg-brand-900/20 transition-colors">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 group-hover:text-brand-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ $title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $desc }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-brand-400 flex-shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

    </div>{{-- end right content --}}
</div>{{-- end flex container --}}

<script>
function appearanceData() {
    return {
        color: '{{ $brandColor }}',
        hexInput: '{{ ltrim($brandColor, '#') }}',
        setColor(hex) { this.color = hex; this.hexInput = hex.replace('#',''); },
        syncHex() { this.hexInput = this.color.replace('#','').toUpperCase(); },
        syncColor() { const v = this.hexInput.replace(/[^0-9a-fA-F]/g,''); if (v.length === 6) this.color = '#' + v; },
    }
}
</script>
@endsection
