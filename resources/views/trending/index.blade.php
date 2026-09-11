@extends('layouts.app')
@section('title', 'Trending Topics — नखोज')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">🔥 Trending Topics</h1>
        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-2 py-1 rounded-full">Last 24 hours · refreshes every 10 min</span>
    </div>

    @if($trending->count())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden">
        @foreach($trending as $i => $topic)
        <a href="/tags/{{ $topic->slug }}" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
            <span class="text-2xl font-black text-gray-200 dark:text-gray-600 w-8 text-center tabular-nums">{{ $i + 1 }}</span>
            <div class="flex-1">
                <p class="font-bold text-gray-900 dark:text-white group-hover:text-brand-500 transition-colors">#{{ $topic->name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ number_format($topic->count) }} {{ Str::plural('post', $topic->count) }}</p>
            </div>
            @if($i < 3)
            <span class="text-lg">{{ ['🥇','🥈','🥉'][$i] }}</span>
            @endif
        </a>
        @endforeach
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-16 text-center">
        <div class="text-4xl mb-3">🔍</div>
        <p class="text-gray-500 dark:text-gray-400">No trending topics yet — check back soon.</p>
    </div>
    @endif
</div>
@endsection
