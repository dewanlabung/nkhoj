@extends('layouts.app')
@section('title', 'Search: ' . request('q') . ' — nkhoj')

@section('content')
<div class="max-w-3xl mx-auto">
    <form action="/search" method="GET" class="mb-8">
        <div class="relative">
            <input type="text" name="q" value="{{ $query }}" autofocus
                placeholder="समाचार खोज्नुहोस्..."
                class="w-full pl-12 pr-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-brand-500 bg-white shadow-sm">
            <svg class="absolute left-4 top-4 h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <button type="submit" class="absolute right-3 top-3 px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-xl hover:bg-brand-600 transition-colors">
                Search
            </button>
        </div>
    </form>

    @if($query)
    <p class="text-sm text-gray-500 mb-5">
        {{ $posts->total() }} results for "<span class="font-semibold text-gray-800">{{ $query }}</span>"
    </p>

    @if($enhanced)
    <div class="mb-6 p-4 bg-brand-50 rounded-xl border border-brand-100">
        <p class="text-xs font-semibold text-brand-600 mb-2">🤖 AI-enhanced search</p>
        <div class="flex flex-wrap gap-2">
            @foreach($enhanced['expanded_terms'] ?? [] as $term)
            <a href="/search?q={{ urlencode($term) }}"
                class="text-xs px-2.5 py-1 bg-white border border-brand-200 text-brand-600 rounded-full hover:bg-brand-100 transition-colors">
                {{ $term }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="space-y-4">
        @forelse($posts as $post)
        <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex gap-4 hover:shadow-md transition-shadow">
            @if($post->thumbnail_url)
            <img src="{{ $post->thumbnail_url }}" alt="" class="w-24 h-16 object-cover rounded-lg flex-shrink-0">
            @endif
            <div>
                <a href="/category/{{ $post->category->slug }}" class="text-xs font-semibold text-brand-600 uppercase tracking-wide">
                    {{ $post->category->name_en }}
                </a>
                <h3 class="font-semibold text-gray-900 hover:text-brand-600 transition-colors mt-1">
                    <a href="/posts/{{ $post->slug }}">{{ $post->title }}</a>
                </h3>
                <p class="text-sm text-gray-500 line-clamp-2 mt-1">{{ $post->excerpt }}</p>
                <div class="text-xs text-gray-400 mt-2">{{ $post->author->name }} · {{ $post->published_at->diffForHumans() }}</div>
            </div>
        </article>
        @empty
        <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
            <div class="text-4xl mb-4">🔍</div>
            <p class="text-gray-500">No results found for "{{ $query }}"</p>
            <p class="text-gray-400 text-sm mt-1">Try a different keyword</p>
        </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
