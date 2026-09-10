<article class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex gap-4 hover:shadow-md transition-shadow {{ ($post->is_sensitive ?? false) ? 'border-amber-200' : '' }}">
    @if($post->thumbnail_url)
    <a href="/posts/{{ $post->slug }}" class="flex-shrink-0 relative">
        <img src="{{ $post->thumbnail_url }}" alt="" class="w-20 h-14 object-cover rounded-lg {{ ($post->is_sensitive ?? false) ? 'blur-sm' : '' }}">
        @if($post->is_sensitive ?? false)
        <span class="absolute inset-0 flex items-center justify-center text-lg">⚠️</span>
        @endif
    </a>
    @endif
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
            @if(isset($post->category))
            <span class="text-xs font-semibold text-brand-600 uppercase tracking-wide">
                {{ $post->category->name_ne ?? $post->category->name_en ?? '' }}
            </span>
            @endif
            @if($post->post_format && $post->post_format !== 'article')
            <span class="text-xs px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded capitalize">{{ $post->post_format }}</span>
            @endif
        </div>
        <h3 class="font-semibold text-gray-900 hover:text-brand-600 transition-colors text-sm leading-snug line-clamp-2 font-nepali">
            <a href="/posts/{{ $post->slug }}">{{ $post->title }}</a>
        </h3>
        @if($post->excerpt)
        <p class="text-xs text-gray-500 line-clamp-1 mt-0.5 font-nepali">{{ $post->excerpt }}</p>
        @endif
        <div class="flex items-center gap-2 text-xs text-gray-400 mt-1.5">
            <span>{{ $post->author->name ?? '' }}</span>
            <span>·</span>
            <span>{{ $post->published_at->diffForHumans() }}</span>
        </div>
    </div>
</article>
