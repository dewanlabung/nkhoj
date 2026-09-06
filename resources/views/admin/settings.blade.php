@extends('layouts.admin')
@section('title', 'General Settings')

@section('content')
@if(session('success'))
<div class="mb-5 text-sm text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl px-4 py-3 flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 text-sm text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3">{{ session('error') }}</div>
@endif

<div class="mb-6">
    <nav class="text-xs text-gray-400 flex items-center gap-1.5 mb-1">
        <a href="/admin" class="hover:text-brand-500">Home</a><span>›</span><span>General Settings</span>
    </nav>
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">General</h1>
</div>

<div class="space-y-px">

    {{-- Site URL --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row items-start gap-4 sm:gap-8">
        <div class="w-full sm:w-80 flex-shrink-0">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Site URL</p>
            <p class="text-xs text-gray-400 leading-relaxed">The primary domain for your site.</p>
            <button type="button" class="mt-2 text-xs text-brand-500 hover:underline">What is a primary site url?</button>
        </div>
        <form method="POST" action="/admin/settings/url" class="flex-1 max-w-lg">
            @csrf
            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Primary site url</label>
            <input type="url" name="site_url" value="{{ $s['site_url'] ?? url('/') }}"
                class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all">
            <button class="mt-3 px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg transition-colors">Save</button>
        </form>
    </div>

    {{-- Site Name --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row items-start gap-4 sm:gap-8">
        <div class="w-full sm:w-80 flex-shrink-0">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Site name</p>
            <p class="text-xs text-gray-400 leading-relaxed">Short name for the site that will appear in browser tabs, SEO tags, PWA app and other places.</p>
        </div>
        <form method="POST" action="/admin/settings/name" class="flex-1 max-w-lg">
            @csrf
            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Site name</label>
            <input type="text" name="site_name" value="{{ $s['site_name'] ?? 'nkhoj' }}"
                class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all">
            <button class="mt-3 px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg transition-colors">Save</button>
        </form>
    </div>

    {{-- Tagline --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row items-start gap-4 sm:gap-8">
        <div class="w-full sm:w-80 flex-shrink-0">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Tagline</p>
            <p class="text-xs text-gray-400 leading-relaxed">Short tagline shown in meta descriptions and the site header.</p>
        </div>
        <form method="POST" action="/admin/settings/tagline" class="flex-1 max-w-lg space-y-3">
            @csrf
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Tagline (English)</label>
                <input type="text" name="tagline_en" value="{{ $s['tagline_en'] ?? "Nepal's leading multi-blog & news platform" }}"
                    class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Tagline (Nepali)</label>
                <input type="text" name="tagline_ne" value="{{ $s['tagline_ne'] ?? 'नेपालको अग्रणी बहु-ब्लग र समाचार प्लेटफर्म' }}"
                    class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Site Description (SEO)</label>
                <textarea name="site_description" rows="2"
                    class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all resize-none">{{ $s['site_description'] ?? '' }}</textarea>
            </div>
            <button class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg transition-colors">Save</button>
        </form>
    </div>

    {{-- Favicon --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row items-start gap-4 sm:gap-8">
        <div class="w-full sm:w-80 flex-shrink-0">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Favicon</p>
            <p class="text-xs text-gray-400 leading-relaxed">This will generate different size favicons. Image should be at least 512×512 in size.</p>
        </div>
        <div class="flex-1 max-w-lg">
            <div x-data="{ preview: '{{ $s['favicon_url'] ?? '' }}' }">
                <form method="POST" action="/admin/settings/favicon" enctype="multipart/form-data">
                    @csrf
                    <div class="flex items-end gap-4">
                        <div class="w-16 h-16 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 overflow-hidden flex items-center justify-center bg-gray-50 dark:bg-gray-700 flex-shrink-0">
                            <template x-if="preview">
                                <img :src="preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!preview">
                                <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </template>
                        </div>
                        <div>
                            <label class="cursor-pointer px-4 py-2 text-xs font-bold border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Replace
                                <input type="file" name="favicon" accept="image/*" class="hidden"
                                    @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                            </label>
                            @if(!empty($s['favicon_url']))
                            <a href="/admin/settings/favicon/remove" onclick="return confirm('Remove favicon?')"
                                class="ml-2 text-xs text-red-500 hover:underline">Remove</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Dark Logo --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row items-start gap-4 sm:gap-8">
        <div class="w-full sm:w-80 flex-shrink-0">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Dark logo</p>
            <p class="text-xs text-gray-400 leading-relaxed">Used when global color scheme or specific element scheme is light. Default logo is 516×117px size.</p>
        </div>
        <div class="flex-1 max-w-lg">
            <div x-data="{ preview: '{{ $s['logo_dark_url'] ?? '' }}' }">
                <form method="POST" action="/admin/settings/logo-dark" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-3">
                        <div class="h-20 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 overflow-hidden flex items-center justify-center bg-gray-800 px-4">
                            <template x-if="preview">
                                <img :src="preview" class="max-h-12 max-w-full object-contain">
                            </template>
                            <template x-if="!preview">
                                <span class="text-xs text-gray-500 font-bold tracking-widest">NKHOJ</span>
                            </template>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="cursor-pointer px-4 py-2 text-xs font-bold border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Replace
                                <input type="file" name="logo_dark" accept="image/*" class="hidden"
                                    @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                            </label>
                            @if(!empty($s['logo_dark_url']))
                            <a href="/admin/settings/logo-dark/remove" onclick="return confirm('Remove logo?')"
                                class="text-xs text-red-500 hover:underline">Remove</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Light Logo --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row items-start gap-4 sm:gap-8">
        <div class="w-full sm:w-80 flex-shrink-0">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Light logo</p>
            <p class="text-xs text-gray-400 leading-relaxed">Used when global color scheme or specific element scheme is dark. If empty, light mode logo will be used.</p>
        </div>
        <div class="flex-1 max-w-lg">
            <div x-data="{ preview: '{{ $s['logo_light_url'] ?? '' }}' }">
                <form method="POST" action="/admin/settings/logo-light" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-3">
                        <div class="h-20 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 overflow-hidden flex items-center justify-center bg-white px-4">
                            <template x-if="preview">
                                <img :src="preview" class="max-h-12 max-w-full object-contain">
                            </template>
                            <template x-if="!preview">
                                <span class="text-xs text-gray-800 font-bold tracking-widest">NKHOJ</span>
                            </template>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="cursor-pointer px-4 py-2 text-xs font-bold border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Replace
                                <input type="file" name="logo_light" accept="image/*" class="hidden"
                                    @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                            </label>
                            @if(!empty($s['logo_light_url']))
                            <a href="/admin/settings/logo-light/remove" onclick="return confirm('Remove logo?')"
                                class="text-xs text-red-500 hover:underline">Remove</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Compact Logos --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row items-start gap-4 sm:gap-8">
        <div class="w-full sm:w-80 flex-shrink-0">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Compact logos</p>
            <p class="text-xs text-gray-400 leading-relaxed">Will show these logos if there's not enough space for regular logos. For example on mobile or when screen is too small.</p>
        </div>
        <div class="flex-1 max-w-lg">
            <div class="grid grid-cols-2 gap-4">
                {{-- Compact Dark --}}
                <div x-data="{ preview: '{{ $s['logo_compact_dark_url'] ?? '' }}' }">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Dark logo</p>
                    <form method="POST" action="/admin/settings/logo-compact-dark" enctype="multipart/form-data">
                        @csrf
                        <div class="h-14 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 overflow-hidden flex items-center justify-center bg-gray-800 mb-2">
                            <template x-if="preview">
                                <img :src="preview" class="max-h-10 max-w-full object-contain">
                            </template>
                            <template x-if="!preview">
                                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </template>
                        </div>
                        <label class="cursor-pointer flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors w-full">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            Choose File
                            <input type="file" name="logo_compact_dark" accept="image/*" class="hidden"
                                @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                        </label>
                    </form>
                </div>
                {{-- Compact Light --}}
                <div x-data="{ preview: '{{ $s['logo_compact_light_url'] ?? '' }}' }">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Light logo</p>
                    <form method="POST" action="/admin/settings/logo-compact-light" enctype="multipart/form-data">
                        @csrf
                        <div class="h-14 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 overflow-hidden flex items-center justify-center bg-white mb-2">
                            <template x-if="preview">
                                <img :src="preview" class="max-h-10 max-w-full object-contain">
                            </template>
                            <template x-if="!preview">
                                <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </template>
                        </div>
                        <label class="cursor-pointer flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors w-full">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            Choose File
                            <input type="file" name="logo_compact_light" accept="image/*" class="hidden"
                                @change="preview = URL.createObjectURL($event.target.files[0]); $el.closest('form').submit()">
                        </label>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Contact & Support --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row items-start gap-4 sm:gap-8">
        <div class="w-full sm:w-80 flex-shrink-0">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Contact &amp; Support</p>
            <p class="text-xs text-gray-400 leading-relaxed">Contact email shown publicly and used for system notifications.</p>
        </div>
        <form method="POST" action="/admin/settings/contact" class="flex-1 max-w-lg">
            @csrf
            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Contact Email</label>
            <input type="email" name="contact_email" value="{{ $s['contact_email'] ?? '' }}" placeholder="hello@nkhoj.com"
                class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all">
            <button class="mt-3 px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg transition-colors">Save</button>
        </form>
    </div>

    {{-- Social Links --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row items-start gap-4 sm:gap-8">
        <div class="w-full sm:w-80 flex-shrink-0">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Social Links</p>
            <p class="text-xs text-gray-400 leading-relaxed">Links to your social profiles, shown in the site footer and about page.</p>
        </div>
        <form method="POST" action="/admin/settings/social" class="flex-1 max-w-lg space-y-3">
            @csrf
            @php
            $socialPlatforms = [
                'facebook'  => ['label'=>'Facebook', 'ph'=>'https://facebook.com/nkhoj'],
                'twitter'   => ['label'=>'X (Twitter)', 'ph'=>'https://x.com/nkhoj'],
                'instagram' => ['label'=>'Instagram', 'ph'=>'https://instagram.com/nkhoj'],
                'youtube'   => ['label'=>'YouTube', 'ph'=>'https://youtube.com/@nkhoj'],
                'tiktok'    => ['label'=>'TikTok', 'ph'=>'https://tiktok.com/@nkhoj'],
            ];
            @endphp
            @foreach($socialPlatforms as $key => $info)
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">{{ $info['label'] }}</label>
                <input type="url" name="social_{{ $key }}" value="{{ $s['social_'.$key] ?? '' }}"
                    placeholder="{{ $info['ph'] }}"
                    class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all">
            </div>
            @endforeach
            <button class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg transition-colors">Save</button>
        </form>
    </div>

    {{-- Analytics & Integrations --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row items-start gap-4 sm:gap-8">
        <div class="w-full sm:w-80 flex-shrink-0">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Analytics &amp; Integrations</p>
            <p class="text-xs text-gray-400 leading-relaxed">Connect analytics and ad networks to your site.</p>
        </div>
        <form method="POST" action="/admin/settings/analytics" class="flex-1 max-w-lg space-y-3">
            @csrf
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Google Analytics ID <span class="font-normal">(G-XXXXXXXXXX)</span></label>
                <input type="text" name="ga_id" value="{{ $s['ga_id'] ?? '' }}" placeholder="G-XXXXXXXXXX"
                    class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 block">Google AdSense ID <span class="font-normal">(ca-pub-XXXXXXXX)</span></label>
                <input type="text" name="adsense_id" value="{{ $s['adsense_id'] ?? '' }}" placeholder="ca-pub-XXXXXXXX"
                    class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all">
            </div>
            <button class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg transition-colors">Save</button>
        </form>
    </div>

    {{-- Content Behaviour --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 first:rounded-t-xl last:rounded-b-xl px-6 py-5 flex flex-col sm:flex-row items-start gap-4 sm:gap-8">
        <div class="w-full sm:w-80 flex-shrink-0">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Content Behaviour</p>
            <p class="text-xs text-gray-400 leading-relaxed">Toggle site-wide features on or off.</p>
        </div>
        <form method="POST" action="/admin/settings/behaviour" class="flex-1 max-w-lg space-y-4">
            @csrf
            @php
            $toggles = [
                'allow_guest_comments'  => ['label'=>'Guest Comments', 'desc'=>'Let non-registered users post comments'],
                'require_post_approval' => ['label'=>'Post Approval', 'desc'=>'New posts require admin approval before going live'],
                'show_breaking_news'    => ['label'=>'Breaking News Ticker', 'desc'=>'Show the breaking news ticker on homepage'],
            ];
            @endphp
            @foreach($toggles as $key => $info)
            <div class="flex items-center justify-between" x-data="{ on: {{ ($s[$key] ?? false) ? 'true' : 'false' }} }">
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $info['label'] }}</p>
                    <p class="text-xs text-gray-400">{{ $info['desc'] }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="{{ $key }}" value="1" class="sr-only peer"
                        x-model="on" @change="$el.closest('form').submit()">
                    <div class="w-9 h-5 rounded-full transition-colors peer-checked:bg-brand-500 bg-gray-200 dark:bg-gray-600 peer-focus:ring-2 peer-focus:ring-brand-400/40 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4"></div>
                </label>
            </div>
            @endforeach
        </form>
    </div>

</div>
@endsection
