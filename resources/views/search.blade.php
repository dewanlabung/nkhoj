@extends('layouts.app')
@section('title', $query ? 'खोज: ' . $query . ' — nkhoj' : 'खोज — nkhoj')

@section('content')

{{-- ── Sticky search bar (full-width on all screens) ── --}}
<div class="sticky top-0 z-20 bg-gray-50 dark:bg-gray-900 pt-3 pb-2.5 px-4 border-b border-gray-100 dark:border-gray-800">
    <form action="/search" method="GET" id="search-form" class="max-w-6xl mx-auto">
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="flex items-center gap-2.5">
            <div class="flex-1 flex items-center bg-white dark:bg-gray-800 rounded-full border border-gray-200 dark:border-gray-700 shadow-sm px-3.5 py-2.5 gap-2 focus-within:border-brand-400 focus-within:ring-2 focus-within:ring-brand-100 transition">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="q" id="search-q" value="{{ $query }}" autofocus
                    placeholder="समाचार, पेजहरू, घटनाहरू खोज्नुहोस्..."
                    class="flex-1 text-sm bg-transparent focus:outline-none text-gray-900 dark:text-gray-100 placeholder-gray-400">
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

{{-- ── Main layout container ── --}}
<div class="max-w-6xl mx-auto lg:flex lg:gap-6 lg:px-4 lg:pt-5 lg:pb-8">

    {{-- ══════════════════════════════════════════════════
         LEFT SIDEBAR — desktop only
    ══════════════════════════════════════════════════ --}}
    <aside class="hidden lg:block lg:w-72 xl:w-80 flex-shrink-0">
        <div class="sticky top-[73px]">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="font-bold text-gray-900 dark:text-gray-100 text-base">खोज नतिजा</h2>
                    @if($query)
                    <p class="text-xs text-gray-400 mt-0.5">"{{ Str::limit($query, 30) }}" को लागि</p>
                    @else
                    <p class="text-xs text-gray-400 mt-0.5">कोटि छान्नुहोस्</p>
                    @endif
                </div>

                @php
                $sidebarTabs = [
                    'all'       => ['label' => 'सबै नतिजा',  'icon' => '🔍', 'color' => 'brand'],
                    'posts'     => ['label' => 'लेखहरू',      'icon' => '📰', 'color' => 'blue'],
                    'events'    => ['label' => 'Events',      'icon' => '📅', 'color' => 'orange'],
                    'pages'     => ['label' => 'Pages',       'icon' => '🏢', 'color' => 'purple'],
                    'questions' => ['label' => 'Q&A',         'icon' => '❓', 'color' => 'yellow'],
                    'recipes'   => ['label' => 'Recipes',     'icon' => '🍽️', 'color' => 'green'],
                    'users'     => ['label' => 'Profiles',    'icon' => '👤', 'color' => 'pink'],
                ];
                @endphp

                <nav class="py-2">
                    @foreach($sidebarTabs as $key => $tab)
                    @php $isActive = $type === $key; $count = $counts[$key] ?? 0; @endphp
                    <a href="/search?q={{ urlencode($query) }}&type={{ $key }}"
                       class="flex items-center gap-3 px-4 py-2.5 transition group
                           {{ $isActive
                               ? 'bg-brand-50 dark:bg-brand-900/20 text-brand-700 dark:text-brand-400'
                               : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                        <span class="w-8 h-8 rounded-xl flex items-center justify-center text-base flex-shrink-0
                            {{ $isActive ? 'bg-brand-100 dark:bg-brand-800' : 'bg-gray-100 dark:bg-gray-700' }}">
                            {{ $tab['icon'] }}
                        </span>
                        <span class="flex-1 text-sm font-{{ $isActive ? 'bold' : 'medium' }} font-nepali">{{ $tab['label'] }}</span>
                        @if($query && $count > 0)
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                            {{ $isActive
                                ? 'bg-brand-100 dark:bg-brand-800 text-brand-700 dark:text-brand-300'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                            {{ $count > 999 ? '999+' : $count }}
                        </span>
                        @endif
                    </a>
                    @endforeach
                </nav>

                @if($query && ($counts['all'] ?? 0) > 0)
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-400">जम्मा <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $counts['all'] }}</span> नतिजा फेला पर्‍यो</p>
                </div>
                @endif
            </div>

            {{-- Trending on sidebar (desktop) --}}
            @if($trending->isNotEmpty())
            <div class="mt-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm">🔥 ट्रेन्डिङ खोजहरू</h3>
                </div>
                <div class="py-2">
                    @foreach($trending as $trend)
                    <a href="/search?q={{ urlencode($trend) }}"
                       class="flex items-center gap-2.5 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $trend }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════════
         MAIN CONTENT AREA
    ══════════════════════════════════════════════════ --}}
    <div class="flex-1 min-w-0">

        @if(!$query)
        {{-- ── Empty state ── --}}

        {{-- Mobile: recent searches --}}
        @if($recent->isNotEmpty())
        <div class="mt-4 lg:mt-0">
            <div class="flex items-center justify-between px-4 lg:px-0 mb-1">
                <p class="font-bold text-gray-900 dark:text-gray-100 text-base">Recent</p>
            </div>
            <div class="bg-white dark:bg-gray-800 border-y lg:border lg:rounded-2xl border-gray-100 dark:border-gray-700 overflow-hidden">
                @foreach($recent as $term)
                <a href="/search?q={{ urlencode($term) }}"
                   class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition border-b border-gray-50 dark:border-gray-700 last:border-0">
                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="flex-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $term }}</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7M17 7v10"/>
                    </svg>
                </a>
                @endforeach
            </div>
        </div>

        @else
        {{-- Trending (mobile, no history) --}}
        <div class="mt-4 lg:mt-0 px-4 lg:px-0">
            <p class="font-bold text-gray-900 dark:text-gray-100 text-base mb-3">🔥 ट्रेन्डिङ खोजहरू</p>
            <div class="flex flex-wrap gap-2">
                @foreach($trending as $trend)
                <a href="/search?q={{ urlencode($trend) }}"
                   class="flex items-center gap-1.5 px-3.5 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full text-sm text-gray-700 dark:text-gray-300 font-medium hover:border-brand-400 hover:text-brand-600 transition shadow-sm">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    {{ $trend }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Suggested people (both mobile and desktop empty state) --}}
        @if($suggested->isNotEmpty())
        <div class="mt-5">
            <p class="font-bold text-gray-900 dark:text-gray-100 text-base px-4 lg:px-0 mb-2">People you may know</p>
            <div class="bg-white dark:bg-gray-800 border-y lg:border lg:rounded-2xl border-gray-100 dark:border-gray-700 overflow-hidden
                        lg:grid lg:grid-cols-2 lg:divide-y-0 lg:divide-x-0 lg:gap-0">
                @foreach($suggested as $person)
                <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 dark:border-gray-700 last:border-0 lg:border-b lg:border-gray-100 dark:lg:border-gray-700">
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
                            <p class="font-semibold text-sm text-gray-900 dark:text-gray-100">{{ $person->name }}</p>
                        </a>
                        @if($person->bio)
                        <p class="text-xs text-gray-400 line-clamp-1">{{ $person->bio }}</p>
                        @else
                        <p class="text-xs text-gray-400">{{ '@' . $person->username }}</p>
                        @endif
                    </div>
                    <a href="/profile/{{ $person->username }}"
                       class="flex-shrink-0 px-4 py-1.5 bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 text-xs font-semibold rounded-full hover:bg-brand-100 transition">
                        View
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @else
        {{-- ── Search results state ── --}}

        {{-- Mobile-only horizontal scroll filter tabs (hidden on desktop where sidebar handles it) --}}
        <div class="flex gap-1.5 px-4 py-3 overflow-x-auto no-scrollbar border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 sticky top-[61px] z-10 lg:hidden">
            @php
            $mobileTabs = [
                'all'       => 'All',
                'posts'     => 'लेखहरू',
                'events'    => 'Events',
                'pages'     => 'Pages',
                'questions' => 'Q&A',
                'recipes'   => 'Recipes',
                'users'     => 'Profiles',
            ];
            @endphp
            @foreach($mobileTabs as $key => $label)
            <a href="/search?q={{ urlencode($query) }}&type={{ $key }}"
                class="flex-shrink-0 flex items-center gap-1 px-3.5 py-1.5 rounded-full text-sm font-semibold border transition-all whitespace-nowrap
                    {{ $type === $key
                        ? 'bg-brand-600 text-white border-brand-600 shadow-sm'
                        : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-gray-300 hover:bg-gray-50' }}">
                <span class="font-nepali">{{ $label }}</span>
                @if(isset($counts[$key]) && $counts[$key] > 0)
                <span class="text-xs {{ $type === $key ? 'bg-white/25 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500' }} rounded-full px-1.5 leading-5 min-w-[20px] text-center">
                    {{ $counts[$key] > 99 ? '99+' : $counts[$key] }}
                </span>
                @endif
            </a>
            @endforeach
        </div>

        @if($results)

        {{-- Desktop: result count header --}}
        @if($query)
        <div class="hidden lg:flex items-center justify-between mb-4">
            <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                @php $tabLabels = ['all'=>'সबै नतिजा','posts'=>'लेखहरू','events'=>'Events','pages'=>'Pages','questions'=>'Q&A','recipes'=>'Recipes','users'=>'Profiles']; @endphp
                {{ $tabLabels[$type] ?? 'नतिजा' }}
            </h1>
            @if(isset($counts[$type]) && $counts[$type] > 0)
            <span class="text-sm text-gray-400">"{{ $query }}" को लागि {{ $counts[$type] }} नतिजा</span>
            @endif
        </div>
        @endif

        {{-- Mobile count line --}}
        <div class="px-4 py-2.5 text-xs text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 lg:hidden">
            "<span class="font-semibold text-gray-800 dark:text-gray-200">{{ $query }}</span>" को लागि
            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $counts[$type] ?? 0 }}</span> नतिजा
        </div>

        {{-- ALL tab: grouped sections --}}
        @if($type === 'all' && is_array($results))

            @php $anyResult = collect($results)->filter()->some(fn($g) => $g->isNotEmpty()); @endphp

            @if(!$anyResult)
            <div class="text-center py-20">
                <div class="text-5xl mb-4">🔍</div>
                <p class="text-gray-700 dark:text-gray-300 font-semibold font-nepali">"{{ $query }}" को कुनै नतिजा फेला परेन।</p>
                <p class="text-gray-400 text-sm mt-1">अर्को किवर्ड प्रयास गर्नुहोस्।</p>
            </div>
            @else

            {{-- Posts --}}
            @if($results['posts']->isNotEmpty())
            <div class="mt-4 lg:mt-0">
                <div class="flex items-center justify-between px-4 lg:px-0 mb-2">
                    <h2 class="font-bold text-gray-900 dark:text-gray-100 text-sm">📰 लेखहरू</h2>
                    @if(($counts['posts'] ?? 0) > 4)
                    <a href="/search?q={{ urlencode($query) }}&type=posts" class="text-xs text-brand-600 font-semibold">
                        सबै {{ $counts['posts'] }} हेर्नुस् →
                    </a>
                    @endif
                </div>
                <div class="bg-white dark:bg-gray-800 border-y lg:border lg:rounded-2xl border-gray-100 dark:border-gray-700 divide-y divide-gray-50 dark:divide-gray-700
                            lg:divide-y-0 lg:grid lg:grid-cols-2 lg:gap-0 lg:overflow-hidden">
                    @foreach($results['posts'] as $post)
                    <div class="lg:border-b lg:border-r lg:border-gray-100 dark:lg:border-gray-700 [&:nth-child(even)]:lg:border-r-0">
                        @include('search._post', ['post' => $post, 'query' => $query])
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Events --}}
            @if($results['events']->isNotEmpty())
            <div class="mt-4">
                <div class="flex items-center justify-between px-4 lg:px-0 mb-2">
                    <h2 class="font-bold text-gray-900 dark:text-gray-100 text-sm">📅 Events</h2>
                    @if(($counts['events'] ?? 0) > 4)
                    <a href="/search?q={{ urlencode($query) }}&type=events" class="text-xs text-brand-600 font-semibold">सबै {{ $counts['events'] }} हेर्नुस् →</a>
                    @endif
                </div>
                <div class="bg-white dark:bg-gray-800 border-y lg:border lg:rounded-2xl border-gray-100 dark:border-gray-700 divide-y divide-gray-50 dark:divide-gray-700
                            lg:divide-y-0 lg:grid lg:grid-cols-2 lg:gap-0 lg:overflow-hidden">
                    @foreach($results['events'] as $event)
                    <div class="lg:border-b lg:border-r lg:border-gray-100 dark:lg:border-gray-700 [&:nth-child(even)]:lg:border-r-0">
                        @include('search._event', ['event' => $event, 'query' => $query])
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Pages --}}
            @if($results['pages']->isNotEmpty())
            <div class="mt-4">
                <div class="flex items-center justify-between px-4 lg:px-0 mb-2">
                    <h2 class="font-bold text-gray-900 dark:text-gray-100 text-sm">🏢 Pages</h2>
                    @if(($counts['pages'] ?? 0) > 4)
                    <a href="/search?q={{ urlencode($query) }}&type=pages" class="text-xs text-brand-600 font-semibold">सबै {{ $counts['pages'] }} हेर्नुस् →</a>
                    @endif
                </div>
                <div class="bg-white dark:bg-gray-800 border-y lg:border lg:rounded-2xl border-gray-100 dark:border-gray-700 divide-y divide-gray-50 dark:divide-gray-700
                            lg:divide-y-0 lg:grid lg:grid-cols-2 lg:gap-0 lg:overflow-hidden">
                    @foreach($results['pages'] as $page)
                    <div class="lg:border-b lg:border-r lg:border-gray-100 dark:lg:border-gray-700 [&:nth-child(even)]:lg:border-r-0">
                        @include('search._page', ['page' => $page, 'query' => $query])
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Q&A --}}
            @if($results['questions']->isNotEmpty())
            <div class="mt-4">
                <div class="flex items-center justify-between px-4 lg:px-0 mb-2">
                    <h2 class="font-bold text-gray-900 dark:text-gray-100 text-sm">❓ Q&A</h2>
                    @if(($counts['questions'] ?? 0) > 4)
                    <a href="/search?q={{ urlencode($query) }}&type=questions" class="text-xs text-brand-600 font-semibold">सबै {{ $counts['questions'] }} हेर्नुस् →</a>
                    @endif
                </div>
                <div class="bg-white dark:bg-gray-800 border-y lg:border lg:rounded-2xl border-gray-100 dark:border-gray-700 divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach($results['questions'] as $question)
                    @include('search._question', ['question' => $question, 'query' => $query])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Recipes --}}
            @if($results['recipes']->isNotEmpty())
            <div class="mt-4">
                <div class="flex items-center justify-between px-4 lg:px-0 mb-2">
                    <h2 class="font-bold text-gray-900 dark:text-gray-100 text-sm">🍽️ Recipes</h2>
                    @if(($counts['recipes'] ?? 0) > 4)
                    <a href="/search?q={{ urlencode($query) }}&type=recipes" class="text-xs text-brand-600 font-semibold">सबै {{ $counts['recipes'] }} हेर्नुस् →</a>
                    @endif
                </div>
                <div class="bg-white dark:bg-gray-800 border-y lg:border lg:rounded-2xl border-gray-100 dark:border-gray-700 divide-y divide-gray-50 dark:divide-gray-700
                            lg:divide-y-0 lg:grid lg:grid-cols-2 lg:gap-0 lg:overflow-hidden">
                    @foreach($results['recipes'] as $recipe)
                    <div class="lg:border-b lg:border-r lg:border-gray-100 dark:lg:border-gray-700 [&:nth-child(even)]:lg:border-r-0">
                        @include('search._recipe', ['recipe' => $recipe, 'query' => $query])
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Profiles --}}
            @if($results['users']->isNotEmpty())
            <div class="mt-4">
                <div class="flex items-center justify-between px-4 lg:px-0 mb-2">
                    <h2 class="font-bold text-gray-900 dark:text-gray-100 text-sm">👤 Profiles</h2>
                    @if(($counts['users'] ?? 0) > 4)
                    <a href="/search?q={{ urlencode($query) }}&type=users" class="text-xs text-brand-600 font-semibold">सबै {{ $counts['users'] }} हेर्नुस् →</a>
                    @endif
                </div>
                <div class="bg-white dark:bg-gray-800 border-y lg:border lg:rounded-2xl border-gray-100 dark:border-gray-700 divide-y divide-gray-50 dark:divide-gray-700
                            lg:divide-y-0 lg:grid lg:grid-cols-2 lg:gap-0 lg:overflow-hidden">
                    @foreach($results['users'] as $user)
                    <div class="lg:border-b lg:border-r lg:border-gray-100 dark:lg:border-gray-700 [&:nth-child(even)]:lg:border-r-0">
                        @include('search._user', ['user' => $user])
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @endif {{-- anyResult --}}

        {{-- PAGINATED single-type tabs --}}
        @else

        <div class="bg-white dark:bg-gray-800 border-y lg:border lg:rounded-2xl border-gray-100 dark:border-gray-700
                    {{ in_array($type, ['posts','events','pages','recipes','users']) ? 'lg:divide-y-0' : 'divide-y divide-gray-50 dark:divide-gray-700' }}
                    overflow-hidden">

            @if(in_array($type, ['posts', 'events', 'pages', 'recipes', 'users']))
            {{-- Grid layout for visual content types --}}
            <div class="{{ in_array($type, ['posts','events','pages','recipes','users']) ? 'lg:grid lg:grid-cols-2' : '' }} divide-y divide-gray-50 dark:divide-gray-700 lg:divide-y-0">
            @endif

                @if($type === 'posts')
                    @forelse($results as $post)
                    <div class="lg:border-b lg:border-r lg:border-gray-100 dark:lg:border-gray-700 [&:nth-child(even)]:lg:border-r-0 border-b border-gray-50 dark:border-gray-700">
                        @include('search._post', ['post' => $post, 'query' => $query])
                    </div>
                    @empty
                        @include('search._empty', ['icon' => '📰', 'query' => $query])
                    @endforelse

                @elseif($type === 'events')
                    @forelse($results as $event)
                    <div class="lg:border-b lg:border-r lg:border-gray-100 dark:lg:border-gray-700 [&:nth-child(even)]:lg:border-r-0 border-b border-gray-50 dark:border-gray-700">
                        @include('search._event', ['event' => $event, 'query' => $query])
                    </div>
                    @empty
                        @include('search._empty', ['icon' => '📅', 'query' => $query])
                    @endforelse

                @elseif($type === 'pages')
                    @forelse($results as $page)
                    <div class="lg:border-b lg:border-r lg:border-gray-100 dark:lg:border-gray-700 [&:nth-child(even)]:lg:border-r-0 border-b border-gray-50 dark:border-gray-700">
                        @include('search._page', ['page' => $page, 'query' => $query])
                    </div>
                    @empty
                        @include('search._empty', ['icon' => '🏢', 'query' => $query])
                    @endforelse

                @elseif($type === 'recipes')
                    @forelse($results as $recipe)
                    <div class="lg:border-b lg:border-r lg:border-gray-100 dark:lg:border-gray-700 [&:nth-child(even)]:lg:border-r-0 border-b border-gray-50 dark:border-gray-700">
                        @include('search._recipe', ['recipe' => $recipe, 'query' => $query])
                    </div>
                    @empty
                        @include('search._empty', ['icon' => '🍽️', 'query' => $query])
                    @endforelse

                @elseif($type === 'users')
                    @forelse($results as $user)
                    <div class="lg:border-b lg:border-r lg:border-gray-100 dark:lg:border-gray-700 [&:nth-child(even)]:lg:border-r-0 border-b border-gray-50 dark:border-gray-700">
                        @include('search._user', ['user' => $user])
                    </div>
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

            @if(in_array($type, ['posts', 'events', 'pages', 'recipes', 'users']))
            </div>
            @endif
        </div>

        @if(method_exists($results, 'hasPages') && $results->hasPages())
        <div class="flex justify-center py-8 px-4 lg:px-0">{{ $results->links() }}</div>
        @endif

        @endif {{-- end all vs paginated --}}

        @else
        <div class="text-center py-20">
            <div class="text-5xl mb-4">🔍</div>
            <p class="text-gray-500 dark:text-gray-400 font-nepali">केहि फेला परेन।</p>
        </div>
        @endif

        @endif {{-- end if $query --}}

        <div class="h-6"></div>
    </div>{{-- end main --}}

</div>{{-- end flex container --}}

@push('styles')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush
@endsection
