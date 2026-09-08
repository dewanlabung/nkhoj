@extends('layouts.app')
@section('title', 'Add Article')

@section('content')
<div x-data="articleForm()" x-init="init()">

    {{-- Top bar --}}
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <a href="/dashboard/posts" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <nav class="flex items-center gap-1.5 text-xs text-gray-400">
                <a href="/dashboard" class="hover:text-brand-500">Dashboard</a>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="/dashboard/posts" class="hover:text-brand-500">Posts</a>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-600 dark:text-gray-300">Add Article</span>
            </nav>
        </div>
        <div class="flex items-center gap-2">
            <span class="hidden sm:inline text-xs text-gray-400 px-2.5 py-1 bg-gray-100 dark:bg-gray-700 rounded-full">Draft</span>
        </div>
    </div>

    <form method="POST" action="/dashboard/posts" enctype="multipart/form-data" id="article-form"
        @submit="$refs.tagsHidden.value = tags.join(', ')">
        @csrf
        <input type="hidden" name="post_format" value="article">
        <input type="hidden" name="tags" x-ref="tagsHidden">

        {{-- Two-column WordPress layout --}}
        <div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-6 items-start">

            {{-- ══ LEFT / MAIN COLUMN ══════════════════════════════════════ --}}
            <div class="space-y-4 min-w-0">

                {{-- Title --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm px-6 py-5">
                    <input type="text" name="title" required
                        value="{{ old('title') }}"
                        placeholder="Add title"
                        @input="autoSlug"
                        x-model="titleVal"
                        class="w-full text-3xl font-bold text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-600 border-0 outline-none focus:outline-none focus:ring-0 bg-transparent leading-tight">

                    {{-- Slug row --}}
                    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-50 dark:border-gray-700">
                        <span class="text-xs text-gray-400 flex-shrink-0">Slug:</span>
                        <input type="text" name="slug" x-model="slugVal"
                            placeholder="auto-generated-from-title"
                            class="flex-1 text-xs font-mono text-brand-600 dark:text-brand-400 border-0 outline-none bg-transparent focus:outline-none focus:ring-0 min-w-0">
                        <button type="button" @click="slugVal = toSlug(titleVal)"
                            class="text-xs text-gray-400 hover:text-brand-500 flex-shrink-0 transition-colors">↺ Regenerate</button>
                    </div>
                </div>

                {{-- Excerpt --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm px-6 py-5">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Excerpt / Summary</label>
                    <textarea name="excerpt" rows="3"
                        placeholder="Write a short summary shown in article previews and search results…"
                        class="w-full text-sm text-gray-700 dark:text-gray-300 placeholder-gray-300 dark:placeholder-gray-600 border-0 outline-none focus:outline-none focus:ring-0 bg-transparent resize-none leading-relaxed">{{ old('excerpt') }}</textarea>
                </div>

                {{-- Tags --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm px-6 py-4">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Tags</label>
                    <div class="flex flex-wrap gap-1.5 min-h-[34px] cursor-text" @click="$refs.tagInput.focus()">
                        <template x-for="tag in tags" :key="tag">
                            <span class="flex items-center gap-1 bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-700 rounded-full px-2.5 py-0.5 text-xs font-medium">
                                <span x-text="tag"></span>
                                <button type="button" @click="removeTag(tag)" class="hover:text-red-400 leading-none">×</button>
                            </span>
                        </template>
                        <input x-ref="tagInput" type="text" placeholder="Add tag…"
                            class="flex-1 min-w-[80px] text-sm outline-none bg-transparent dark:text-white placeholder-gray-300 dark:placeholder-gray-600"
                            @keydown.enter.prevent="addTag($event.target.value); $event.target.value = ''"
                            @keydown.comma.prevent="addTag($event.target.value); $event.target.value = ''"
                            @keydown.backspace="if (!$event.target.value) removeTag(tags[tags.length-1])">
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">Press Enter or comma to add.</p>
                </div>

                {{-- Body editor --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="px-6 pt-5 pb-2">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
                            Content <span class="text-red-400">*</span>
                        </label>
                    </div>
                    <textarea id="body-editor" name="body" rows="20" required
                        placeholder="Start writing your article…"
                        class="w-full text-sm border-0 outline-none focus:outline-none focus:ring-0 bg-transparent px-6 pb-6 dark:text-white leading-relaxed">{{ old('body') }}</textarea>
                </div>

                {{-- Optional URL --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm px-6 py-4">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                        Optional URL <span class="text-gray-400 font-normal normal-case">(external link shown with article)</span>
                    </label>
                    <input type="url" name="optional_url"
                        value="{{ old('optional_url') }}"
                        placeholder="https://example.com/related-article"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                {{-- Sources --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm px-6 py-5">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        Sources
                    </h3>
                    <p class="text-xs text-gray-400 mb-4">Cite where your information comes from.</p>
                    <div class="space-y-2">
                        <template x-for="(src, i) in sources" :key="i">
                            <div class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-600">
                                <input type="text" :name="'sources[' + i + '][label]'" :value="src.label"
                                    @input="src.label = $event.target.value"
                                    placeholder="Label (Reuters, BBC…)"
                                    class="w-32 flex-shrink-0 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <input type="url" :name="'sources[' + i + '][url]'" :value="src.url"
                                    @input="src.url = $event.target.value"
                                    placeholder="https://source.com/article"
                                    class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 min-w-0">
                                <button type="button" @click="sources.splice(i, 1)" class="text-gray-400 hover:text-red-400 flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="sources.push({ label: '', url: '' })"
                            class="flex items-center gap-1.5 text-sm text-brand-500 hover:text-brand-600 font-semibold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Source
                        </button>
                    </div>
                </div>

                {{-- FAQ (collapsible) --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-semibold text-gray-900 dark:text-white text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            FAQ
                            <span class="text-xs font-normal text-gray-400">Optional</span>
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
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
                                class="flex items-center gap-1.5 text-sm text-brand-500 hover:text-brand-600 font-semibold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add FAQ
                            </button>
                        </div>
                    </div>
                </div>

            </div>{{-- /left column --}}

            {{-- ══ RIGHT SIDEBAR ════════════════════════════════════════════ --}}
            <div class="space-y-4 xl:sticky xl:top-6">

                {{-- Publish card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Publish</h3>
                        <span class="text-xs text-gray-400 bg-gray-50 dark:bg-gray-700 px-2 py-0.5 rounded-full">New Draft</span>
                    </div>
                    <div class="p-4 space-y-3">
                        <button type="submit" name="status" value="draft"
                            class="w-full py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                            Save Draft
                        </button>
                        <button type="submit" name="status" value="published"
                            class="w-full py-2.5 text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Publish
                        </button>
                        <a href="/dashboard/posts" class="block text-center text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Featured Image --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden" x-data="{ preview: '{{ old('thumbnail_url') }}' }">
                    <div class="px-4 py-3 border-b border-gray-50 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Featured Image</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        {{-- Image preview --}}
                        <div class="relative">
                            <div x-show="!preview" class="aspect-video bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-600 flex flex-col items-center justify-center text-gray-400 cursor-pointer hover:border-brand-300 transition-colors" @click="$refs.fileInput.click()">
                                <svg class="w-8 h-8 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-xs">Click to upload</span>
                            </div>
                            <div x-show="preview" class="relative group aspect-video rounded-lg overflow-hidden border border-gray-100 dark:border-gray-600">
                                <img :src="preview" class="w-full h-full object-cover">
                                <button type="button" @click="preview = ''; $refs.fileInput.value = ''"
                                    class="absolute top-2 right-2 w-6 h-6 bg-black/60 hover:bg-black/80 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-xs">×</button>
                            </div>
                        </div>
                        <input type="file" name="thumbnail_file" x-ref="fileInput" class="hidden" accept="image/*"
                            @change="preview = URL.createObjectURL($event.target.files[0])">
                        <input type="url" name="thumbnail_url"
                            value="{{ old('thumbnail_url') }}"
                            x-model="preview"
                            placeholder="Or paste image URL…"
                            class="w-full text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <input type="text" name="image_caption"
                            value="{{ old('image_caption') }}"
                            placeholder="Caption / alt text"
                            class="w-full text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                {{-- Settings card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-50 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Settings</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Category <span class="text-red-400">*</span></label>
                            <select name="category_id" required class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
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
                            <select name="visibility" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="public" {{ old('visibility', 'public') === 'public' ? 'selected' : '' }}>Public</option>
                                <option value="members" {{ old('visibility') === 'members' ? 'selected' : '' }}>Members Only</option>
                                <option value="private" {{ old('visibility') === 'private' ? 'selected' : '' }}>Private</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Schedule</label>
                            <input type="datetime-local" name="scheduled_at"
                                value="{{ old('scheduled_at') }}"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>

                        {{-- Toggles --}}
                        <div class="space-y-2 pt-1">
                            @foreach([
                                ['is_featured','Featured'],
                                ['is_breaking','Breaking News'],
                                ['is_slider','Slider'],
                                ['is_recommended','Recommended'],
                            ] as [$name, $label])
                            <label class="flex items-center justify-between cursor-pointer py-1" x-data="{ on: false }">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                <div class="relative flex-shrink-0" @click.stop="on = !on">
                                    <input type="hidden" name="{{ $name }}" value="0">
                                    <input type="checkbox" name="{{ $name }}" value="1" class="sr-only" :checked="on">
                                    <div class="w-9 h-5 rounded-full transition-colors" :class="on ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-4' : ''"></div>
                                </div>
                            </label>
                            @endforeach

                            {{-- Premium toggle --}}
                            <label class="flex items-center justify-between cursor-pointer py-1 border-t border-gray-50 dark:border-gray-700 mt-2 pt-2" x-data="{ on: false }">
                                <div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Premium</span>
                                    <p class="text-xs text-gray-400">Members-only access</p>
                                </div>
                                <div class="relative flex-shrink-0" @click.stop="on = !on">
                                    <input type="hidden" name="is_pro" value="0">
                                    <input type="checkbox" name="is_pro" value="1" class="sr-only" :checked="on">
                                    <div class="w-9 h-5 rounded-full transition-colors" :class="on ? 'bg-amber-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-4' : ''"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Meta / SEO (collapsible) --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-left">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">SEO / Meta</h3>
                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-collapse class="px-4 pb-4 space-y-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">SEO Title</label>
                            <input type="text" name="seo_title" value="{{ old('seo_title') }}"
                                placeholder="Overrides article title for search engines"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Meta Description</label>
                            <textarea name="seo_desc" rows="3"
                                placeholder="160 characters max…"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none">{{ old('seo_desc') }}</textarea>
                        </div>
                    </div>
                </div>

            </div>{{-- /sidebar --}}

        </div>{{-- /two-column --}}
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
@include('partials.tinymce', ['editorId' => 'body-editor', 'height' => 560])
@endsection
