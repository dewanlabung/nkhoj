@extends('layouts.app')
@section('title', 'Add Article')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8"
    x-data="articleForm()"
    x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <nav class="flex items-center gap-2 text-xs text-gray-400 mb-1">
                <a href="/dashboard" class="hover:text-brand-500 transition-colors">Dashboard</a>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Add Article</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add Article</h1>
        </div>
        <a href="/dashboard/posts" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            My Posts
        </a>
    </div>

    <form method="POST" action="/dashboard/posts" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="post_format" value="article">
        <input type="hidden" name="tags" :value="JSON.stringify(tags)">

        <div class="space-y-5">

            {{-- ── IMAGE ─────────────────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Featured Image
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Thumbnail URL (external)</label>
                        <input type="url" name="thumbnail_url"
                            value="{{ old('thumbnail_url') }}"
                            placeholder="https://example.com/image.jpg"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Image Caption / Alt text</label>
                        <input type="text" name="image_caption"
                            value="{{ old('image_caption') }}"
                            placeholder="Describe the image…"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="mt-3" x-data="{ preview: null }">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Or Upload File</label>
                    <div class="flex items-center gap-3">
                        <label class="cursor-pointer flex items-center gap-2 border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-lg px-4 py-3 text-sm text-gray-500 dark:text-gray-400 hover:border-brand-400 hover:text-brand-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            Upload Image
                            <input type="file" name="thumbnail_file" class="hidden" accept="image/*"
                                @change="preview = URL.createObjectURL($event.target.files[0])">
                        </label>
                        <img x-show="preview" :src="preview" class="h-12 w-20 object-cover rounded-lg border border-gray-200">
                    </div>
                </div>
            </div>

            {{-- ── SETTINGS ──────────────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Category <span class="text-red-400">*</span></label>
                        <select name="category_id" required class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="">— Select —</option>
                            @foreach(\App\Models\Category::whereNull('parent_id')->orderBy('sort_order')->get() as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name_en }}</option>
                            @foreach($cat->children as $sub)
                            <option value="{{ $sub->id }}" {{ old('category_id') == $sub->id ? 'selected' : '' }}>&nbsp;&nbsp;↳ {{ $sub->name_en }}</option>
                            @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Visibility</label>
                        <select name="visibility" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="public" {{ old('visibility', 'public') === 'public' ? 'selected' : '' }}>Public</option>
                            <option value="members" {{ old('visibility') === 'members' ? 'selected' : '' }}>Members Only</option>
                            <option value="private" {{ old('visibility') === 'private' ? 'selected' : '' }}>Private</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Scheduled At</label>
                        <input type="datetime-local" name="scheduled_at"
                            value="{{ old('scheduled_at') }}"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                {{-- Toggle grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach([
                        ['is_featured','Featured','star'],
                        ['is_breaking','Breaking News','bolt'],
                        ['is_slider','Slider','view-list'],
                        ['is_recommended','Recommended','thumb-up'],
                    ] as [$name, $label, $icon])
                    <label class="flex items-center gap-2 cursor-pointer bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2.5 border border-gray-100 dark:border-gray-600 hover:border-brand-300 transition-colors" x-data="{ on: false }">
                        <div class="relative flex-shrink-0" @click="on = !on">
                            <input type="hidden" name="{{ $name }}" value="0">
                            <input type="checkbox" name="{{ $name }}" value="1" class="sr-only" :checked="on">
                            <div class="w-9 h-5 rounded-full transition-colors" :class="on ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-4' : ''"></div>
                        </div>
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- ── GENERAL ───────────────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    General
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Title <span class="text-red-400">*</span></label>
                        <input type="text" name="title" required
                            value="{{ old('title') }}"
                            placeholder="Enter article title…"
                            @input="autoSlug"
                            x-model="titleVal"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Slug</label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="slug" x-model="slugVal"
                                placeholder="auto-generated-from-title"
                                class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                            <button type="button" @click="slugVal = toSlug(titleVal)" class="text-xs text-brand-500 hover:text-brand-600 font-semibold px-2">Regenerate</button>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Summary / Excerpt</label>
                        <textarea name="excerpt" rows="2"
                            placeholder="Brief description shown in article previews…"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none">{{ old('excerpt') }}</textarea>
                    </div>

                    {{-- Tags --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Tags</label>
                        <div class="flex flex-wrap gap-1.5 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 min-h-[42px] cursor-text"
                            @click="$refs.tagInput.focus()">
                            <template x-for="tag in tags" :key="tag">
                                <span class="flex items-center gap-1 bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-700 rounded-full px-2.5 py-0.5 text-xs font-medium">
                                    <span x-text="tag"></span>
                                    <button type="button" @click="removeTag(tag)" class="hover:text-red-400">×</button>
                                </span>
                            </template>
                            <input x-ref="tagInput" type="text" placeholder="Add tag…"
                                class="flex-1 min-w-[80px] text-sm outline-none bg-transparent dark:text-white placeholder-gray-400"
                                @keydown.enter.prevent="addTag($event.target.value); $event.target.value = ''"
                                @keydown.comma.prevent="addTag($event.target.value); $event.target.value = ''"
                                @keydown.backspace="if (!$event.target.value) removeTag(tags[tags.length-1])">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Press Enter or comma to add. Press Backspace to remove last tag.</p>
                    </div>

                    {{-- Body --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Content <span class="text-red-400">*</span></label>
                        <textarea id="body-editor" name="body" rows="12" required
                            placeholder="Write your article content here…"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 leading-relaxed">{{ old('body') }}</textarea>
                    </div>

                    {{-- Optional URL --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">
                            Optional URL
                            <span class="ml-1 font-normal text-gray-400">(external link shown with article)</span>
                        </label>
                        <input type="url" name="optional_url"
                            value="{{ old('optional_url') }}"
                            placeholder="https://example.com/related-article"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
            </div>

            {{-- ── SOURCES ───────────────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Sources
                </h2>
                <p class="text-xs text-gray-400 mb-4">Cite where your information comes from. Add multiple sources with labels.</p>

                <div class="space-y-3" id="sources-list">
                    <template x-for="(src, i) in sources" :key="i">
                        <div class="flex items-start gap-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-600">
                            <div class="flex-1 grid grid-cols-1 sm:grid-cols-5 gap-2">
                                <div class="sm:col-span-2">
                                    <input type="text" :name="'sources[' + i + '][label]'" :value="src.label"
                                        @input="src.label = $event.target.value"
                                        placeholder="Label (e.g. Reuters, BBC)"
                                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                </div>
                                <div class="sm:col-span-3">
                                    <input type="url" :name="'sources[' + i + '][url]'" :value="src.url"
                                        @input="src.url = $event.target.value"
                                        placeholder="https://source.com/article"
                                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                </div>
                            </div>
                            <button type="button" @click="sources.splice(i, 1)" class="text-gray-400 hover:text-red-400 transition-colors mt-2 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </template>

                    <button type="button" @click="sources.push({ label: '', url: '' })"
                        class="flex items-center gap-2 text-sm text-brand-500 hover:text-brand-600 font-semibold transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Source
                    </button>
                </div>
            </div>

            {{-- ── FAQ ────────────────────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm"
                x-data="{ open: false }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                    <span class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        FAQ
                        <span class="text-xs font-normal text-gray-400">Optional</span>
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-6 pb-6">
                    <div class="space-y-3">
                        <template x-for="(faq, i) in articleFaq" :key="i">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-100 dark:border-gray-600">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">FAQ #<span x-text="i+1"></span></span>
                                    <button type="button" @click="articleFaq.splice(i,1)" class="text-gray-400 hover:text-red-400 text-xs">Remove</button>
                                </div>
                                <input type="text" :name="'article_faq_q[]'" :value="faq.q"
                                    @input="faq.q = $event.target.value"
                                    placeholder="Question…"
                                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 mb-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <textarea :name="'article_faq_a[]'" rows="2"
                                    @input="faq.a = $event.target.value"
                                    :value="faq.a"
                                    placeholder="Answer…"
                                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                            </div>
                        </template>
                        <button type="button" @click="articleFaq.push({ q: '', a: '' })"
                            class="flex items-center gap-2 text-sm text-brand-500 hover:text-brand-600 font-semibold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add FAQ
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── META OPTIONS ──────────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm"
                x-data="{ open: false }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                    <span class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Meta Options
                        <span class="text-xs font-normal text-gray-400">SEO</span>
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-6 pb-6 space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">SEO Title</label>
                        <input type="text" name="seo_title" value="{{ old('seo_title') }}"
                            placeholder="Overrides the article title for search engines"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Meta Description</label>
                        <textarea name="seo_desc" rows="2"
                            placeholder="160 characters max…"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none">{{ old('seo_desc') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ── SUBMIT BUTTONS ────────────────────────────────────────── --}}
            <div class="flex items-center justify-end gap-3 py-4">
                <a href="/dashboard" class="px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-gray-600 rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit" name="status" value="draft"
                    class="px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Save as Draft
                </button>
                <button type="submit" name="status" value="published"
                    class="px-6 py-2.5 text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Add Post
                </button>
            </div>

        </div>
    </form>
</div>

<script>
function articleForm() {
    return {
        titleVal: '{{ old('title') }}',
        slugVal: '{{ old('slug') }}',
        tags: [],
        sources: [{ label: '', url: '' }],
        articleFaq: [],

        init() {
            const oldTags = @json(old('tags', '[]'));
            try { this.tags = JSON.parse(oldTags); } catch {}
            this.$watch('titleVal', v => { if (!this.slugVal || this.slugVal === this.toSlug(this.titleVal.slice(0,-1))) this.slugVal = this.toSlug(v); });
        },

        toSlug(str) {
            return str.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim().replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        },

        autoSlug(e) {
            this.titleVal = e.target.value;
        },

        addTag(v) {
            v = v.trim().replace(/,/g, '');
            if (v && !this.tags.includes(v)) this.tags.push(v);
        },

        removeTag(v) {
            this.tags = this.tags.filter(t => t !== v);
        },
    }
}
</script>
@include('partials.tinymce', ['editorId' => 'body-editor', 'height' => 480])
@endsection
