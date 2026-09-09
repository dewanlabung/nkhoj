@extends('layouts.app')
@section('title', $series->title . ' — Series')

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Series header --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden mb-6">
        @if($series->cover_image)
        <img src="{{ $series->cover_image }}" class="w-full h-48 object-cover">
        @else
        <div class="w-full h-48 bg-gradient-to-br from-brand-400 to-indigo-500 flex items-center justify-center">
            <span class="text-6xl">📖</span>
        </div>
        @endif
        <div class="p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="text-xs font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wide">Series</span>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $series->title }}</h1>
                    @if($series->description)
                    <p class="text-gray-500 dark:text-gray-400 mt-2 font-nepali">{{ $series->description }}</p>
                    @endif
                </div>
                <span class="flex-shrink-0 bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 font-black px-3 py-1.5 rounded-xl text-sm">
                    {{ $posts->count() }} parts
                </span>
            </div>
            <div class="flex items-center gap-2 mt-4 text-sm text-gray-500 dark:text-gray-400">
                <img src="{{ $series->author->avatar ?? '/default-avatar.png' }}" class="w-6 h-6 rounded-full">
                <a href="/profile/{{ $series->author->username }}" class="font-medium hover:text-brand-600 transition-colors">
                    {{ $series->author->name }}
                </a>
                <span>·</span>
                <span>{{ $series->created_at->format('M Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Posts list --}}
    <div class="space-y-3">
        @foreach($posts as $i => $post)
        <a href="/posts/{{ $post->slug }}"
           class="flex gap-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 hover:shadow-md hover:border-brand-200 dark:hover:border-brand-800 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center flex-shrink-0 font-black text-brand-600 dark:text-brand-400 text-lg">
                {{ $i + 1 }}
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="font-bold text-gray-900 dark:text-white group-hover:text-brand-600 transition-colors line-clamp-2 font-nepali">
                    {{ $post->title }}
                </h2>
                @if($post->excerpt)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1 font-nepali">{{ $post->excerpt }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-1">{{ $post->published_at?->format('M d, Y') }} · {{ number_format($post->view_count) }} views</p>
            </div>
            @if($post->thumbnail_url)
            <img src="{{ $post->thumbnail_url }}" class="w-16 h-14 rounded-lg object-cover flex-shrink-0">
            @endif
        </a>
        @endforeach
    </div>

    @if($posts->isEmpty())
    <div class="text-center py-12 text-gray-400">
        <p>No articles in this series yet.</p>
    </div>
    @endif
</div>
@endsection
