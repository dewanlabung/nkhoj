@extends('layouts.app')
@section('title', 'Post a Story')

@section('content')
<div class="max-w-lg mx-auto py-8" x-data="{ mode: 'upload' }">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Post a Story</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Your story will expire in 24 hours</p>

        {{-- Mode tabs --}}
        <div class="flex gap-2 mb-6 p-1 bg-gray-100 dark:bg-gray-700 rounded-xl">
            <button type="button" @click="mode = 'upload'"
                :class="mode === 'upload' ? 'bg-white dark:bg-gray-800 shadow text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
                class="flex-1 py-2 text-sm font-semibold rounded-lg transition-all">
                📷 Upload Media
            </button>
            <button type="button" @click="mode = 'post'"
                :class="mode === 'post' ? 'bg-white dark:bg-gray-800 shadow text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
                class="flex-1 py-2 text-sm font-semibold rounded-lg transition-all">
                📰 From Blog Post
            </button>
        </div>

        @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 text-sm">
            {{ session('success') }}
        </div>
        @endif

        {{-- ── Upload Mode ─────────────────────────────────── --}}
        <div x-show="mode === 'upload'">
            <form action="/stories" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Photo or Video <span class="text-red-500">*</span>
                    </label>
                    <div x-data="{ preview: null, isVideo: false }" class="relative">
                        <input type="file" name="media" accept="image/*,video/*" required
                            @change="
                                const file = $event.target.files[0];
                                if (file) {
                                    isVideo = file.type.startsWith('video');
                                    const reader = new FileReader();
                                    reader.onload = (e) => preview = e.target.result;
                                    reader.readAsDataURL(file);
                                }
                            "
                            class="hidden" id="media-input">

                        <label for="media-input" class="block cursor-pointer">
                            <div x-show="!preview" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-brand-500 transition-colors">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Click to upload or drag and drop</p>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF, WebP, MP4, MOV or WebM (max 50MB)</p>
                            </div>
                            <div x-show="preview" class="relative rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700" style="aspect-ratio:9/16;">
                                <img x-show="!isVideo" :src="preview" class="w-full h-full object-cover">
                                <video x-show="isVideo" :src="preview" class="w-full h-full object-cover" muted></video>
                                <div class="absolute inset-0 bg-black/20 hover:bg-black/40 transition-colors flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">Change</span>
                                </div>
                            </div>
                        </label>

                        @error('media')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="caption" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Caption (optional)</label>
                    <textarea name="caption" id="caption" maxlength="500" rows="3"
                        placeholder="What's on your mind?"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"></textarea>
                    @error('caption')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="/stories" class="flex-1 px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-center">Cancel</a>
                    <button type="submit" class="flex-1 px-4 py-3 rounded-lg bg-brand-500 hover:bg-brand-600 text-white font-semibold transition-colors">Post Story</button>
                </div>
            </form>
        </div>

        {{-- ── From Blog Post Mode ──────────────────────────── --}}
        <div x-show="mode === 'post'" x-data="{ selected: null, preview: '', title: '', query: '' }">
            @if($myPosts->isEmpty())
            <div class="text-center py-12">
                <div class="text-4xl mb-3">📰</div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-nepali">तपाईंको कुनै प्रकाशित लेख छैन।</p>
                <a href="/posts/create" class="mt-4 inline-block text-brand-500 hover:underline text-sm font-medium">Create a post →</a>
            </div>
            @else
            <form action="/stories" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="post_id" :value="selected">

                {{-- Search box --}}
                <div>
                    <input type="text" x-model="query" placeholder="Search your posts…"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>

                {{-- Posts list --}}
                <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    @foreach($myPosts as $post)
                    <label
                        x-show="!query || '{{ strtolower($post->title) }}'.includes(query.toLowerCase())"
                        @click="selected = {{ $post->id }}; preview = '{{ $post->thumbnail_url }}'; title = '{{ addslashes($post->title) }}';"
                        :class="selected == {{ $post->id }} ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20 ring-2 ring-brand-500' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all">
                        <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                            @if($post->thumbnail_url)
                            <img src="{{ $post->thumbnail_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center text-xl font-black text-gray-300">{{ strtoupper(substr($post->title,0,1)) }}</div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2 font-nepali leading-snug">{{ $post->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $post->published_at->diffForHumans() }}</p>
                        </div>
                        <div x-show="selected == {{ $post->id }}" class="text-brand-500 flex-shrink-0">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Preview --}}
                <div x-show="selected" class="rounded-xl overflow-hidden bg-black" style="aspect-ratio:9/16; max-height:240px; position:relative;">
                    <img :src="preview" class="w-full h-full object-cover opacity-80">
                    <div class="absolute inset-0 flex items-end p-3">
                        <p x-text="title" class="text-white text-xs font-bold font-nepali line-clamp-2 drop-shadow"></p>
                    </div>
                    <div class="absolute top-2 right-2 bg-black/60 rounded-full px-2 py-0.5 text-[10px] text-white">📰 Post Story</div>
                </div>

                {{-- Caption --}}
                <div x-show="selected">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Caption (optional)</label>
                    <textarea name="caption" rows="2" maxlength="500"
                        placeholder="Add a caption…"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-brand-500 resize-none text-sm"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="/stories" class="flex-1 px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-center">Cancel</a>
                    <button type="submit" :disabled="!selected"
                        :class="selected ? 'bg-brand-500 hover:bg-brand-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-400 cursor-not-allowed'"
                        class="flex-1 px-4 py-3 rounded-lg font-semibold transition-colors">
                        Create Story
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
