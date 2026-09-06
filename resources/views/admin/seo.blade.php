@extends('layouts.admin')
@section('title', 'SEO Tools')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Sitemap ──────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Sitemap</h3>
        <p class="text-xs text-gray-400 mb-5">Configure XML sitemap settings for search engines.</p>

        <form method="POST" action="/admin/seo/settings" class="space-y-5">
            @csrf

            {{-- Frequency --}}
            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-0.5">Frequency <span class="text-red-500">*</span></label>
                <p class="text-xs text-gray-400 mb-2">How frequently the content at a particular URL is likely to change</p>
                <div class="flex gap-5">
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <input type="radio" name="sitemap_frequency" value="auto" {{ ($s['sitemap_frequency'] ?? 'auto') === 'auto' ? 'checked' : '' }}
                            class="accent-brand-500">
                        Automatically Calculated
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <input type="radio" name="sitemap_frequency" value="none" {{ ($s['sitemap_frequency'] ?? '') === 'none' ? 'checked' : '' }}
                            class="accent-brand-500">
                        None
                    </label>
                </div>
            </div>

            {{-- Last Modification --}}
            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-0.5">Last Modification <span class="text-red-500">*</span></label>
                <p class="text-xs text-gray-400 mb-2">The time the URL was last modified</p>
                <div class="flex gap-5">
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <input type="radio" name="sitemap_lastmod" value="auto" {{ ($s['sitemap_lastmod'] ?? '') === 'auto' ? 'checked' : '' }}
                            class="accent-brand-500">
                        Automatically Calculated
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <input type="radio" name="sitemap_lastmod" value="none" {{ ($s['sitemap_lastmod'] ?? 'none') === 'none' ? 'checked' : '' }}
                            class="accent-brand-500">
                        None
                    </label>
                </div>
            </div>

            {{-- Priority --}}
            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-0.5">Priority <span class="text-red-500">*</span></label>
                <p class="text-xs text-gray-400 mb-2">The priority of a particular URL relative to other pages on the same site</p>
                <div class="flex gap-5">
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <input type="radio" name="sitemap_priority" value="auto" {{ ($s['sitemap_priority'] ?? '') === 'auto' ? 'checked' : '' }}
                            class="accent-brand-500">
                        Automatically Calculated
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <input type="radio" name="sitemap_priority" value="none" {{ ($s['sitemap_priority'] ?? 'none') === 'none' ? 'checked' : '' }}
                            class="accent-brand-500">
                        None
                    </label>
                </div>
            </div>

            <button class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                Generate Sitemap
            </button>
        </form>

        {{-- Generated Sitemaps --}}
        <div class="mt-6 border-t border-gray-100 dark:border-gray-700 pt-5">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Generated Sitemaps</h4>
            <div class="border border-gray-200 dark:border-gray-600 rounded-lg divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        <a href="/sitemap.xml" target="_blank" class="text-sm text-brand-600 hover:underline">sitemap.xml</a>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="/sitemap.xml" target="_blank"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 hover:bg-green-100">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                        <form method="POST" action="/admin/seo/robots" onsubmit="return false">
                            <button type="button" disabled
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/30 text-red-400 cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cron tip --}}
        <div class="mt-4 flex gap-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-lg p-3">
            <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 mb-0.5">Sitemap</p>
                <p class="text-xs text-blue-600 dark:text-blue-400">Submit this URL to Google Search Console and Bing Webmaster Tools to help search engines index your content automatically.</p>
                <p class="text-xs font-mono text-blue-600 dark:text-blue-400 mt-1">{{ url('/sitemap.xml') }}</p>
            </div>
        </div>
    </div>

    {{-- Google Analytics ──────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white mb-5">Google Analytics</h3>
        <form method="POST" action="/admin/seo/settings" class="space-y-5" x-data="{ ga: {{ ($s['ga_enabled'] ?? false) ? 'true' : 'false' }} }">
            @csrf
            <input type="hidden" name="sitemap_frequency" value="{{ $s['sitemap_frequency'] ?? 'auto' }}">
            <input type="hidden" name="sitemap_lastmod"   value="{{ $s['sitemap_lastmod'] ?? 'none' }}">
            <input type="hidden" name="sitemap_priority"  value="{{ $s['sitemap_priority'] ?? 'none' }}">

            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Status</label>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400" x-text="ga ? 'Enabled' : 'Enable'"></span>
                    <button type="button" @click="ga = !ga"
                        :class="ga ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 focus:outline-none">
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
                <p class="text-xs text-gray-400 mt-1.5">Find your Measurement ID in <a href="https://analytics.google.com" target="_blank" class="text-brand-600 hover:underline">Google Analytics → Admin → Data Streams</a>.</p>
            </div>

            <button class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                Save Changes
            </button>
        </form>
    </div>

    {{-- Robots.txt ────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Robots.txt</h3>
        <p class="text-xs text-gray-400 mb-4">Control how search engines crawl your site.</p>
        <form method="POST" action="/admin/seo/robots" class="space-y-3">
            @csrf
            <textarea name="robots" rows="8"
                class="w-full text-sm font-mono border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none bg-gray-50 dark:bg-gray-700"
            >{{ $robots }}</textarea>
            <div class="flex items-center gap-3">
                <button class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg">Save</button>
                <a href="/robots.txt" target="_blank" class="text-sm text-brand-600 hover:underline">View robots.txt ↗</a>
            </div>
        </form>
    </div>

    {{-- RSS Feed + Google Preview ────────────────────────────── --}}
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white mb-1">RSS Feed</h3>
            <p class="text-xs text-gray-400 mb-4">RSS feed for all published posts. Submit to Google News for wider reach.</p>
            <div class="space-y-2">
                <a href="/feed.xml" target="_blank"
                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Main RSS Feed</span>
                    <span class="text-xs text-gray-400 font-mono">{{ url('/feed.xml') }}</span>
                </a>
            </div>
            <p class="text-xs text-gray-400 mt-3">Submit to <a href="https://publishercenter.google.com" target="_blank" class="text-brand-600 hover:underline">Google News Publisher Center</a> to appear in Google News.</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Google Search Preview</h3>
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-900">
                <p class="text-xs text-green-700 dark:text-green-500 mb-0.5">{{ url('/') }}</p>
                <p class="text-blue-700 dark:text-blue-400 text-lg leading-tight hover:underline cursor-pointer mb-1">{{ config('app.name', 'nkhoj') }} — नेपाली समाचार र ब्लग</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $s['site_description'] ?? 'नेपालको अग्रणी बहु-ब्लग र समाचार प्लेटफर्म। ताजा समाचार, विचार र विश्लेषण पढ्नुहोस्।' }}</p>
            </div>
            <p class="text-xs text-gray-400 mt-3">Update description in <a href="/admin/settings" class="text-brand-600 hover:underline">Settings → Site Description</a>.</p>
        </div>
    </div>

</div>
@endsection
