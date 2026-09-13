@extends('layouts.admin')
@section('title', 'Settings')

@section('content')
@php
    $settings = $s ?? [];
    $auth     = $settings['auth']       ?? [];
    $em       = $settings['email']      ?? [];
    $sec      = $settings['security']   ?? [];
    $cap      = $settings['captcha']    ?? [];
    $ap       = $settings['appearance'] ?? [];
    $brandColor = $ap['brand_color'] ?? '#6366f1';
    $customCss  = $ap['custom_css']  ?? '';
    $cronToken  = $settings['cron_token'] ?? null;

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

@if(session('success'))
<div class="mb-4 flex items-center gap-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-400 text-sm rounded-xl px-4 py-3">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 flex items-center gap-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-400 text-sm rounded-xl px-4 py-3">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    {{ session('error') }}
</div>
@endif

<div class="flex gap-6" x-data="{ tab: new URLSearchParams(window.location.search).get('tab') || 'general' }">

    {{-- ── LEFT TAB NAV ───────────────────────────────────────────── --}}
    <div class="w-52 flex-shrink-0">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden sticky top-6">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Configuration</p>
            </div>
            <nav class="py-1.5">
                @foreach($tabs as $key => $t)
                <button @click="tab = '{{ $key }}'; history.replaceState(null,'','?tab={{ $key }}')"
                    :class="tab === '{{ $key }}'
                        ? 'bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 border-l-2 border-brand-500'
                        : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/40 border-l-2 border-transparent'"
                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium transition-all text-left">
                    <svg class="w-4 h-4 flex-shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $t['icon'] }}"/>
                    </svg>
                    {{ $t['label'] }}
                </button>
                @endforeach
            </nav>
        </div>
    </div>

    {{-- ── RIGHT CONTENT ──────────────────────────────────────────── --}}
    <div class="flex-1 min-w-0 space-y-4">

        {{-- ══════════════════════════════════════════════════════════
             GENERAL
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'general'" x-cloak class="space-y-px">

            {{-- Site URL --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Site URL</p>
                    <p class="text-xs text-gray-400 leading-relaxed">The primary domain for your site.</p>
                </div>
                <form method="POST" action="/admin/settings/url" class="flex-1 max-w-lg">@csrf
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Primary site url</label>
                    <input type="url" name="site_url" value="{{ $settings['site_url'] ?? url('/') }}"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <button class="mt-2 px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors">Save</button>
                </form>
            </div>

            {{-- Site Name --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Site Name</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Appears in browser tabs, SEO tags, and app title.</p>
                </div>
                <form method="POST" action="/admin/settings/name" class="flex-1 max-w-lg">@csrf
                    <input type="text" name="site_name" value="{{ $settings['site_name'] ?? 'nkhoj' }}"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <button class="mt-2 px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors">Save</button>
                </form>
            </div>

            {{-- Tagline & Description --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Tagline & Description</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Shown in meta descriptions and the site header.</p>
                </div>
                <form method="POST" action="/admin/settings/tagline" class="flex-1 max-w-lg space-y-3">@csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Tagline (English)</label>
                        <input type="text" name="tagline_en" value="{{ $settings['tagline_en'] ?? "Nepal's leading multi-blog & news platform" }}"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Tagline (Nepali)</label>
                        <input type="text" name="tagline_ne" value="{{ $settings['tagline_ne'] ?? 'नेपालको अग्रणी बहु-ब्लग र समाचार प्लेटफर्म' }}"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Site Description (SEO)</label>
                        <textarea name="site_description" rows="2"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none">{{ $settings['site_description'] ?? '' }}</textarea>
                    </div>
                    <button class="px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors">Save</button>
                </form>
            </div>

            {{-- Contact Email --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Contact Email</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Shown publicly and used for system notifications.</p>
                </div>
                <form method="POST" action="/admin/settings/contact" class="flex-1 max-w-lg">@csrf
                    <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" placeholder="hello@nkhoj.com"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <button class="mt-2 px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors">Save</button>
                </form>
            </div>

            {{-- Social Links --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Social Links</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Links shown in site footer and about page.</p>
                </div>
                <form method="POST" action="/admin/settings/social" class="flex-1 max-w-lg space-y-2">@csrf
                    @foreach(['facebook'=>'Facebook','twitter'=>'X (Twitter)','instagram'=>'Instagram','youtube'=>'YouTube','tiktok'=>'TikTok','linkedin'=>'LinkedIn'] as $k=>$lbl)
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-0.5 block">{{ $lbl }}</label>
                        <input type="url" name="social_{{ $k }}" value="{{ $settings['social_'.$k] ?? '' }}"
                            placeholder="https://{{ strtolower($lbl) === 'x (twitter)' ? 'x.com' : strtolower($lbl).'.com' }}/nkhoj"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    @endforeach
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-0.5 block">Reader Count</label>
                            <input type="text" name="social_reader_count" value="{{ $settings['social_reader_count'] ?? '' }}" placeholder="12,400+"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-0.5 block">Reader Label</label>
                            <input type="text" name="social_reader_label" value="{{ $settings['social_reader_label'] ?? '' }}" placeholder="monthly readers"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>
                    <button class="px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors">Save</button>
                </form>
            </div>

            {{-- Analytics --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Analytics & Ads</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Connect analytics and ad networks.</p>
                </div>
                <form method="POST" action="/admin/settings/analytics" class="flex-1 max-w-lg space-y-3">@csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Google Analytics ID <span class="font-normal">(G-XXXXXXXXXX)</span></label>
                        <input type="text" name="ga_id" value="{{ $settings['ga_id'] ?? '' }}" placeholder="G-XXXXXXXXXX"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Google AdSense ID <span class="font-normal">(ca-pub-XXXXXXXX)</span></label>
                        <input type="text" name="adsense_id" value="{{ $settings['adsense_id'] ?? '' }}" placeholder="ca-pub-XXXXXXXX"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <button class="px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors">Save</button>
                </form>
            </div>

            {{-- Behaviour Toggles --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Content Behaviour</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Toggle site-wide features on or off.</p>
                </div>
                <form method="POST" action="/admin/settings/behaviour" class="flex-1 max-w-lg space-y-3">@csrf
                    @foreach(['allow_guest_comments'=>['Guest Comments','Let non-registered users post comments'],'require_post_approval'=>['Post Approval','New posts require admin approval'],'show_breaking_news'=>['Breaking News Ticker','Show ticker on homepage']] as $k=>[$lbl,$desc])
                    <div class="flex items-center justify-between" x-data="{ on: {{ ($settings[$k] ?? false) ? 'true' : 'false' }} }">
                        <div><p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $lbl }}</p><p class="text-xs text-gray-400">{{ $desc }}</p></div>
                        <div class="relative w-11 h-6 cursor-pointer flex-shrink-0 ml-4" @click="on = !on">
                            <input type="checkbox" name="{{ $k }}" value="1" class="sr-only" :checked="on" @change="$el.closest('form').submit()">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-5' : ''"></div>
                        </div>
                    </div>
                    @endforeach
                </form>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             BRANDING
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'branding'" x-cloak class="space-y-px">

            {{-- Favicon --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Favicon</p>
                    <p class="text-xs text-gray-400 leading-relaxed">At least 512×512 px. PNG or ICO recommended.</p>
                </div>
                <div class="flex-1 max-w-lg">
                    <div x-data="{ preview: '{{ $settings['favicon_url'] ?? '' }}' }">
                        <form method="POST" action="/admin/settings/favicon" enctype="multipart/form-data">@csrf
                            <div class="flex items-end gap-4">
                                <div class="w-16 h-16 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 overflow-hidden flex items-center justify-center bg-gray-50 dark:bg-gray-700 flex-shrink-0">
                                    <template x-if="preview"><img :src="preview" class="w-full h-full object-cover"></template>
                                    <template x-if="!preview"><svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></template>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="cursor-pointer px-3 py-1.5 text-xs font-bold border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        Replace<input type="file" name="favicon" accept="image/*" class="hidden" @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                                    </label>
                                    @if(!empty($settings['favicon_url']))<a href="/admin/settings/favicon/remove" onclick="return confirm('Remove favicon?')" class="text-xs text-red-500 hover:underline">Remove</a>@endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Dark Logo --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Logo (Dark background)</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Used on dark backgrounds. Recommended 516×117 px.</p>
                </div>
                <div class="flex-1 max-w-lg" x-data="{ preview: '{{ $settings['logo_dark_url'] ?? '' }}' }">
                    <form method="POST" action="/admin/settings/logo-dark" enctype="multipart/form-data">@csrf
                        <div class="h-16 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 overflow-hidden flex items-center justify-center bg-gray-800 px-4 mb-2">
                            <template x-if="preview"><img :src="preview" class="max-h-10 max-w-full object-contain"></template>
                            <template x-if="!preview"><span class="text-xs text-gray-500 font-bold tracking-widest">NKHOJ</span></template>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="cursor-pointer px-3 py-1.5 text-xs font-bold border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Replace<input type="file" name="logo_dark" accept="image/*" class="hidden" @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                            </label>
                            @if(!empty($settings['logo_dark_url']))<a href="/admin/settings/logo-dark/remove" onclick="return confirm('Remove?')" class="text-xs text-red-500 hover:underline">Remove</a>@endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Light Logo --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Logo (Light background)</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Used on light backgrounds. Same size as dark logo.</p>
                </div>
                <div class="flex-1 max-w-lg" x-data="{ preview: '{{ $settings['logo_light_url'] ?? '' }}' }">
                    <form method="POST" action="/admin/settings/logo-light" enctype="multipart/form-data">@csrf
                        <div class="h-16 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 overflow-hidden flex items-center justify-center bg-white px-4 mb-2">
                            <template x-if="preview"><img :src="preview" class="max-h-10 max-w-full object-contain"></template>
                            <template x-if="!preview"><span class="text-xs text-gray-800 font-bold tracking-widest">NKHOJ</span></template>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="cursor-pointer px-3 py-1.5 text-xs font-bold border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Replace<input type="file" name="logo_light" accept="image/*" class="hidden" @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                            </label>
                            @if(!empty($settings['logo_light_url']))<a href="/admin/settings/logo-light/remove" onclick="return confirm('Remove?')" class="text-xs text-red-500 hover:underline">Remove</a>@endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Compact Logos --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Compact Logos</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Shown on mobile or narrow screens. Usually an icon/symbol only.</p>
                </div>
                <div class="flex-1 max-w-lg grid grid-cols-2 gap-4">
                    <div x-data="{ preview: '{{ $settings['logo_compact_dark_url'] ?? '' }}' }">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">Dark logo</p>
                        <form method="POST" action="/admin/settings/logo-compact-dark" enctype="multipart/form-data">@csrf
                            <div class="h-12 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 flex items-center justify-center bg-gray-800 mb-1.5">
                                <template x-if="preview"><img :src="preview" class="max-h-9 max-w-full object-contain"></template>
                                <template x-if="!preview"><svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg></template>
                            </div>
                            <label class="cursor-pointer flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Choose<input type="file" name="logo_compact_dark" accept="image/*" class="hidden" @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                            </label>
                        </form>
                    </div>
                    <div x-data="{ preview: '{{ $settings['logo_compact_light_url'] ?? '' }}' }">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">Light logo</p>
                        <form method="POST" action="/admin/settings/logo-compact-light" enctype="multipart/form-data">@csrf
                            <div class="h-12 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 flex items-center justify-center bg-white mb-1.5">
                                <template x-if="preview"><img :src="preview" class="max-h-9 max-w-full object-contain"></template>
                                <template x-if="!preview"><svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg></template>
                            </div>
                            <label class="cursor-pointer flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Choose<input type="file" name="logo_compact_light" accept="image/*" class="hidden" @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                            </label>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             APPEARANCE
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'appearance'" x-cloak>
            <form method="POST" action="/admin/appearance" x-data="appearanceSettings()" @submit="applyPreview()">
            @csrf
            <div class="space-y-px">

                {{-- Color picker --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                    <div class="w-full sm:w-64 flex-shrink-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Brand Color</p>
                        <p class="text-xs text-gray-400 leading-relaxed">Used for buttons, links, and accents across the site.</p>
                    </div>
                    <div class="flex-1 max-w-lg space-y-4">
                        {{-- picker + hex --}}
                        <div class="flex items-center gap-4">
                            <div class="relative w-12 h-12 rounded-xl overflow-hidden shadow border border-gray-200 dark:border-gray-600 cursor-pointer flex-shrink-0" @click="$refs.cp.click()">
                                <div class="absolute inset-0" :style="'background:'+color"></div>
                                <input x-ref="cp" type="color" x-model="color" @input="syncHex()" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">HEX</label>
                                <div class="flex items-center gap-1">
                                    <span class="text-gray-400 text-sm font-mono">#</span>
                                    <input type="text" x-model="hexInput" @input="syncColor()" maxlength="6"
                                        class="font-mono text-sm border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-24 focus:ring-2 focus:ring-brand-500 outline-none uppercase">
                                </div>
                            </div>
                            <input type="hidden" name="brand_color" :value="color">
                        </div>
                        {{-- presets --}}
                        <div>
                            <p class="text-xs font-semibold text-gray-400 mb-2">Presets</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach(['#6366f1'=>'Indigo','#8b5cf6'=>'Violet','#ec4899'=>'Pink','#ef4444'=>'Red','#f97316'=>'Orange','#eab308'=>'Yellow','#22c55e'=>'Green','#14b8a6'=>'Teal','#3b82f6'=>'Blue','#06b6d4'=>'Cyan','#64748b'=>'Slate','#111827'=>'Dark'] as $hex=>$name)
                                <button type="button" @click="setColor('{{ $hex }}')"
                                    class="w-7 h-7 rounded-lg shadow border-2 transition-all hover:scale-110"
                                    :class="color === '{{ $hex }}' ? 'border-gray-900 dark:border-white scale-110' : 'border-transparent'"
                                    style="background:{{ $hex }}" title="{{ $name }}"></button>
                                @endforeach
                            </div>
                        </div>
                        {{-- preview --}}
                        <div class="flex flex-wrap gap-2 items-center p-3 bg-gray-50 dark:bg-gray-900 rounded-xl">
                            <button type="button" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white" :style="'background:'+color">Button</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg text-xs font-semibold border-2" :style="'color:'+color+';border-color:'+color">Outline</button>
                            <span class="text-xs font-medium cursor-pointer hover:underline" :style="'color:'+color">Link</span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full" :style="'background:'+color+'22;color:'+color">Badge</span>
                        </div>
                    </div>
                </div>

                {{-- Custom CSS --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                    <div class="w-full sm:w-64 flex-shrink-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Custom CSS</p>
                        <p class="text-xs text-gray-400 leading-relaxed">Injected into every frontend page. Invalid CSS can break layout.</p>
                    </div>
                    <div class="flex-1 max-w-lg">
                        <textarea name="custom_css" rows="10" spellcheck="false"
                            class="w-full font-mono text-xs border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-500 outline-none resize-y leading-relaxed"
                            placeholder="/* Add custom CSS here */">{{ $customCss }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">Save Appearance</button>
                </div>
            </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             AUTHENTICATION
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'auth'" x-cloak>
            <form method="POST" action="/admin/auth-settings" class="space-y-px">@csrf

                {{-- Registration --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                    <div class="w-full sm:w-64 flex-shrink-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Registration</p>
                        <p class="text-xs text-gray-400 leading-relaxed">Control who can create accounts.</p>
                    </div>
                    <div class="flex-1 max-w-lg space-y-4">
                        @foreach(['disable_registration'=>['Disable Registration','All registration will be hidden from users.'],'require_email_confirmation'=>['Require Email Confirmation','Users must verify email before logging in.'],'social_login_require_account'=>['Social Login Requires Account','Social login only works for users who pre-connected it.'],'single_device_login'=>['Single Device Login','Logging in on a new device logs out all others.']] as $k=>[$lbl,$desc])
                        <div class="flex items-center justify-between" x-data="{ on: {{ ($auth[$k] ?? false) ? 'true' : 'false' }} }">
                            <div><p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $lbl }}</p><p class="text-xs text-gray-400 mt-0.5">{{ $desc }}</p></div>
                            <div class="flex-shrink-0 ml-4 relative w-12 h-6 cursor-pointer" @click="on = !on">
                                <input type="hidden" name="{{ $k }}" value="0">
                                <input type="checkbox" name="{{ $k }}" value="1" class="sr-only" :checked="on" @click.stop>
                                <div class="w-12 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-6' : ''"></div>
                            </div>
                        </div>
                        @endforeach
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Domain Blacklist <span class="font-normal text-gray-400">(comma-separated)</span></label>
                            <textarea name="domain_blacklist" rows="2" placeholder="spam.com, disposable.org"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none">{{ $auth['domain_blacklist'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Google Login --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6"
                     x-data="{ gopen: {{ ($auth['google_login_enabled'] ?? false) ? 'true' : 'false' }} }">
                    <div class="w-full sm:w-64 flex-shrink-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Google Login</p>
                        <p class="text-xs text-gray-400 leading-relaxed">OAuth via Google Cloud Console.</p>
                    </div>
                    <div class="flex-1 max-w-lg space-y-3">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Enable Google login</p>
                            <div class="relative w-12 h-6 cursor-pointer flex-shrink-0 ml-4" @click="gopen = !gopen">
                                <input type="hidden" name="google_login_enabled" value="0">
                                <input type="checkbox" name="google_login_enabled" value="1" class="sr-only" :checked="gopen" @click.stop>
                                <div class="w-12 h-6 rounded-full transition-colors" :class="gopen ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="gopen ? 'translate-x-6' : ''"></div>
                            </div>
                        </div>
                        <div x-show="gopen" x-cloak class="space-y-2 border-t border-gray-50 dark:border-gray-700 pt-3">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Google Client ID</label>
                                <input type="text" name="google_client_id" value="{{ old('google_client_id', $auth['google_client_id'] ?? '') }}"
                                    placeholder="442813778020-xxxxxxxx.apps.googleusercontent.com"
                                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Google Client Secret</label>
                                <input type="password" name="google_client_secret" value="{{ old('google_client_secret', $auth['google_client_secret'] ?? '') }}"
                                    placeholder="GOCSPX-xxxxxxxxxxxxxxxxxxxxxxxx"
                                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                            </div>
                            <p class="text-xs text-gray-400">Redirect URI: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{{ url('/auth/google/callback') }}</code></p>
                        </div>
                    </div>
                </div>

                {{-- Facebook Login --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6"
                     x-data="{ fopen: {{ ($auth['facebook_login_enabled'] ?? false) ? 'true' : 'false' }} }">
                    <div class="w-full sm:w-64 flex-shrink-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Facebook Login</p>
                        <p class="text-xs text-gray-400 leading-relaxed">OAuth via Facebook Developers portal.</p>
                    </div>
                    <div class="flex-1 max-w-lg space-y-3">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Enable Facebook login</p>
                            <div class="relative w-12 h-6 cursor-pointer flex-shrink-0 ml-4" @click="fopen = !fopen">
                                <input type="hidden" name="facebook_login_enabled" value="0">
                                <input type="checkbox" name="facebook_login_enabled" value="1" class="sr-only" :checked="fopen" @click.stop>
                                <div class="w-12 h-6 rounded-full transition-colors" :class="fopen ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="fopen ? 'translate-x-6' : ''"></div>
                            </div>
                        </div>
                        <div x-show="fopen" x-cloak class="space-y-2 border-t border-gray-50 dark:border-gray-700 pt-3">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Facebook App ID</label>
                                <input type="text" name="facebook_client_id" value="{{ old('facebook_client_id', $auth['facebook_client_id'] ?? '') }}"
                                    placeholder="1234567890123456"
                                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Facebook App Secret</label>
                                <input type="password" name="facebook_client_secret" value="{{ old('facebook_client_secret', $auth['facebook_client_secret'] ?? '') }}"
                                    placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                            </div>
                            <p class="text-xs text-gray-400">Redirect URI: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{{ url('/auth/facebook/callback') }}</code></p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">Save Authentication</button>
                </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             EMAIL
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'email'" x-cloak class="space-y-px">

            {{-- Options --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Options</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Email verification and contact forwarding.</p>
                </div>
                <form method="POST" action="/admin/email-settings" class="flex-1 max-w-lg space-y-4">@csrf
                    @foreach(['email_verification'=>['email_verification','Email Verification','Require users to verify email after registration.'],'contact_forward'=>['contact_forward','Forward Contact Messages','Send contact form submissions to an inbox.']] as $k=>[$fname,$lbl,$desc])
                    <div class="flex items-center justify-between" x-data="{ on: {{ ($em[str_replace('email_','',$fname)] ?? $em['contact_forward'] ?? false) ? 'true' : 'false' }} }">
                        <div><p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $lbl }}</p><p class="text-xs text-gray-400 mt-0.5">{{ $desc }}</p></div>
                        <div class="flex-shrink-0 ml-4 relative w-12 h-6 cursor-pointer" @click="on = !on">
                            <input type="hidden" name="{{ $fname }}" value="0">
                            <input type="checkbox" name="{{ $fname }}" value="1" class="sr-only" :checked="on" @click.stop>
                            <div class="w-12 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-6' : ''"></div>
                        </div>
                    </div>
                    @endforeach
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Contact Forward Email</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $em['contact_email'] ?? '') }}"
                            placeholder="admin@yourdomain.com"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <button class="px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors">Save</button>
                </form>
            </div>

            {{-- SMTP --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">SMTP Settings</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Configure outgoing mail server credentials.</p>
                </div>
                <form method="POST" action="/admin/email-settings/smtp" class="flex-1 max-w-lg space-y-3">@csrf
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Service</label>
                            <select name="mail_service" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                @foreach(['smtp'=>'Custom SMTP','gmail-api'=>'Gmail API','mailgun'=>'Mailgun','ses'=>'Amazon SES','postmark'=>'Postmark','resend'=>'Resend','sendmail'=>'Sendmail','mailpit'=>'Mailpit'] as $v=>$l)
                                <option value="{{ $v }}" {{ ($em['service'] ?? 'smtp') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Protocol</label>
                            <select name="mail_protocol" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="smtp" {{ ($em['protocol'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="sendmail" {{ ($em['protocol'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="phpmail" {{ ($em['protocol'] ?? '') === 'phpmail' ? 'selected' : '' }}>PHPMail</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Encryption</label>
                            <select name="mail_encryption" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="tls" {{ ($em['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($em['encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="none" {{ ($em['encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-3">
                        <div class="col-span-3">
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Host</label>
                            <input type="text" name="mail_host" value="{{ old('mail_host', $em['host'] ?? '') }}" placeholder="smtp.mailgun.org"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Port</label>
                            <input type="number" name="mail_port" value="{{ old('mail_port', $em['port'] ?? 587) }}" placeholder="587"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3" x-data="{ showPw: false }">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Username</label>
                            <input type="email" name="mail_username" value="{{ old('mail_username', $em['username'] ?? '') }}" placeholder="user@domain.com"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Password</label>
                            <div class="relative">
                                <input :type="showPw ? 'text' : 'password'" name="mail_password" placeholder="Leave blank to keep current"
                                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <button type="button" @click="showPw = !showPw" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">From Address</label>
                            <input type="email" name="mail_from" value="{{ old('mail_from', $em['from_address'] ?? '') }}" placeholder="no-reply@domain.com"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">From Name</label>
                            <input type="text" name="mail_title" value="{{ old('mail_title', $em['title'] ?? config('app.name')) }}"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Reply-To</label>
                            <input type="email" name="reply_to" value="{{ old('reply_to', $em['reply_to'] ?? '') }}" placeholder="support@domain.com"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>
                    <div class="flex justify-between items-center pt-1">
                        <button type="submit" class="px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors">Save SMTP</button>
                    </div>
                </form>
            </div>

            {{-- Test Email --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Send Test Email</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Verify your mail server is working correctly.</p>
                </div>
                <form method="POST" action="/admin/email-settings/test" class="flex-1 max-w-lg flex items-end gap-3">@csrf
                    <div class="flex-1">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Recipient Email</label>
                        <input type="email" name="test_email" required value="{{ auth()->user()->email }}" placeholder="test@example.com"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold rounded-lg transition-colors flex-shrink-0">Send Test</button>
                </form>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             CONTENT
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'content'" x-cloak class="space-y-px">

            {{-- General toggles --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">General</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Homepage and display settings.</p>
                </div>
                <form method="POST" action="/admin/content-settings?tab=general" class="flex-1 max-w-lg space-y-3">@csrf
                    @foreach(['show_featured_section'=>['Featured Section','Show featured slider on homepage'],'comment_system'=>['Comments','Enable site-wide commenting'],'comment_approval'=>['Comment Approval','Require approval before comments go live'],'emoji_reactions'=>['Emoji Reactions','Allow emoji reactions on posts'],'show_latest_posts'=>['Latest Posts on Homepage','Show latest posts section on homepage']] as $k=>[$lbl,$desc])
                    <div class="flex items-center justify-between" x-data="{ on: {{ ($settings[$k] ?? true) ? 'true' : 'false' }} }">
                        <div><p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $lbl }}</p><p class="text-xs text-gray-400">{{ $desc }}</p></div>
                        <div class="relative w-11 h-6 cursor-pointer flex-shrink-0 ml-4" @click="on = !on">
                            <input type="hidden" name="{{ $k }}" :value="on ? '1' : '0'">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-5' : ''"></div>
                        </div>
                    </div>
                    @endforeach
                    <div class="flex items-center justify-between pt-1">
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Posts per page</label>
                        <input type="number" name="posts_per_page" value="{{ $settings['posts_per_page'] ?? 16 }}" min="4" max="100"
                            class="w-20 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500 text-right">
                    </div>
                    <button class="px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors">Save</button>
                </form>
            </div>

            {{-- Posts --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Posts</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Post display and moderation rules.</p>
                </div>
                <form method="POST" action="/admin/content-settings?tab=posts" class="flex-1 max-w-lg space-y-3">@csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Post URL Structure</label>
                        <select name="post_url_structure" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="slug" {{ ($settings['post_url_structure'] ?? 'slug') === 'slug' ? 'selected' : '' }}>Slug (domain.com/slug)</option>
                            <option value="id"   {{ ($settings['post_url_structure'] ?? '') === 'id' ? 'selected' : '' }}>ID (domain.com/123)</option>
                        </select>
                    </div>
                    @foreach(['show_post_author'=>['Show Author','Display author name on posts'],'show_post_date'=>['Show Date','Display publish date on posts'],'show_post_view_count'=>['Show View Count','Display view count on posts'],'require_approval_new'=>['Approval for New Posts','Admin must approve new posts'],'require_approval_edited'=>['Approval for Edits','Admin must approve edited posts']] as $k=>[$lbl,$desc])
                    <div class="flex items-center justify-between" x-data="{ on: {{ ($settings[$k] ?? false) ? 'true' : 'false' }} }">
                        <div><p class="text-sm text-gray-700 dark:text-gray-200">{{ $lbl }}</p><p class="text-xs text-gray-400">{{ $desc }}</p></div>
                        <div class="relative w-11 h-6 cursor-pointer flex-shrink-0 ml-4" @click="on = !on">
                            <input type="hidden" name="{{ $k }}" :value="on ? '1' : '0'">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-5' : ''"></div>
                        </div>
                    </div>
                    @endforeach
                    <button class="px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors">Save</button>
                </form>
            </div>

            {{-- Post Formats --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Post Formats</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Enable or disable content formats site-wide.</p>
                </div>
                <form method="POST" action="/admin/content-settings?tab=post_formats" class="flex-1 max-w-lg space-y-2">@csrf
                    @foreach(['article'=>'Article','gallery'=>'Gallery','sorted_list'=>'Sorted List','table_of_contents'=>'Table of Contents','video'=>'Video','audio'=>'Audio','trivia_quiz'=>'Trivia Quiz','personality_quiz'=>'Personality Quiz','poll'=>'Poll','recipe'=>'Recipe','event'=>'Event'] as $fk=>$fl)
                    <div class="flex items-center justify-between py-1.5 border-b border-gray-50 dark:border-gray-700 last:border-0"
                         x-data="{ on: {{ ($settings['formats_enabled'][$fk] ?? true) ? 'true' : 'false' }} }">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $fl }}</span>
                        <div class="relative w-11 h-6 cursor-pointer flex-shrink-0 ml-4" @click="on = !on">
                            <input type="hidden" name="formats_enabled[{{ $fk }}]" :value="on ? '1' : '0'">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-5' : ''"></div>
                        </div>
                    </div>
                    @endforeach
                    <button class="px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors mt-1">Save</button>
                </form>
            </div>

            {{-- AI Content --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">AI Content</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Auto-generate posts using Gemini or ChatGPT.</p>
                </div>
                <form method="POST" action="/admin/content-settings/ai" class="flex-1 max-w-lg space-y-3">@csrf
                    <div class="flex items-center justify-between" x-data="{ on: {{ ($settings['ai_enabled'] ?? false) ? 'true' : 'false' }} }">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">AI Generator Status</p>
                        <div class="relative w-11 h-6 cursor-pointer flex-shrink-0 ml-4" @click="on = !on">
                            <input type="hidden" name="ai_enabled" :value="on ? '1' : '0'">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-5' : ''"></div>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Provider</label>
                        <select name="ai_provider" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="gemini" {{ ($settings['ai_provider'] ?? 'gemini') === 'gemini' ? 'selected' : '' }}>Gemini (Google)</option>
                            <option value="chatgpt" {{ ($settings['ai_provider'] ?? '') === 'chatgpt' ? 'selected' : '' }}>ChatGPT (OpenAI)</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">API Key</label>
                        <input type="password" name="ai_api_key" value="{{ $settings['ai_api_key'] ?? '' }}" placeholder="••••••••"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <button class="px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors">Save</button>
                </form>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             SEO
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'seo'" x-cloak class="space-y-px">

            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Global Meta</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Site-wide keywords and default social share image.</p>
                </div>
                <form method="POST" action="/admin/seo/meta" class="flex-1 max-w-lg space-y-3">@csrf
                    <input type="hidden" name="section" value="global">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Site Keywords <span class="font-normal">(comma-separated)</span></label>
                        <input type="text" name="site_keywords" value="{{ $settings['site_keywords'] ?? '' }}"
                            placeholder="नेपाल, समाचार, ब्लग, news, nepal"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Default OG Image URL</label>
                        <input type="url" name="og_default_image" value="{{ $settings['og_default_image'] ?? '' }}"
                            placeholder="https://example.com/og-cover.jpg"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <p class="text-xs text-gray-400 mt-1">Recommended 1200×630 px.</p>
                    </div>
                    <button class="px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors">Save</button>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Advanced SEO Tools</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Sitemap, robots.txt, structured data, and more.</p>
                </div>
                <div class="flex-1 max-w-lg flex items-center">
                    <a href="/admin/seo" class="inline-flex items-center gap-2 text-sm text-brand-600 dark:text-brand-400 hover:underline font-medium">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Open full SEO settings
                    </a>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             SECURITY
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'security'" x-cloak class="space-y-px">

            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Login Security</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Brute-force and lockout protection.</p>
                </div>
                <form method="POST" action="/admin/security" class="flex-1 max-w-lg space-y-4">@csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Max Login Attempts</label>
                            <input type="number" name="max_login_attempts" min="1" max="100" value="{{ old('max_login_attempts', $sec['max_login_attempts'] ?? 5) }}"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Lockout Time (minutes)</label>
                            <input type="number" name="lockout_time" min="1" max="1440" value="{{ old('lockout_time', $sec['lockout_time'] ?? 5) }}"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Min Password Length</label>
                            <input type="number" name="min_password_length" min="4" max="128" value="{{ old('min_password_length', $sec['min_password_length'] ?? 8) }}"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">Post Links</label>
                            <select name="post_links" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="nofollow" {{ ($sec['post_links'] ?? 'nofollow') === 'nofollow' ? 'selected' : '' }}>Nofollow (SEO Safe)</option>
                                <option value="remove" {{ ($sec['post_links'] ?? '') === 'remove' ? 'selected' : '' }}>Remove Links</option>
                                <option value="allow" {{ ($sec['post_links'] ?? '') === 'allow' ? 'selected' : '' }}>Allow All</option>
                                <option value="blank" {{ ($sec['post_links'] ?? '') === 'blank' ? 'selected' : '' }}>Open in New Tab</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center justify-between" x-data="{ on: {{ ($sec['password_complexity'] ?? false) ? 'true' : 'false' }} }">
                        <div><p class="text-sm text-gray-700 dark:text-gray-200">Require complex passwords</p><p class="text-xs text-gray-400">Numbers and special characters required</p></div>
                        <div class="relative w-11 h-6 cursor-pointer flex-shrink-0 ml-4" @click="on = !on">
                            <input type="hidden" name="password_complexity" value="0">
                            <input type="checkbox" name="password_complexity" value="1" class="sr-only" :checked="on">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="on ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-5' : ''"></div>
                        </div>
                    </div>
                    <button type="submit" class="px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg transition-colors">Save</button>
                </form>
            </div>

            {{-- Cron Token --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row gap-6"
                 x-data="{ show: false }">
                <div class="w-full sm:w-64 flex-shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Cron Job Token</p>
                    <p class="text-xs text-gray-400 leading-relaxed">Secure token required to run scheduled tasks.</p>
                </div>
                <div class="flex-1 max-w-lg space-y-3">
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" readonly value="{{ $cronToken ?? str_repeat('•', 32) }}"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 pr-10 font-mono focus:outline-none bg-gray-50 dark:bg-gray-700/50">
                        <button type="button" @click="show = !show" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <form method="POST" action="/admin/security/cron/generate" class="inline">@csrf
                            <button type="submit" class="px-3 py-1.5 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-700 text-xs font-semibold rounded-lg hover:bg-green-100 transition-colors">Generate Token</button>
                        </form>
                        @if($cronToken)
                        <form method="POST" action="/admin/security/cron/revoke" class="inline">@csrf
                            <button type="submit" class="px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-500 border border-red-200 dark:border-red-700 text-xs font-semibold rounded-lg hover:bg-red-100 transition-colors" onclick="return confirm('Revoke cron token?')">Revoke</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             INFRASTRUCTURE
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'infra'" x-cloak class="space-y-px">
            @foreach([
                ['/admin/cache',         'Cache System',    'cache-control',    'Clear application, view, route, and configuration caches.'],
                ['/admin/storage',       'File Storage',    'hard-drive',       'Storage driver, disk usage, and media paths.'],
                ['/admin/queue-settings','Queue Settings',  'queue-list',       'Job queue driver, failed jobs, and scheduling.'],
                ['/admin/deploy',        'Deploy',          'cloud-upload',     'Pull latest code and run post-deploy steps.'],
                ['/admin/backup',        'Database Backup', 'server',           'Download a full backup of the database.'],
            ] as [$url, $lbl, $ico, $desc])
            <a href="{{ $url }}"
               class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ $lbl }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $desc }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-brand-500 flex-shrink-0 ml-4 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endforeach
        </div>

    </div>{{-- end right --}}
</div>{{-- end flex --}}

<script>
function appearanceSettings() {
    return {
        color: '{{ $brandColor }}',
        hexInput: '{{ ltrim($brandColor, '#') }}',
        setColor(hex) { this.color = hex; this.hexInput = hex.replace('#',''); },
        syncHex() { this.hexInput = this.color.replace('#','').toUpperCase(); },
        syncColor() { const v = this.hexInput.replace(/[^0-9a-fA-F]/g,''); if (v.length === 6) this.color = '#' + v; },
        applyPreview() {}
    }
}
</script>
@endsection
