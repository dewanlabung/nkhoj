@extends('layouts.app')
@section('title', $story->user->name . ''s Story — नखोज')
@section('content')
<div class="max-w-sm mx-auto relative bg-black rounded-2xl overflow-hidden" style="height: 85vh; max-height: 720px;"
     x-data="{paused: false}">

    @if($story->media_type === 'video')
    <video src="{{ $story->media_url }}" class="w-full h-full object-cover" autoplay playsinline loop
           x-ref="vid" @click="$refs.vid.paused ? $refs.vid.play() : $refs.vid.pause(); paused = $refs.vid.paused"></video>
    @else
    <img src="{{ $story->media_url }}" alt="" class="w-full h-full object-cover">
    @endif

    {{-- Progress bar (auto-advance disabled since we don't have JS routing) --}}
    <div class="absolute top-0 left-0 right-0 h-1 bg-white/30 rounded-full m-2">
        <div class="h-full bg-white rounded-full animate-[progress_5s_linear]" style="animation-fill-mode:forwards"></div>
    </div>

    {{-- Header --}}
    <div class="absolute top-4 left-0 right-0 px-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-brand-400 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
            {{ strtoupper(substr($story->user->name, 0, 1)) }}
        </div>
        <div class="flex-1">
            <p class="text-white text-sm font-bold">{{ $story->user->name }}</p>
            <p class="text-white/70 text-xs">{{ $story->created_at->diffForHumans() }}</p>
        </div>
        <a href="/stories" class="text-white/70 hover:text-white text-xl">✕</a>
    </div>

    {{-- Caption --}}
    @if($story->caption)
    <div class="absolute bottom-0 left-0 right-0 p-5 bg-gradient-to-t from-black/80 to-transparent">
        <p class="text-white text-sm">{{ $story->caption }}</p>
    </div>
    @endif

    @if($story->media_type === 'video')
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div x-show="paused" class="w-14 h-14 bg-black/50 rounded-full flex items-center justify-center">
            <svg class="w-7 h-7 text-white ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        </div>
    </div>
    @endif
</div>

<style>
@keyframes progress { from { width: 0 } to { width: 100% } }
</style>
@endsection
