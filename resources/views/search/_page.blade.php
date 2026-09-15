<a href="/pages/{{ $page->slug }}"
    class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition block group">
    <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0 flex items-center justify-center">
        @if($page->avatar_url ?? $page->avatar ?? null)
        <img src="{{ $page->avatar_url ?? $page->avatar }}" alt="" class="w-full h-full object-cover">
        @else
        <span class="text-gray-500 font-bold text-lg">{{ strtoupper(substr($page->name, 0, 1)) }}</span>
        @endif
    </div>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-1.5">
            <p class="font-semibold text-sm text-gray-900 group-hover:text-brand-600 transition">{{ $page->name }}</p>
            @if($page->is_verified)
            <svg class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            @endif
        </div>
        <p class="text-xs text-gray-400 mt-0.5">{{ ucfirst($page->page_type ?? 'Page') }} · {{ number_format($page->followers_count) }} followers</p>
        @if($page->bio)
        <p class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $page->bio }}</p>
        @endif
    </div>
    <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>
