<a href="/events/{{ $event->slug ?? $event->uuid }}"
    class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex gap-4 hover:shadow-md transition-shadow block group">
    @if($event->thumbnail_url)
    <div class="w-20 h-14 rounded-lg overflow-hidden flex-shrink-0">
        <img src="{{ $event->thumbnail_url }}" alt="" class="w-full h-full object-cover">
    </div>
    @else
    <div class="w-14 h-14 rounded-xl bg-brand-50 flex items-center justify-center flex-shrink-0 text-2xl">📅</div>
    @endif
    <div class="flex-1 min-w-0">
        <h3 class="font-semibold text-gray-900 group-hover:text-brand-600 transition-colors text-sm line-clamp-2 font-nepali">
            {{ $event->title }}
        </h3>
        <div class="flex items-center gap-2 text-xs text-gray-400 mt-1.5">
            @if($event->starts_at)
            <span>{{ $event->starts_at->format('M j, Y') }}</span>
            @endif
            @if($event->venue)
            <span>·</span>
            <span class="truncate">{{ $event->venue }}</span>
            @endif
        </div>
        @if($event->organizer)
        <p class="text-xs text-gray-400 mt-0.5">by {{ $event->organizer }}</p>
        @endif
    </div>
    <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>
