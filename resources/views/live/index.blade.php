@extends('layouts.app')
@section('title', 'Live — नखोज')
@section('content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">🔴 Live Streams</h1>
        <p class="text-sm text-gray-400 mt-0.5">Watch live and join the conversation</p>
    </div>
    @auth
    <a href="/live/create" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold text-sm rounded-xl transition-colors flex items-center gap-2">
        <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span> Go Live
    </a>
    @endauth
</div>

@if($live->count())
<h2 class="text-sm font-bold text-red-500 uppercase tracking-widest mb-3 flex items-center gap-2">
    <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span> Now Live
</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @foreach($live as $stream)
    <a href="/live/{{ $stream->id }}" class="group block bg-white dark:bg-gray-800 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
        <div class="relative aspect-video bg-gray-900 flex items-center justify-center">
            @if($stream->thumbnail_url)
            <img src="{{ $stream->thumbnail_url }}" alt="" class="w-full h-full object-cover">
            @else
            <div class="text-5xl">🔴</div>
            @endif
            <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span> LIVE
            </span>
            <span class="absolute bottom-2 right-2 bg-black/70 text-white text-xs px-2 py-0.5 rounded-full">
                👁 {{ number_format($stream->viewer_count) }}
            </span>
        </div>
        <div class="p-3">
            <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ $stream->title }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $stream->user->name }} · {{ $stream->started_at->diffForHumans() }}</p>
        </div>
    </a>
    @endforeach
</div>
{{ $live->links() }}
@else
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-12 text-center mb-8">
    <div class="text-4xl mb-3">📡</div>
    <p class="text-gray-500 dark:text-gray-400">No one is live right now.</p>
    @auth
    <a href="/live/create" class="mt-4 inline-block px-5 py-2 bg-red-500 text-white text-sm font-semibold rounded-xl hover:bg-red-600 transition-colors">Be the first to go live</a>
    @endauth
</div>
@endif

@if($past->count())
<h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-3">Recent Streams</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach($past as $stream)
    <a href="/live/{{ $stream->id }}" class="group block bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
        <div class="relative aspect-video bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
            @if($stream->thumbnail_url)
            <img src="{{ $stream->thumbnail_url }}" alt="" class="w-full h-full object-cover opacity-70">
            @else
            <div class="text-3xl opacity-40">📹</div>
            @endif
        </div>
        <div class="p-3">
            <p class="font-medium text-gray-900 dark:text-white text-sm truncate">{{ $stream->title }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $stream->user->name }} · {{ $stream->peak_viewers }} peak viewers</p>
        </div>
    </a>
    @endforeach
</div>
@endif
@endsection
