<a href="/posts/{{ $post->slug }}" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition block group">
    @if($post->thumbnail_url)
    <div class="w-16 h-14 rounded-lg overflow-hidden flex-shrink-0 relative">
        <img src="{{ $post->thumbnail_url }}" alt="" class="w-full h-full object-cover {{ ($post->is_sensitive ?? false) ? 'blur-sm' : '' }}">
        @if($post->is_sensitive ?? false)
        <span class="absolute inset-0 flex items-center justify-center text-base">⚠️</span>
        @endif
    </div>
    @endif
    <div class="flex-1 min-w-0">
        @if(isset($post->category))
        <p class="text-xs font-semibold text-brand-600 uppercase tracking-wide mb-0.5">
            {{ $post->category->name_ne ?? $post->category->name_en ?? '' }}
        </p>
        @endif
        <h3 class="font-semibold text-sm text-gray-900 group-hover:text-brand-600 transition line-clamp-2 font-nepali leading-snug">
            {{ $post->title }}
        </h3>
        <div class="flex items-center gap-1.5 text-xs text-gray-400 mt-1">
            <span>{{ $post->author->name ?? '' }}</span>
            @if($post->published_at)
            <span>·</span>
            <span>{{ $post->published_at->diffForHumans() }}</span>
            @endif
        </div>
    </div>
</a>
