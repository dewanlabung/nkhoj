<a href="/recipes/{{ $recipe->slug ?? $recipe->uuid }}"
    class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition block group">
    @if($recipe->thumbnail_url)
    <div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0">
        <img src="{{ $recipe->thumbnail_url }}" alt="" class="w-full h-full object-cover">
    </div>
    @else
    <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center flex-shrink-0 text-xl">🍽️</div>
    @endif
    <div class="flex-1 min-w-0">
        <h3 class="font-semibold text-sm text-gray-900 group-hover:text-brand-600 transition line-clamp-1 font-nepali">
            {{ $recipe->title }}
        </h3>
        <div class="flex items-center gap-1.5 text-xs text-gray-400 mt-0.5">
            @if($recipe->cuisine_type)
            <span>{{ $recipe->cuisine_type }}</span>
            @endif
            @if($recipe->meal_type)
            <span>·</span>
            <span class="capitalize">{{ $recipe->meal_type }}</span>
            @endif
            @if($recipe->total_time)
            <span>·</span>
            <span>⏱ {{ $recipe->total_time }}m</span>
            @endif
        </div>
        @if($recipe->author)
        <p class="text-xs text-gray-400 mt-0.5">by {{ $recipe->author->name }}</p>
        @endif
    </div>
    <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>
