@extends('layouts.app')
@section('title', '#' . $tag->name_en . ' — nkhoj')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <div class="lg:col-span-3">
        <div class="mb-6 flex items-center gap-3">
            <span class="px-4 py-2 bg-brand-100 text-brand-700 text-xl font-bold rounded-full">#{{ $tag->name_en }}</span>
            @if($tag->name_ne)
                <span class="text-gray-400 text-lg">{{ $tag->name_ne }}</span>
            @endif
            <span class="text-sm text-gray-400 ml-auto">{{ $posts->total() }} articles</span>
        </div>

        @forelse($posts as $post)
        <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-4 flex gap-5 hover:shadow-md transition-shadow">
            @if($post->thumbnail_url)
            <a href="/posts/{{ $post->slug }}" class="flex-shrink-0">
                <img src="{{ $post->thumbnail_url }}" alt="{{ $post->title }}" class="w-28 h-20 object-cover rounded-lg">
            </a>
            @endif
            <div class="flex-1 min-w-0">
                @if($post->category)
                <a href="/category/{{ $post->category->slug }}" class="text-xs font-semibold text-brand-600 uppercase tracking-wide hover:text-brand-700">{{ $post->category->name_ne ?? $post->category->name_en }}</a>
                @endif
                <h2 class="mt-1 font-bold text-gray-900 hover:text-brand-600 transition-colors line-clamp-2">
                    <a href="/posts/{{ $post->slug }}">{{ $post->title }}</a>
                </h2>
                @if($post->excerpt)
                <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $post->excerpt }}</p>
                @endif
                <div class="mt-2 flex items-center gap-3 text-xs text-gray-400">
                    <span>{{ $post->author->name }}</span>
                    <span>{{ $post->published_at?->diffForHumans() }}</span>
                    <span>{{ number_format($post->view_count) }} views</span>
                </div>
            </div>
        </article>
        @empty
        <div class="text-center py-16 text-gray-400">
            <p class="text-lg">No articles with this tag yet.</p>
        </div>
        @endforelse

        <div class="mt-6">{{ $posts->links() }}</div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-4">All Categories</h3>
            <ul class="space-y-2">
                @foreach(\App\Models\Category::withCount(['posts' => fn($q) => $q->published()])->orderBy('sort_order')->get() as $cat)
                <li class="flex items-center justify-between">
                    <a href="/category/{{ $cat->slug }}" class="text-sm text-gray-700 hover:text-brand-600">{{ $cat->name_ne ?? $cat->name_en }}</a>
                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">{{ $cat->posts_count }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
