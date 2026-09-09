@extends('layouts.app')
@section('title', 'Following Feed — nkhoj')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-full bg-brand-500 flex items-center justify-center text-white">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">फलो गरिएकाहरूको फिड</h1>
            <p class="text-sm text-gray-400">Following Feed</p>
        </div>
        <a href="/" class="ml-auto text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 flex items-center gap-1">
            ← Home
        </a>
    </div>

    @if($posts->isEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-16 text-center">
        <div class="text-5xl mb-4">👥</div>
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2 font-nepali">फिड खाली छ</h2>
        <p class="text-gray-400 text-sm mb-6 font-nepali">तपाईंले फलो गर्नुभएका लेखकहरूले अहिलेसम्म केही प्रकाशित गर्नुभएको छैन।</p>
        <a href="/" class="inline-block px-5 py-2.5 bg-brand-500 text-white font-semibold text-sm rounded-xl hover:bg-brand-600 transition-colors">
            नयाँ लेखकहरू खोज्नुहोस्
        </a>
    </div>
    @else
    <div id="posts-feed"
        x-data="{
            page: {{ $posts->currentPage() }},
            hasMore: {{ $posts->hasMorePages() ? 'true' : 'false' }},
            loading: false,
            async loadMore() {
                if (this.loading || !this.hasMore) return;
                this.loading = true;
                this.page++;
                const res = await fetch('/following/feed?ajax=1&page=' + this.page);
                const html = await res.text();
                const tmp = document.createElement('div');
                tmp.innerHTML = html;
                const newCards = Array.from(tmp.querySelectorAll('article'));
                const grid = this.$el.querySelector('.grid');
                if (grid && newCards.length) newCards.forEach(c => grid.appendChild(c));
                const meta = tmp.querySelector('[data-feed-meta]');
                this.hasMore = meta?.dataset.hasMore === 'true';
                this.loading = false;
            }
        }">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($posts as $post)
            <article class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden group flex flex-col">
                <a href="/posts/{{ $post->slug }}" class="block relative overflow-hidden flex-shrink-0" style="aspect-ratio:16/9;">
                    @if($post->thumbnail_url)
                    <img src="{{ $post->thumbnail_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    @else
                    <div class="w-full h-full bg-gradient-to-br from-brand-50 via-indigo-50 to-brand-100 flex items-center justify-center">
                        <span class="text-4xl font-black text-brand-200/80">{{ strtoupper(substr($post->title,0,1)) }}</span>
                    </div>
                    @endif
                    <div class="absolute top-3 left-3">
                        <span class="text-xs px-2.5 py-1 bg-brand-500 text-white rounded-full font-bold">
                            {{ $post->category->name_ne ?? $post->category->name_en }}
                        </span>
                    </div>
                </a>
                <div class="p-4 flex-1 flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <a href="/profile/{{ $post->author->username }}" class="w-7 h-7 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($post->author->name,0,1)) }}
                        </a>
                        <div class="flex-1 min-w-0">
                            <a href="/profile/{{ $post->author->username }}" class="text-xs font-semibold text-gray-700 dark:text-gray-200 hover:text-brand-600">{{ $post->author->name }}</a>
                            <div class="text-xs text-gray-400">{{ $post->published_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-brand-600 transition-colors line-clamp-2 text-sm leading-snug font-nepali flex-1 mb-2">
                        <a href="/posts/{{ $post->slug }}">{{ $post->title }}</a>
                    </h3>
                    @if($post->excerpt)
                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-3 font-nepali leading-relaxed">{{ $post->excerpt }}</p>
                    @endif
                    <div class="flex items-center justify-between pt-2 border-t border-gray-50 dark:border-gray-700 text-xs text-gray-400">
                        <span>{{ $post->readingTimeMinutes() }} min read</span>
                        <span>{{ number_format($post->view_count) }} views</span>
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        <div hidden data-feed-meta data-has-more="{{ $posts->hasMorePages() ? 'true' : 'false' }}"></div>

        {{-- Infinite scroll sentinel --}}
        <div x-intersect.threshold.10="loadMore" class="h-4 mt-4"></div>

        <div x-show="loading" class="flex justify-center py-6">
            <div class="w-6 h-6 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        @if($posts->hasPages())
        <noscript><div class="flex justify-center mt-8">{{ $posts->links() }}</div></noscript>
        @endif
    </div>
    @endif
</div>
@endsection
