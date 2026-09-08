<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden text-center hover:shadow-md transition group">
    <a href="/pages/{{ $page->slug }}">
        {{-- Avatar --}}
        <div class="pt-5 pb-2 px-4 flex justify-center">
            <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-200 ring-2 ring-gray-100 group-hover:ring-blue-200 transition">
                <img src="{{ $page->avatar }}" class="w-full h-full object-cover" loading="lazy">
            </div>
        </div>

        {{-- Name --}}
        <div class="px-3 pb-1">
            <p class="font-bold text-gray-900 text-sm line-clamp-1 group-hover:text-blue-600 transition">
                {{ $page->name }}
                @if($page->is_verified)
                    <svg class="w-3.5 h-3.5 text-blue-500 inline-block" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </p>
            @if($page->first_category)
                <p class="text-gray-400 text-xs mt-0.5">{{ $page->first_category }}</p>
            @endif
            <p class="text-gray-500 text-xs mt-0.5 mb-3">{{ number_format($page->followers_count) }} {{ Str::plural('follower', $page->followers_count) }}</p>
        </div>
    </a>

    {{-- Follow button --}}
    <div class="px-3 pb-4">
        @if($showFollow ?? true)
            @auth
                <form method="POST" action="/pages/{{ $page->slug }}/follow">
                    @csrf
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 rounded-xl transition flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                        </svg>
                        Like
                    </button>
                </form>
            @else
                <a href="/login"
                   class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 rounded-xl transition">
                    Like
                </a>
            @endauth
        @endif
    </div>
</div>
