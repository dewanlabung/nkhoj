<div class="flex items-center gap-3 px-4 py-3">
    <a href="/profile/{{ $user->username }}" class="flex-shrink-0">
        <div class="w-12 h-12 rounded-full overflow-hidden bg-brand-500 flex items-center justify-center">
            @if($user->avatar_url ?? $user->avatar ?? null)
            <img src="{{ $user->avatar_url ?? $user->avatar }}" alt="" class="w-full h-full object-cover">
            @else
            <span class="text-white font-bold text-lg">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            @endif
        </div>
    </a>
    <div class="flex-1 min-w-0">
        <a href="/profile/{{ $user->username }}">
            <p class="font-semibold text-sm text-gray-900">{{ $user->name }}</p>
        </a>
        <p class="text-xs text-brand-500">@{{ $user->username }}</p>
        @if($user->bio)
        <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $user->bio }}</p>
        @endif
    </div>
    <a href="/profile/{{ $user->username }}"
       class="flex-shrink-0 px-4 py-1.5 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full hover:bg-gray-200 transition">
        View profile
    </a>
</div>
