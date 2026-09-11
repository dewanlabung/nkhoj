@extends('layouts.app')
@section('title', 'Stories — नखोज')
@section('content')

{{-- Upload strip --}}
@auth
<div class="mb-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4">
    <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Share a Story</h2>
    <form action="/stories" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
        @csrf
        <label class="flex-shrink-0 w-14 h-14 rounded-full border-2 border-dashed border-brand-300 hover:border-brand-500 flex items-center justify-center cursor-pointer transition-colors" for="story-media">
            <span class="text-2xl">+</span>
        </label>
        <input id="story-media" type="file" name="media" accept="image/*,video/*" class="hidden"
               onchange="this.form.submit()">
        <input type="text" name="caption" maxlength="500" placeholder="Optional caption..."
               class="flex-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400">
        <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl transition-colors">Post</button>
    </form>
</div>
@endauth

{{-- Stories grid --}}
@if($stories->count())
<div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
    @foreach($stories as $userId => $userStories)
    @php $first = $userStories->first(); @endphp
    <a href="/stories/{{ $first->id }}" class="flex-shrink-0 flex flex-col items-center gap-1 w-20">
        <div class="w-16 h-16 rounded-full ring-2 ring-brand-400 ring-offset-2 overflow-hidden bg-gray-200 dark:bg-gray-700">
            @if($first->media_type === 'image')
            <img src="{{ $first->media_url }}" alt="" class="w-full h-full object-cover">
            @else
            <div class="w-full h-full flex items-center justify-center text-2xl bg-gray-900">🎥</div>
            @endif
        </div>
        <span class="text-xs text-gray-600 dark:text-gray-400 truncate w-full text-center">{{ $first->user->name }}</span>
        @if($userStories->count() > 1)
        <span class="text-xs text-brand-500 font-semibold">+{{ $userStories->count() - 1 }}</span>
        @endif
    </a>
    @endforeach
</div>

{{-- Full story viewer --}}
<div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
    @foreach($stories->flatten() as $story)
    <a href="/stories/{{ $story->id }}" class="relative aspect-[9/16] rounded-2xl overflow-hidden bg-black group">
        @if($story->media_type === 'image')
        <img src="{{ $story->media_url }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
        <video src="{{ $story->media_url }}" class="w-full h-full object-cover" muted playsinline></video>
        @endif
        <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/70 to-transparent">
            <p class="text-white text-xs font-semibold truncate">{{ $story->user->name }}</p>
            @if($story->caption)
            <p class="text-white/80 text-xs truncate">{{ $story->caption }}</p>
            @endif
        </div>
        @auth
        @if(auth()->id() === $story->user_id)
        <form method="POST" action="/stories/{{ $story->id }}" class="absolute top-2 right-2 hidden group-hover:block">
            @csrf @method('DELETE')
            <button type="submit" class="w-7 h-7 bg-black/60 hover:bg-red-600 text-white rounded-full text-xs flex items-center justify-center">✕</button>
        </form>
        @endif
        @endauth
    </a>
    @endforeach
</div>
@else
<div class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
    <div class="text-4xl mb-3">📸</div>
    <p class="text-gray-500 dark:text-gray-400">No active stories right now.</p>
    @auth
    <p class="text-sm text-gray-400 mt-1">Share the first one above!</p>
    @endauth
</div>
@endif
@endsection
