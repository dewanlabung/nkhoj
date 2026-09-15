@extends('layouts.app')
@section('title', 'Go Live — नखोज')
@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">🔴 Start a Live Stream</h1>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <form action="/live" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Stream Title *</label>
                <input type="text" name="title" required maxlength="200" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-400" placeholder="What are you streaming about?">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" maxlength="1000" rows="3" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-400 resize-none" placeholder="Tell viewers what to expect..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">YouTube Live URL <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="url" name="embed_url" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-400" placeholder="https://youtube.com/watch?v=...">
                <p class="text-xs text-gray-400 mt-1">Paste your YouTube Live or Facebook Live watch URL. Viewers will see the embed.</p>
            </div>
            <button type="submit" class="w-full py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span> Go Live Now
            </button>
        </form>
    </div>
</div>
@endsection
