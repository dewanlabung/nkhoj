@extends('layouts.app')
@section('title', 'Create Watch Party — नखोज')
@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">🎉 Create Watch Party</h1>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <form action="/watch-party" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Party Name *</label>
                <input type="text" name="title" required maxlength="200" value="{{ old('title') }}"
                       class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                       placeholder="Movie night, study session...">
                @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">YouTube / Vimeo URL *</label>
                <input type="url" name="video_url" required value="{{ old('video_url') }}"
                       class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                       placeholder="https://youtube.com/watch?v=...">
                <p class="text-xs text-gray-400 mt-1">Paste a YouTube or Vimeo video URL. All members will watch the same video in sync.</p>
                @error('video_url')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="description" maxlength="500" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 resize-none"
                          placeholder="What are you watching?">{{ old('description') }}</textarea>
            </div>

            <div class="bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800 rounded-xl p-3 text-sm text-brand-700 dark:text-brand-300">
                <strong>How it works:</strong> You'll get a unique join code to share with friends. Everyone joins the same video and can chat in real-time.
            </div>

            <button type="submit" class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                🎉 Start Party
            </button>
        </form>
    </div>
</div>
@endsection
