<a href="/recipes/{{ $recipe->slug ?? $recipe->uuid }}"
    class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex gap-4 hover:shadow-md transition-shadow block group">
    @if($recipe->thumbnail_url)
    <div class="w-20 h-14 rounded-lg overflow-hidden flex-shrink-0">
        <img src="{{ $recipe->thumbnail_url }}" alt="" class="w-full h-full object-cover">
    </div>
    @else
    <div class="w-14 h-14 rounded-xl bg-orange-50 flex items-center justify-center flex-shrink-0 text-2xl">🍽️</div>
    @endif
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-0.5">
            @if($recipe->cuisine_type)
            <span class="text-xs font-semibold text-orange-600 uppercase tracking-wide">{{ $recipe->cuisine_type }}</span>
            @endif
            @if($recipe->meal_type)
            <span class="text-xs px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded capitalize">{{ $recipe->meal_type }}</span>
            @endif
        </div>
        <h3 class="font-semibold text-gray-900 group-hover:text-brand-600 transition-colors text-sm line-clamp-2 font-nepali">
            {{ $recipe->title }}
        </h3>
        @if($recipe->description)
        <p class="text-xs text-gray-500 line-clamp-1 mt-0.5 font-nepali">{{ $recipe->description }}</p>
        @endif
        <div class="flex items-center gap-2 text-xs text-gray-400 mt-1.5">
            @if($recipe->total_time)
            <span>⏱ {{ $recipe->total_time }} मिनेट</span>
            @endif
            @if($recipe->difficulty)
            <span>·</span>
            <span class="capitalize">{{ $recipe->difficulty }}</span>
            @endif
            @if($recipe->author)
            <span>·</span>
            <span>{{ $recipe->author->name }}</span>
            @endif
        </div>
    </div>
    <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>
