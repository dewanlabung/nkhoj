@extends('layouts.app')
@section('title', 'Post a Story')

@section('content')
<div class="max-w-lg mx-auto py-8">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Post a Story</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Your story will expire in 24 hours</p>

        <form action="/stories" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- Media Upload --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Photo or Video <span class="text-red-500">*</span>
                </label>
                <div x-data="{ preview: null }" class="relative">
                    <input type="file" name="media" accept="image/*,video/*" required
                        @change="
                            const file = $event.target.files[0];
                            if (file) {
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
                            <img :src="preview" class="w-full h-full object-cover" x-show="preview">
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

            {{-- Caption --}}
            <div>
                <label for="caption" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Caption (optional)
                </label>
                <textarea name="caption" id="caption" maxlength="500" rows="3"
                    placeholder="What's on your mind? Add a caption (0/500)"
                    class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    @input="$el.parentElement.querySelector('span').textContent = $el.value.length + '/500'"></textarea>
                <div class="text-right text-xs text-gray-500 dark:text-gray-400 mt-1">
                    <span>0/500</span>
                </div>
                @error('caption')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Add to Category --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Add to Story Highlight (optional)
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Group related stories together</p>

                <div class="space-y-2">
                    <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                        <input type="checkbox" name="highlights[]" value="featured" class="w-4 h-4 text-brand-500 rounded">
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Featured</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Highlight your best stories</p>
                        </div>
                    </label>

                    <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                        <input type="checkbox" name="highlights[]" value="breaking" class="w-4 h-4 text-red-500 rounded">
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Breaking News</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Mark as urgent/breaking</p>
                        </div>
                    </label>

                    <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                        <input type="checkbox" name="highlights[]" value="recommended" class="w-4 h-4 text-green-500 rounded">
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Recommended</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Community favorite</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex gap-3 pt-4">
                <a href="/stories" class="flex-1 px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-center">
                    Cancel
                </a>
                <button type="submit" class="flex-1 px-4 py-3 rounded-lg bg-brand-500 hover:bg-brand-600 text-white font-semibold transition-colors">
                    Post Story
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
