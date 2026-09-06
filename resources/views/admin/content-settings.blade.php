@extends('layouts.admin')
@section('title', 'Content Settings')

@section('content')
@php
$tab = request('tab', 'general');
$s   = $settings;
@endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT: tabbed settings ──────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- Tabs --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="flex border-b border-gray-100 dark:border-gray-700 overflow-x-auto">
                @foreach(['general' => 'General', 'posts' => 'Posts', 'post_formats' => 'Post Formats', 'file_upload' => 'File Upload'] as $key => $label)
                <a href="?tab={{ $key }}"
                    class="whitespace-nowrap px-5 py-3.5 text-sm font-medium border-b-2 transition-colors
                    {{ $tab === $key ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>

            <form method="POST" action="/admin/content-settings?tab={{ $tab }}" class="p-6 space-y-5">
                @csrf

                {{-- ── GENERAL TAB ──────────────────────────────── --}}
                @if($tab === 'general')
                @foreach([
                    ['show_featured_section',  'Show Featured Section',       'Yes'],
                    ['comment_system',         'Comment System',               'Enabled'],
                    ['comment_approval',       'Comment Approval System',      'Enabled'],
                    ['emoji_reactions',        'Emoji Reactions',              'Enabled'],
                    ['show_latest_posts',      'Show Latest Posts on Homepage','Yes'],
                ] as [$key, $label, $onLabel])
                <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700 last:border-0"
                     x-data="{ on: {{ ($s[$key] ?? true) ? 'true' : 'false' }} }">
                    <a class="text-sm text-brand-600 hover:underline cursor-pointer" @click="">{{ $label }}</a>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-green-600" x-show="on">{{ $onLabel }}</span>
                        <button type="button" @click="on = !on"
                            :class="on ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200">
                            <span :class="on ? 'translate-x-5' : 'translate-x-0.5'"
                                class="inline-block h-5 w-5 translate-y-0.5 transform rounded-full bg-white shadow transition duration-200"></span>
                        </button>
                        <input type="hidden" name="{{ $key }}" :value="on ? '1' : '0'">
                    </div>
                </div>
                @endforeach

                <div class="flex items-center justify-between py-2">
                    <label class="text-sm text-brand-600 cursor-pointer">Pagination (Number of posts per page)</label>
                    <input type="number" name="posts_per_page" value="{{ $s['posts_per_page'] ?? 16 }}" min="4" max="100"
                        class="w-24 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500 text-right">
                </div>

                {{-- ── POSTS TAB ─────────────────────────────────── --}}
                @elseif($tab === 'posts')
                <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700">
                    <label class="text-sm text-brand-600">Post URL Structure</label>
                    <p class="text-xs text-red-400 mr-4">Changing the URL structure will not affect old records.</p>
                    <select name="post_url_structure"
                        class="text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                        <option value="slug" {{ ($s['post_url_structure'] ?? 'slug') === 'slug' ? 'selected' : '' }}>Use Slug in URLs (domain.com/slug)</option>
                        <option value="id"   {{ ($s['post_url_structure'] ?? '') === 'id'   ? 'selected' : '' }}>Use ID in URLs (domain.com/123)</option>
                    </select>
                </div>

                @foreach([
                    ['bulk_upload_authors',       'Bulk Post Upload for Authors',             'Enabled'],
                    ['delete_images_with_post',   'Delete Images Along with Post',            'Enabled'],
                    ['audio_download',            'Audio Download Button',                    'Enabled'],
                    ['show_post_author',          'Show Post Author',                         'Yes'],
                    ['show_post_date',            'Show Post Date',                           'Yes'],
                    ['show_post_view_count',      'Show Post View Count',                     'Yes'],
                    ['require_approval_new',      'Require Admin Approval for New Posts',     'Yes'],
                    ['require_approval_edited',   'Require Admin Approval for Edited Posts',  'Yes'],
                    ['restrict_rss',              'Redirect RSS Posts to the Original Site',  'Yes'],
                ] as [$key, $label, $onLabel])
                <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700 last:border-0"
                     x-data="{ on: {{ ($s[$key] ?? false) ? 'true' : 'false' }} }">
                    <span class="text-sm text-brand-600">{{ $label }}</span>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-green-600" x-show="on">{{ $onLabel }}</span>
                        <button type="button" @click="on = !on"
                            :class="on ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200">
                            <span :class="on ? 'translate-x-5' : 'translate-x-0.5'"
                                class="inline-block h-5 w-5 translate-y-0.5 transform rounded-full bg-white shadow transition duration-200"></span>
                        </button>
                        <input type="hidden" name="{{ $key }}" :value="on ? '1' : '0'">
                    </div>
                </div>
                @endforeach

                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="flex items-center justify-between">
                        <label class="text-sm text-brand-600">Popular Posts Limit</label>
                        <input type="number" name="popular_posts_limit" value="{{ $s['popular_posts_limit'] ?? 5 }}" min="1" max="50"
                            class="w-20 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500 text-right">
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="text-sm text-brand-600">Related Posts Limit</label>
                        <input type="number" name="related_posts_limit" value="{{ $s['related_posts_limit'] ?? 6 }}" min="1" max="50"
                            class="w-20 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500 text-right">
                    </div>
                </div>

                {{-- ── POST FORMATS TAB ──────────────────────────── --}}
                @elseif($tab === 'post_formats')
                @foreach([
                    'article'           => 'Article',
                    'gallery'           => 'Gallery',
                    'sorted_list'       => 'Sorted List',
                    'table_of_contents' => 'Table of Contents',
                    'video'             => 'Video',
                    'audio'             => 'Audio',
                    'trivia_quiz'       => 'Trivia Quiz',
                    'personality_quiz'  => 'Personality Quiz',
                    'poll'              => 'Poll',
                    'recipe'            => 'Recipe',
                    'event'             => 'Event',
                ] as $fkey => $flabel)
                <div class="flex items-center justify-between py-2.5 border-b border-gray-50 dark:border-gray-700 last:border-0"
                     x-data="{ on: {{ ($s['formats_enabled'][$fkey] ?? true) ? 'true' : 'false' }} }">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $flabel }}</span>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-green-600" x-show="on">Enabled</span>
                        <button type="button" @click="on = !on"
                            :class="on ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200">
                            <span :class="on ? 'translate-x-5' : 'translate-x-0.5'"
                                class="inline-block h-5 w-5 translate-y-0.5 transform rounded-full bg-white shadow transition duration-200"></span>
                        </button>
                        <input type="hidden" name="formats_enabled[{{ $fkey }}]" :value="on ? '1' : '0'">
                    </div>
                </div>
                @endforeach

                {{-- ── FILE UPLOAD TAB ───────────────────────────── --}}
                @elseif($tab === 'file_upload')
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Image File Format <span class="text-red-500">*</span></label>
                    <select name="image_format"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                        @foreach(['webp' => 'WebP', 'original' => 'Keep Original', 'jpg' => 'JPG', 'png' => 'PNG'] as $v => $l)
                        <option value="{{ $v }}" {{ ($s['image_format'] ?? 'webp') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Allowed File Extensions</label>
                    <p class="text-xs text-gray-400 mb-2">E.g. file, jpg, doc, pdf</p>
                    <div class="flex flex-wrap gap-2 p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 min-h-12" x-data="{
                        exts: {{ json_encode(array_filter(explode(',', $s['allowed_extensions'] ?? 'jpg,jpeg,png,gif,pdf,doc,ppt,docx'))) }},
                        newExt: '',
                        add() { if (this.newExt.trim()) { this.exts.push(this.newExt.trim()); this.newExt = ''; } },
                        remove(i) { this.exts.splice(i, 1); }
                    }">
                        <template x-for="(ext, i) in exts" :key="i">
                            <span class="flex items-center gap-1 bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 text-xs font-medium px-2 py-1 rounded-full">
                                <span x-text="ext"></span>
                                <button type="button" @click="remove(i)" class="text-brand-400 hover:text-brand-700 ml-0.5">×</button>
                                <input type="hidden" :name="'extensions[]'" :value="ext">
                            </span>
                        </template>
                        <input type="text" x-model="newExt" @keydown.enter.prevent="add()" @keydown.comma.prevent="add()"
                            placeholder="add + enter" class="text-xs border-0 bg-transparent focus:outline-none text-gray-500 w-20">
                    </div>
                </div>
                @foreach([
                    ['max_image_size', 'Maximum Image File Size',  20],
                    ['max_video_size', 'Maximum Video File Size',  50],
                    ['max_audio_size', 'Maximum Audio File Size',  20],
                    ['max_file_size',  'Maximum File Size',        30],
                ] as [$k, $label, $default])
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $label }} <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 font-medium">MB</span>
                        <input type="number" name="{{ $k }}" value="{{ $s[$k] ?? $default }}" min="1" max="2000"
                            class="w-24 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 text-right">
                    </div>
                </div>
                @endforeach
                @endif

                <div class="pt-3">
                    <button class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Featured Content Settings --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-5 pt-4 pb-2">
                <h3 class="font-bold text-gray-900 dark:text-white">Featured Content Settings</h3>
            </div>
            <div x-data="{ featured: 'slider' }" class="px-5 pb-5">
                <div class="flex border-b border-gray-100 dark:border-gray-700 mb-5 -mx-5 px-5 gap-1">
                    @foreach(['slider' => ['Main Slider', '📊'], 'featured_posts' => ['Featured Posts', '⭐'], 'recommended' => ['Recommended Posts', '🏠'], 'breaking' => ['Breaking News', '⚡']] as $k => [$l, $ico])
                    <button @click="featured = '{{ $k }}'"
                        :class="featured === '{{ $k }}' ? 'bg-brand-500 text-white border-brand-500' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors mb-2">
                        {{ $ico }} {{ $l }}
                    </button>
                    @endforeach
                </div>
                <form method="POST" action="/admin/content-settings?tab=featured" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm text-gray-700 dark:text-gray-300 block mb-1.5">Content Source</label>
                        <div class="relative">
                            <select name="featured_source"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 pr-8">
                                <option value="manual" {{ ($s['featured_source'] ?? 'manual') === 'manual' ? 'selected' : '' }}>Manual Selection Only</option>
                                <option value="auto"   {{ ($s['featured_source'] ?? '') === 'auto'   ? 'selected' : '' }}>Automatic (Most Popular)</option>
                                <option value="recent" {{ ($s['featured_source'] ?? '') === 'recent' ? 'selected' : '' }}>Most Recent</option>
                            </select>
                            <svg class="absolute right-3 top-3 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-700 dark:text-gray-300 block mb-1.5">Sorting Logic</label>
                        <div class="relative">
                            <select name="featured_sort"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 pr-8">
                                <option value="order"  {{ ($s['featured_sort'] ?? 'order')  === 'order'  ? 'selected' : '' }}>Slider Order</option>
                                <option value="recent" {{ ($s['featured_sort'] ?? '') === 'recent' ? 'selected' : '' }}>Most Recent</option>
                                <option value="views"  {{ ($s['featured_sort'] ?? '') === 'views'  ? 'selected' : '' }}>Most Viewed</option>
                            </select>
                            <svg class="absolute right-3 top-3 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-700 dark:text-gray-300 block mb-1.5">Display Duration (Number of Days)</label>
                            <input type="number" name="featured_duration" value="{{ $s['featured_duration'] ?? 10 }}" min="1"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-sm text-gray-700 dark:text-gray-300 block mb-1.5">Display Limit</label>
                            <input type="number" name="featured_limit" value="{{ $s['featured_limit'] ?? 15 }}" min="1"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>
                    <button class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: AI Generator + Auto Delete ─────────────────── --}}
    <div class="space-y-6">

        {{-- AI Content Generator --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white mb-5">AI Content Generator</h3>
            <form method="POST" action="/admin/content-settings/ai" class="space-y-4">
                @csrf
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Status <span class="text-red-500">*</span></label>
                    <div x-data="{ on: {{ ($s['ai_enabled'] ?? false) ? 'true' : 'false' }} }" class="flex items-center gap-2">
                        <span class="text-sm font-medium text-green-600" x-show="on">Enable</span>
                        <button type="button" @click="on = !on"
                            :class="on ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200">
                            <span :class="on ? 'translate-x-5' : 'translate-x-0.5'"
                                class="inline-block h-5 w-5 translate-y-0.5 transform rounded-full bg-white shadow transition duration-200"></span>
                        </button>
                        <input type="hidden" name="ai_enabled" :value="on ? '1' : '0'">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Active Provider <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="ai_provider"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 pr-8">
                            <option value="gemini"  {{ ($s['ai_provider'] ?? 'gemini') === 'gemini'  ? 'selected' : '' }}>Gemini (Google)</option>
                            <option value="chatgpt" {{ ($s['ai_provider'] ?? '') === 'chatgpt' ? 'selected' : '' }}>ChatGPT (OpenAI)</option>
                        </select>
                        <svg class="absolute right-3 top-3 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Provider tabs --}}
                <div x-data="{ prov: '{{ $s['ai_provider'] ?? 'gemini' }}' }" class="space-y-4">
                    <div class="flex rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden">
                        <button type="button" @click="prov = 'chatgpt'"
                            :class="prov === 'chatgpt' ? 'bg-white dark:bg-gray-700 font-semibold text-gray-900 dark:text-white' : 'bg-gray-50 dark:bg-gray-800 text-gray-500'"
                            class="flex-1 py-2 text-sm transition-colors text-center">ChatGPT (OpenAI)</button>
                        <button type="button" @click="prov = 'gemini'"
                            :class="prov === 'gemini' ? 'bg-white dark:bg-gray-700 font-semibold text-gray-900 dark:text-white' : 'bg-gray-50 dark:bg-gray-800 text-gray-500'"
                            class="flex-1 py-2 text-sm transition-colors text-center border-l border-gray-200 dark:border-gray-600">Gemini (Google)</button>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">API Key <span class="text-red-500">*</span></label>
                        <input type="password" name="ai_api_key" value="{{ $s['ai_api_key'] ?? '' }}" placeholder="••••••••"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Model</label>
                        <div class="relative" x-show="prov === 'gemini'">
                            <select name="ai_model_gemini"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 pr-8">
                                <option value="gemini-2.5-flash-lite-legacy" {{ ($s['ai_model'] ?? 'gemini-2.5-flash-lite-legacy') === 'gemini-2.5-flash-lite-legacy' ? 'selected' : '' }}>Gemini 2.5 Flash-Lite (Legacy Budget)</option>
                                <option value="gemini-2.5-flash" {{ ($s['ai_model'] ?? '') === 'gemini-2.5-flash' ? 'selected' : '' }}>Gemini 2.5 Flash</option>
                                <option value="gemini-2.5-pro"   {{ ($s['ai_model'] ?? '') === 'gemini-2.5-pro'   ? 'selected' : '' }}>Gemini 2.5 Pro</option>
                            </select>
                            <svg class="absolute right-3 top-3 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div class="relative" x-show="prov === 'chatgpt'">
                            <select name="ai_model_chatgpt"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 pr-8">
                                <option value="gpt-4o-mini" {{ ($s['ai_model_chatgpt'] ?? 'gpt-4o-mini') === 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini</option>
                                <option value="gpt-4o"      {{ ($s['ai_model_chatgpt'] ?? '') === 'gpt-4o'      ? 'selected' : '' }}>GPT-4o</option>
                                <option value="gpt-4-turbo" {{ ($s['ai_model_chatgpt'] ?? '') === 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo</option>
                            </select>
                            <svg class="absolute right-3 top-3 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <button class="w-full py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg">Save Changes</button>
            </form>
        </div>

        {{-- Auto Post Deletion --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white mb-5">Auto Post Deletion</h3>
            <form method="POST" action="/admin/content-settings/auto-delete" class="space-y-4">
                @csrf
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Status</label>
                    <div x-data="{ on: {{ ($s['auto_delete_enabled'] ?? false) ? 'true' : 'false' }} }" class="flex items-center gap-2">
                        <span class="text-sm text-gray-400" x-text="on ? 'Enabled' : 'Enable'"></span>
                        <button type="button" @click="on = !on"
                            :class="on ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200">
                            <span :class="on ? 'translate-x-5' : 'translate-x-0.5'"
                                class="inline-block h-5 w-5 translate-y-0.5 transform rounded-full bg-white shadow transition duration-200"></span>
                        </button>
                        <input type="hidden" name="auto_delete_enabled" :value="on ? '1' : '0'">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Number of Days <span class="text-red-500">*</span></label>
                    <input type="number" name="auto_delete_days" value="{{ $s['auto_delete_days'] ?? 30 }}" min="1"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-xs text-gray-400 mt-1">E.g. if you add 30 here, the system will delete posts older than 30 days</p>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Posts <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="auto_delete_scope"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 pr-8">
                            <option value="all"      {{ ($s['auto_delete_scope'] ?? 'all')      === 'all'      ? 'selected' : '' }}>Delete All Posts</option>
                            <option value="drafts"   {{ ($s['auto_delete_scope'] ?? '') === 'drafts'   ? 'selected' : '' }}>Drafts Only</option>
                            <option value="archived" {{ ($s['auto_delete_scope'] ?? '') === 'archived' ? 'selected' : '' }}>Archived Only</option>
                        </select>
                        <svg class="absolute right-3 top-3 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <button class="w-full py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg">Save Changes</button>
            </form>
        </div>
    </div>
</div>
@endsection
