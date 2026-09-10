@extends('layouts.app')
@section('title', $query ? 'खोज: ' . $query . ' — nkhoj' : 'खोज — nkhoj')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Compact sticky search bar --}}
    <div class="sticky top-0 z-20 bg-gray-50 pt-3 pb-2.5 px-4 border-b border-gray-100">
        <form action="/search" method="GET" id="search-form">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="flex items-center gap-2.5">
                <div class="flex-1 flex items-center bg-white rounded-full border border-gray-200 shadow-sm px-3.5 py-2.5 gap-2 focus-within:border-brand-400 focus-within:ring-2 focus-within:ring-brand-100 transition">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="q" id="search-q" value="{{ $query }}" autofocus
                        placeholder="समाचार, पेजहरू, घटनाहरू खोज्नुहोस्..."
                        class="flex-1 text-sm bg-transparent focus:outline-none text-gray-900 placeholder-gray-400">
                    @if($query)
                    <a href="/search" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                    @endif
                </div>
                @if($query)
                <button type="submit" class="text-brand-600 text-sm font-semibold flex-shrink-0">खोज्नुस्</button>
                @endif
            </div>
        </form>
    </div>

    @if(!$query)
    {{-- ── Empty state ── --}}

    {{-- Recent searches --}}
    @if($recent->isNotEmpty())
    <div class="mt-4">
        <div class="flex items-center justify-between px-4 mb-1">
            <p class="font-bold text-gray-900 text-base">Recent</p>
            {{-- could add clear-all here later --}}
        </div>
        <div class="bg-white border-y border-gray-100">
            @foreach($recent as $term)
            <a href="/search?q={{ urlencode($term) }}"
               class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition border-b border-gray-50 last:border-0">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="flex-1 text-sm font-medium text-gray-900">{{ $term }}</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7M17 7v10"/>
                </svg>
            </a>
            @endforeach
        </div>
    </div>

    @elseif($trending->isNotEmpty())
    {{-- Trending (shown when no personal history) --}}
    <div class="mt-4 px-4">
        <p class="font-bold text-gray-900 text-base mb-3">🔥 ट्रेन्डिङ खोजहरू</p>
        <div class="flex flex-wrap gap-2">
            @foreach($trending as $trend)
            <a href="/search?q={{ urlencode($trend) }}"
               class="flex items-center gap-1.5 px-3.5 py-2 bg-white border border-gray-200 rounded-full text-sm text-gray-700 font-medium hover:border-brand-400 hover:text-brand-600 transition shadow-sm">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                {{ $trend }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- People you may know --}}
    @if($suggested->isNotEmpty())
    <div class="mt-5">
        <p class="font-bold text-gray-900 text-base px-4 mb-1">People you may know</p>
        <div class="bg-white border-y border-gray-100">
            @foreach($suggested as $person)
            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 last:border-0">
                <a href="/profile/{{ $person->username }}" class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full overflow-hidden bg-brand-500 flex items-center justify-center">
                        @if($person->avatar_url)
                        <img src="{{ $person->avatar_url }}" alt="" class="w-full h-full object-cover">
                        @else
                        <span class="text-white font-bold text-lg">{{ strtoupper(substr($person->name, 0, 1)) }}</span>
                        @endif
                    </div>
                </a>
                <div class="flex-1 min-w-0">
                    <a href="/profile/{{ $person->username }}">
                        <p class="font-semibold text-sm text-gray-900">{{ $person->name }}</p>
                    </a>
                    @if($person->bio)
                    <p class="text-xs text-gray-400 line-clamp-1">{{ $person->bio }}</p>
                    @else
                    <p class="text-xs text-gray-400">@{{ $person->username }}</p>
                    @endif
                </div>
                <a href="/profile/{{ $person->username }}"
                   class="flex-shrink-0 px-4 py-1.5 bg-brand-50 text-brand-600 text-xs font-semibold rounded-full hover:bg-brand-100 transition">
                    View
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @else
    {{-- ── Search results state ── --}}

    {{-- Horizontal scroll filter tabs --}}
    <div class="flex gap-1.5 px-4 py-3 overflow-x-auto no-scrollbar border-b border-gray-100 bg-white sticky top-[61px] z-10">
        @php
            $tabs = [
                'all'       => ['label' => 'All',        'icon' => null],
                'posts'     => ['label' => 'लेखहरू',      'icon' => null],
                'pages'     => ['label' => 'Pages',       'icon' => null],
                'events'    => ['label' => 'Events',      'icon' => null],
                'recipes'   => ['label' => 'Recipes',     'icon' => null],
                'users'     => ['label' => 'People',      'icon' => null],
                'questions' => ['label' => 'Q&A',         'icon' => null],
            ];
        @endphp
        @foreach($tabs as $key => $tab)
        <a href="/search?q={{ urlencode($query) }}&type={{ $key }}"
            class="flex-shrink-0 flex items-center gap-1 px-3.5 py-1.5 rounded-full text-sm font-semibold border transition-all whitespace-nowrap
                {{ $type === $key
                    ? 'bg-brand-600 text-white border-brand-600 shadow-sm'
                    : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
            <span class="font-nepali">{{ $tab['label'] }}</span>
            @if(isset($counts[$key]) && $counts[$key] > 0)
            <span class="text-xs {{ $type === $key ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }} rounded-full px-1.5 leading-5 min-w-[20px] text-center">
                {{ $counts[$key] > 99 ? '99+' : $counts[$key] }}
            </span>
            @endif
        </a>
        @endforeach
    </div>

    @if($results)

    {{-- ALL tab: grouped sections --}}
    @if($type === 'all' && is_array($results))

        @php $anyResult = collect($results)->filter()->some(fn($g) => $g->isNotEmpty()); @endphp

        @if(!$anyResult)
        <div class="text-center py-20">
            <div class="text-5xl mb-4">🔍</div>
            <p class="text-gray-700 font-semibold font-nepali">"{{ $query }}" को कुनै नतिजा फेला परेन।</p>
            <p class="text-gray-400 text-sm mt-1">अर्को किवर्ड प्रयास गर्नुहोस्।</p>
        </div>
        @else

        {{-- Posts --}}
        @if($results['posts']->isNotEmpty())
        <div class="mt-4">
            <div class="flex items-center justify-between px-4 mb-2">
                <h2 class="font-bold text-gray-900 text-sm">📰 लेखहरू</h2>
                <a href="/search?q={{ urlencode($query) }}&type=posts" class="text-xs text-brand-600 font-semibold">See all →</a>
            </div>
            <div class="bg-white border-y border-gray-100 divide-y divide-gray-50">
                @foreach($results['posts'] as $post)
                @include('search._post', ['post' => $post, 'query' => $query])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Pages --}}
        @if($results['pages']->isNotEmpty())
        <div class="mt-4">
            <div class="flex items-center justify-between px-4 mb-2">
                <h2 class="font-bold text-gray-900 text-sm">🏢 Pages</h2>
                <a href="/search?q={{ urlencode($query) }}&type=pages" class="text-xs text-brand-600 font-semibold">See all →</a>
            </div>
            <div class="bg-white border-y border-gray-100 divide-y divide-gray-50">
                @foreach($results['pages'] as $page)
                @include('search._page', ['page' => $page, 'query' => $query])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Events --}}
        @if($results['events']->isNotEmpty())
        <div class="mt-4">
            <div class="flex items-center justify-between px-4 mb-2">
                <h2 class="font-bold text-gray-900 text-sm">📅 Events</h2>
                <a href="/search?q={{ urlencode($query) }}&type=events" class="text-xs text-brand-600 font-semibold">See all →</a>
            </div>
            <div class="bg-white border-y border-gray-100 divide-y divide-gray-50">
                @foreach($results['events'] as $event)
                @include('search._event', ['event' => $event, 'query' => $query])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Recipes --}}
        @if($results['recipes']->isNotEmpty())
        <div class="mt-4">
            <div class="flex items-center justify-between px-4 mb-2">
                <h2 class="font-bold text-gray-900 text-sm">🍽️ Recipes</h2>
                <a href="/search?q={{ urlencode($query) }}&type=recipes" class="text-xs text-brand-600 font-semibold">See all →</a>
            </div>
            <div class="bg-white border-y border-gray-100 divide-y divide-gray-50">
                @foreach($results['recipes'] as $recipe)
                @include('search._recipe', ['recipe' => $recipe, 'query' => $query])
                @endforeach
            </div>
        </div>
        @endif

        {{-- People --}}
        @if($results['users']->isNotEmpty())
        <div class="mt-4">
            <div class="flex items-center justify-between px-4 mb-2">
                <h2 class="font-bold text-gray-900 text-sm">👤 People</h2>
                <a href="/search?q={{ urlencode($query) }}&type=users" class="text-xs text-brand-600 font-semibold">See all →</a>
            </div>
            <div class="bg-white border-y border-gray-100 divide-y divide-gray-50">
                @foreach($results['users'] as $user)
                @include('search._user', ['user' => $user])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Q&A --}}
        @if($results['questions']->isNotEmpty())
        <div class="mt-4">
            <div class="flex items-center justify-between px-4 mb-2">
                <h2 class="font-bold text-gray-900 text-sm">❓ Q&A</h2>
                <a href="/search?q={{ urlencode($query) }}&type=questions" class="text-xs text-brand-600 font-semibold">See all →</a>
            </div>
            <div class="bg-white border-y border-gray-100 divide-y divide-gray-50">
                @foreach($results['questions'] as $question)
                @include('search._question', ['question' => $question, 'query' => $query])
                @endforeach
            </div>
        </div>
        @endif

        @endif {{-- anyResult --}}

    {{-- PAGINATED tabs --}}
    @else

    <div class="px-4 py-2.5 text-xs text-gray-500 bg-white border-b border-gray-100">
        "<span class="font-semibold text-gray-800">{{ $query }}</span>" को लागि
        <span class="font-semibold text-gray-800">{{ $results->total() }}</span> नतिजा
    </div>

    <div class="bg-white border-b border-gray-100 divide-y divide-gray-50">
        @if($type === 'posts')
            @forelse($results as $post)
                @include('search._post', ['post' => $post, 'query' => $query])
            @empty
                @include('search._empty', ['icon' => '📰', 'query' => $query])
            @endforelse

        @elseif($type === 'pages')
            @forelse($results as $page)
                @include('search._page', ['page' => $page, 'query' => $query])
            @empty
                @include('search._empty', ['icon' => '🏢', 'query' => $query])
            @endforelse

        @elseif($type === 'events')
            @forelse($results as $event)
                @include('search._event', ['event' => $event, 'query' => $query])
            @empty
                @include('search._empty', ['icon' => '📅', 'query' => $query])
            @endforelse

        @elseif($type === 'recipes')
            @forelse($results as $recipe)
                @include('search._recipe', ['recipe' => $recipe, 'query' => $query])
            @empty
                @include('search._empty', ['icon' => '🍽️', 'query' => $query])
            @endforelse

        @elseif($type === 'users')
            @forelse($results as $user)
                @include('search._user', ['user' => $user])
            @empty
                @include('search._empty', ['icon' => '👤', 'query' => $query])
            @endforelse

        @elseif($type === 'questions')
            @forelse($results as $question)
                @include('search._question', ['question' => $question, 'query' => $query])
            @empty
                @include('search._empty', ['icon' => '❓', 'query' => $query])
            @endforelse
        @endif
    </div>

    @if(method_exists($results, 'hasPages') && $results->hasPages())
    <div class="flex justify-center py-8">{{ $results->links() }}</div>
    @endif

    @endif {{-- end all vs paginated --}}

    @else
    <div class="text-center py-20">
        <div class="text-5xl mb-4">🔍</div>
        <p class="text-gray-500 font-nepali">केहि फेला परेन।</p>
    </div>
    @endif

    @endif {{-- end if $query --}}

    <div class="h-6"></div>
</div>

@push('styles')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush
@endsection
