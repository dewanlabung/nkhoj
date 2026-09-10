<a href="/profile/{{ $user->username }}"
    class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4 hover:shadow-md transition-shadow block group">
    <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0">
        @if($user->avatar)
        <img src="{{ $user->avatar }}" alt="" class="w-full h-full object-cover">
        @else
        <div class="w-full h-full bg-brand-500 flex items-center justify-center text-white font-bold text-lg">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        @endif
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-semibold text-gray-900 group-hover:text-brand-600 transition-colors text-sm">{{ $user->name }}</p>
        <p class="text-xs text-brand-500">@{{ $user->username }}</p>
        @if($user->bio)
        <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $user->bio }}</p>
        @endif
    </div>
    <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>
