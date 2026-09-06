@extends('layouts.admin')
@section('title', 'Google News')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Google News</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
        <a href="/admin" class="hover:text-brand-500">Home</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">›</span>
        Google News
    </p>
</div>

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm rounded-xl border border-green-100 dark:border-green-800/40">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Settings panel --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-5">Settings</h3>
            <form method="POST" action="/admin/google-news" class="space-y-4">
                @csrf

                {{-- Status --}}
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="google_news_enabled" value="0">
                        <input type="checkbox" name="google_news_enabled" value="1" class="sr-only peer"
                            {{ ($settings['google_news_enabled'] ?? false) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                    </label>
                </div>

                {{-- Publication Name --}}
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">
                        Google News Publication Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="google_news_publication_name"
                        value="{{ $settings['google_news_publication_name'] ?? 'nkhoj' }}"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                {{-- RSS Content --}}
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">RSS Content <span class="text-red-400">*</span></label>
                    <p class="text-xs text-gray-400 mb-1.5">Determines how your news appears inside the Google News App.</p>
                    <select name="google_news_rss_content"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="full"    {{ ($settings['google_news_rss_content'] ?? 'full') === 'full'   ? 'selected' : '' }}>Show Full Content</option>
                        <option value="excerpt" {{ ($settings['google_news_rss_content'] ?? 'full') === 'excerpt' ? 'selected' : '' }}>Show Excerpt Only</option>
                    </select>
                </div>

                {{-- Feed Post Limit --}}
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Feed Post Limit <span class="text-red-400">*</span></label>
                    <p class="text-xs text-gray-400 mb-1.5">The number of posts to be shown in the RSS feed. (Default: 50, Max: 100)</p>
                    <input type="number" name="google_news_feed_limit" min="1" max="100"
                        value="{{ $settings['google_news_feed_limit'] ?? 50 }}"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <button class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    {{-- Right column --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Feed Link Generator --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-5">Feed Link Generator</h3>
            <div class="space-y-4" x-data="{
                lang: 'en', catSlug: '',
                get feedUrl() {
                    let u = '{{ $appUrl }}/feed.xml?lang='+this.lang;
                    if(this.catSlug) u += '&category='+this.catSlug;
                    return u;
                },
                copy() { navigator.clipboard.writeText(this.feedUrl); }
            }">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Language</label>
                        <select x-model="lang"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="en">English</option>
                            <option value="ne">Nepali</option>
                            <option value="ar">Arabic</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Category</label>
                        <select x-model="catSlug"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="">Select a category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}">{{ $cat->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Generated Feed URL</label>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 flex items-center gap-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            <span x-text="feedUrl" class="text-sm text-gray-600 dark:text-gray-300 truncate"></span>
                        </div>
                        <button @click="copy()"
                            class="flex-shrink-0 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Integration Endpoints --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-1.5">Integration Endpoints</h3>
            <p class="text-sm text-gray-400 dark:text-gray-500 mb-5">The following links are generated for you to submit to Google services.</p>

            {{-- News Sitemap --}}
            <div class="border border-dashed border-brand-300 dark:border-brand-700/50 bg-brand-50/30 dark:bg-brand-900/10 rounded-xl p-4 mb-4">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-9 h-9 rounded-lg bg-brand-100 dark:bg-brand-900/40 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">News Sitemap (for SEO &amp; Bots)</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">This file includes only articles from the last 48 hours. Copy this URL and submit it once via Google Search Console › Sitemaps.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input readonly value="{{ url('/sitemap.xml') }}"
                        class="flex-1 text-xs bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-gray-600 dark:text-gray-300 font-mono">
                    <a href="/sitemap.xml" target="_blank"
                        class="flex-shrink-0 px-3 py-2 text-xs font-medium border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        View
                    </a>
                </div>
            </div>

            {{-- Publisher Center Feed --}}
            <div class="border border-dashed border-yellow-300 dark:border-yellow-700/50 bg-yellow-50/30 dark:bg-yellow-900/10 rounded-xl p-4">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-9 h-9 rounded-lg bg-yellow-100 dark:bg-yellow-900/40 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">Publisher Center Feed (for App)</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">This is your primary feed containing all latest news. Use this URL in Google Publisher Center. To create separate sections (e.g., specific Categories or Languages), please use the Feed Link Generator tool above.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input readonly value="{{ url('/feed.xml') }}"
                        class="flex-1 text-xs bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-gray-600 dark:text-gray-300 font-mono">
                    <a href="/feed.xml" target="_blank"
                        class="flex-shrink-0 px-3 py-2 text-xs font-medium border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        View
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
