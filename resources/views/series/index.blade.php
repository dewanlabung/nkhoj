@extends('layouts.app')
@section('title', 'Post Series')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">📚 Post Series</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Multi-part article collections</p>
        </div>
        @auth
        <a href="/series/create"
           class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl transition-colors">
            + New Series
        </a>
        @endauth
    </div>

    @if($series->count())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($series as $s)
        <a href="/series/{{ $s->slug }}"
           class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden hover:shadow-md transition-shadow group">
            @if($s->cover_image)
            <img src="{{ $s->cover_image }}" loading="lazy" class="w-full h-36 object-cover group-hover:opacity-90 transition">
            @else
            <div class="w-full h-36 bg-gradient-to-br from-brand-400 to-indigo-500 flex items-center justify-center">
                <span class="text-4xl">📖</span>
            </div>
            @endif
            <div class="p-4">
                <h2 class="font-bold text-gray-900 dark:text-white group-hover:text-brand-600 transition-colors line-clamp-2 mb-1">
                    {{ $s->title }}
                </h2>
                @if($s->description)
                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-2 font-nepali">{{ $s->description }}</p>
                @endif
                <div class="flex items-center justify-between text-xs text-gray-400">
                    <span>by <a href="/profile/{{ $s->author->username }}" class="font-medium text-gray-600 dark:text-gray-300 hover:text-brand-600">{{ $s->author->name }}</a></span>
                    <span class="bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 font-semibold px-2 py-0.5 rounded-full">
                        {{ $s->published_posts_count }} parts
                    </span>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    @if($series->hasPages())
    <div class="mt-8">{{ $series->links() }}</div>
    @endif

    @else
    <div class="text-center py-20 text-gray-400">
        <div class="text-5xl mb-4">📚</div>
        <p class="font-semibold text-gray-600 dark:text-gray-300">No series yet</p>
        <p class="text-sm mt-1">Start a series to group related articles together.</p>
        @auth
        <a href="/series/create" class="mt-4 inline-block px-5 py-2 bg-brand-500 text-white rounded-xl font-semibold text-sm hover:bg-brand-600 transition-colors">
            Create First Series
        </a>
        @endauth
    </div>
    @endif
</div>
@endsection
