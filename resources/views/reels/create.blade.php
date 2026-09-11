@extends('layouts.app')
@section('title', 'Upload Reel — नखोज')
@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">🎬 Upload a Reel</h1>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <form action="/reels" method="POST" enctype="multipart/form-data" class="space-y-4"
              x-data="{videoPreview: null, dragOver: false}">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Video *</label>
                <div class="relative border-2 border-dashed rounded-xl p-6 text-center transition-colors cursor-pointer"
                     :class="dragOver ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-brand-400'"
                     @dragover.prevent="dragOver = true"
                     @dragleave="dragOver = false"
                     @drop.prevent="dragOver = false; const f = $event.dataTransfer.files[0]; if(f) { videoPreview = URL.createObjectURL(f); $refs.videoInput.files = $event.dataTransfer.files; }">
                    <template x-if="!videoPreview">
                        <div>
                            <div class="text-4xl mb-2">🎥</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Drag & drop your video or <span class="text-brand-500 font-semibold">browse</span></p>
                            <p class="text-xs text-gray-400 mt-1">MP4, MOV, WebM · max 100 MB · portrait recommended</p>
                        </div>
                    </template>
                    <template x-if="videoPreview">
                        <video :src="videoPreview" class="max-h-64 mx-auto rounded-xl" controls playsinline></video>
                    </template>
                    <input type="file" name="video" accept="video/*" required x-ref="videoInput"
                           class="absolute inset-0 opacity-0 cursor-pointer"
                           @change="videoPreview = URL.createObjectURL($event.target.files[0])">
                </div>
                @error('video')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Thumbnail <span class="text-gray-400 font-normal">(optional — auto-generated from video)</span></label>
                <input type="file" name="thumbnail" accept="image/*"
                       class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300">
                @error('thumbnail')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Caption</label>
                <textarea name="description" maxlength="500" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 resize-none"
                          placeholder="What's this reel about?">{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-colors">
                Post Reel
            </button>
        </form>
    </div>
</div>
@endsection
