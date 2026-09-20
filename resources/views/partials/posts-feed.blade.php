{{-- Posts grid partial — rendered standalone for AJAX tab switching --}}
<div class="grid grid-cols-2 gap-3">
    @forelse($posts as $post)
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

        <div class="p-2.5 flex-1 flex flex-col">
            <div class="flex items-center gap-1.5 mb-1.5">
                <a href="/profile/{{ $post->author->username }}" class="w-5 h-5 rounded-full bg-brand-500 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
                    {{ strtoupper(substr($post->author->name,0,1)) }}
                </a>
                <div class="flex-1 min-w-0">
                    <a href="/profile/{{ $post->author->username }}" class="text-[11px] font-semibold text-gray-700 dark:text-gray-200 hover:text-brand-600 transition-colors truncate block">{{ $post->author->name }}</a>
                </div>
            </div>

            <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-brand-600 transition-colors line-clamp-3 text-xs leading-snug font-nepali flex-1 mb-1.5">
                <a href="/posts/{{ $post->slug }}">{{ $post->title }}</a>
            </h3>

            <div class="flex items-center justify-between pt-1.5 border-t border-gray-50 dark:border-gray-700">
                <div class="text-[10px] text-gray-400">{{ $post->published_at->diffForHumans() }}</div>
                <div class="text-[10px] text-gray-400">{{ number_format($post->view_count) }}v</div>
            </div>
        </div>
    </article>
    @empty
    <div class="col-span-2 text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
        <div class="text-5xl mb-4">📰</div>
        <p class="text-gray-500 dark:text-gray-400 font-nepali">यस श्रेणीमा कुनै लेख छैन।</p>
    </div>
    @endforelse
</div>

<div hidden data-feed-meta data-has-more="{{ $posts->hasMorePages() ? 'true' : 'false' }}" data-current-page="{{ $posts->currentPage() }}"></div>

@if($posts->hasPages())
<noscript><div class="flex justify-center mt-8">{{ $posts->links() }}</div></noscript>
@endif
