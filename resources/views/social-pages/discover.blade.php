@extends('layouts.app')

@section('title', 'Pages — Discover')

@section('content')
<div class="min-h-screen bg-gray-50">

    {{-- Hero Banner --}}
    <div class="relative bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-10 -left-10 w-64 h-64 bg-white rounded-full"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -translate-y-1/2 translate-x-1/4"></div>
        </div>
        <div class="relative max-w-5xl mx-auto px-4 py-10 text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-6">Discover Pages</h1>
            <form method="GET" action="/pages" class="max-w-xl mx-auto">
                <div class="flex bg-white rounded-xl overflow-hidden shadow-lg">
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Search for pages..."
                           class="flex-1 px-5 py-3.5 text-base text-gray-800 focus:outline-none">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3.5 transition text-sm">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabs + Create button --}}
    <div class="bg-white border-b border-gray-200 sticky top-0 z-20 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 flex items-center justify-between">
            <div class="flex overflow-x-auto scrollbar-hide">
                <a href="/pages"
                   class="shrink-0 px-5 py-4 text-sm font-semibold border-b-2 transition whitespace-nowrap
                          {{ !request('tab') || request('tab') === 'discover' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                    Discover
                </a>
                @auth
                    <a href="/pages?tab=liked"
                       class="shrink-0 px-5 py-4 text-sm font-semibold border-b-2 transition whitespace-nowrap
                              {{ request('tab') === 'liked' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                        Liked Pages
                    </a>
                    <a href="/pages?tab=mine"
                       class="shrink-0 px-5 py-4 text-sm font-semibold border-b-2 transition whitespace-nowrap
                              {{ request('tab') === 'mine' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                        My Pages
                    </a>
                @endauth
                <button id="nearby-btn" onclick="findNearby()"
                    class="shrink-0 px-5 py-4 text-sm font-semibold border-b-2 transition whitespace-nowrap
                           {{ request('tab') === 'nearby' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                    📍 Nearby
                </button>
            </div>
            <a href="{{ auth()->check() ? '/pages/create' : '/pages/start' }}"
               class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-xl text-sm transition flex items-center gap-1.5 ml-4">
                <span class="text-lg leading-none">+</span> Create Page
            </a>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 py-6">
        <div class="flex gap-6">

            {{-- Left sidebar: categories --}}
            <aside class="hidden md:block w-48 shrink-0">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-20">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="font-bold text-gray-900 text-sm">Category</p>
                    </div>
                    @php
                        $sidebarCats = [
                            'All', 'Arts & Entertainment', 'Business', 'Cars & Vehicles',
                            'Comedy', 'Economics & Trade', 'Education', 'Food & Drink',
                            'Gaming', 'Health & Fitness', 'Lifestyle', 'Music',
                            'News & Media', 'Politics', 'Science & Tech', 'Sports',
                            'Travel', 'Non-profit',
                        ];
                        $activeCategory = request('category', 'All');
                    @endphp
                    <ul class="py-1">
                        @foreach($sidebarCats as $cat)
                            <li>
                                <a href="/pages?category={{ urlencode($cat) }}{{ request('tab') ? '&tab='.request('tab') : '' }}"
                                   class="block px-4 py-2 text-sm transition
                                          {{ $activeCategory === $cat
                                              ? 'text-blue-600 font-semibold bg-blue-50'
                                              : 'text-gray-700 hover:bg-gray-50' }}">
                                    {{ $cat }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>

            {{-- Main content --}}
            <div class="flex-1 min-w-0">

                {{-- Mobile category pills --}}
                <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-3 mb-4 md:hidden">
                    @foreach(['All', 'Business', 'Education', 'Music', 'Sports', 'Food', 'Tech', 'News'] as $mcat)
                        <a href="/pages?category={{ urlencode($mcat) }}"
                           class="shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition border
                                  {{ $activeCategory === $mcat
                                      ? 'bg-blue-600 text-white border-blue-600'
                                      : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300' }}">
                            {{ $mcat }}
                        </a>
                    @endforeach
                </div>

                @php $tab = request('tab', 'discover'); @endphp

                {{-- TAB: My Pages --}}
                @if($tab === 'mine' && auth()->check())
                    <h2 class="font-bold text-gray-900 mb-4 text-base">Your Pages</h2>
                    @if($myPages->count())
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($myPages as $myPage)
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden text-center p-4 hover:shadow-md transition">
                                    <a href="/pages/{{ $myPage->slug }}">
                                        <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-200 mx-auto mb-3 ring-2 ring-gray-100">
                                            <img src="{{ $myPage->avatar }}" class="w-full h-full object-cover">
                                        </div>
                                        <p class="font-bold text-gray-900 text-sm line-clamp-1">{{ $myPage->name }}</p>
                                        <p class="text-gray-400 text-xs mb-3">{{ number_format($myPage->followers_count) }} followers</p>
                                    </a>
                                    <a href="/pages/{{ $myPage->slug }}/dashboard"
                                       class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 rounded-xl transition">
                                        Dashboard
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                            <p class="text-gray-500 mb-4">You haven't created any pages yet.</p>
                            <a href="/pages/create" class="inline-block bg-blue-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-blue-700 transition">
                                Create your first Page
                            </a>
                        </div>
                    @endif

                {{-- TAB: Liked --}}
                @elseif($tab === 'liked' && auth()->check())
                    <h2 class="font-bold text-gray-900 mb-4 text-base">Pages you follow</h2>
                    @if($pages->count())
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($pages as $page)
                                @include('social-pages._card', ['page' => $page, 'showFollow' => false])
                            @endforeach
                        </div>
                        <div class="mt-6">{{ $pages->links() }}</div>
                    @else
                        <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                            <p class="text-gray-500">You don't follow any pages yet.</p>
                        </div>
                    @endif

                {{-- TAB: Nearby --}}
                @elseif($tab === 'nearby')
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-gray-900 text-base">📍 Pages Near You</h2>
                        <button onclick="findNearby()" class="text-sm text-blue-600 hover:underline font-semibold">Refresh location</button>
                    </div>
                    @if($pages->count())
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($pages as $page)
                                @include('social-pages._card', ['page' => $page, 'showFollow' => true])
                            @endforeach
                        </div>
                        <div class="mt-6">{{ $pages->links() }}</div>
                    @else
                        <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                            <p class="text-4xl mb-4">📍</p>
                            <p class="text-gray-500 font-medium mb-2">No nearby pages found</p>
                            <p class="text-gray-400 text-sm">Try creating a page for your local business!</p>
                        </div>
                    @endif

                {{-- TAB: Discover (default) --}}
                @else
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-gray-900 text-base">
                            @if(request('q'))
                                Results for "{{ request('q') }}"
                            @elseif($activeCategory !== 'All')
                                {{ $activeCategory }}
                            @else
                                @auth Suggested for you @else Discover @endauth
                            @endif
                        </h2>
                    </div>

                    @if($pages->count())
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($pages as $page)
                                @include('social-pages._card', ['page' => $page, 'showFollow' => true])
                            @endforeach
                        </div>
                        <div class="mt-6">{{ $pages->links() }}</div>
                    @else
                        <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                            <p class="text-gray-500 text-lg font-medium mb-2">No pages found</p>
                            <p class="text-gray-400 text-sm mb-6">Be the first to create a page!</p>
                            <a href="{{ auth()->check() ? '/pages/create' : '/pages/start' }}"
                               class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-xl transition">
                                Create a Page
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function findNearby() {
    const btn = document.getElementById('nearby-btn');
    if (btn) btn.textContent = '⏳ Locating...';
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        if (btn) btn.textContent = '📍 Nearby';
        return;
    }
    navigator.geolocation.getCurrentPosition(function(pos) {
        const lat = pos.coords.latitude.toFixed(6);
        const lng = pos.coords.longitude.toFixed(6);
        window.location.href = '/pages?tab=nearby&near=' + lat + ',' + lng;
    }, function() {
        alert('Unable to get your location. Please allow location access and try again.');
        if (btn) btn.textContent = '📍 Nearby';
    });
}
</script>
@endpush

@endsection
