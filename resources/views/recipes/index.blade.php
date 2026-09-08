@extends('layouts.app')
@section('title', 'Recipes – Nkhoj Community')

@section('content')
{{-- Hero --}}
<div class="relative rounded-2xl overflow-hidden mb-8" style="background:linear-gradient(135deg,#f97316 0%,#ef4444 50%,#ec4899 100%)">
    <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml,%3Csvg width=60 height=60 xmlns=http://www.w3.org/2000/svg%3E%3Cpath d=M30 5 L55 50 L5 50 Z fill=white/%3E%3C/svg%3E')]"></div>
    <div class="relative px-6 py-10 text-white">
        <p class="text-orange-200 text-sm font-semibold uppercase tracking-widest mb-2">Community Kitchen</p>
        <h1 class="text-3xl md:text-4xl font-black mb-3">Discover Recipes</h1>
        <p class="text-orange-100 text-sm mb-6 max-w-lg">Explore trending dishes, traditional cuisines, and share your own recipes with the community.</p>
        <form method="GET" action="/recipe" class="flex gap-2 max-w-xl">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search recipes, ingredients…"
                   class="flex-1 px-4 py-2.5 rounded-xl text-gray-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-white/50">
            <button class="px-5 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur rounded-xl text-sm font-bold transition-colors">Search</button>
        </form>
    </div>
</div>

<div class="flex gap-6">
    {{-- Left sidebar (desktop) --}}
    <aside class="hidden lg:block w-56 flex-shrink-0 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Cuisine Type</p>
            <div class="space-y-1">
                <a href="/recipe?tab={{ $tab }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ !$cuisine ? 'bg-orange-50 text-orange-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750' }} transition-colors">All Cuisines</a>
                @foreach($cuisines as $c)
                    <a href="/recipe?tab={{ $tab }}&cuisine={{ urlencode($c) }}{{ $meal ? '&meal='.urlencode($meal) : '' }}{{ $search ? '&q='.urlencode($search) : '' }}"
                       class="block px-3 py-2 rounded-lg text-sm font-medium {{ $cuisine === $c ? 'bg-orange-50 text-orange-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750' }} transition-colors">{{ $c }}</a>
                @endforeach
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Meal Type</p>
            <div class="space-y-1">
                <a href="/recipe?tab={{ $tab }}{{ $cuisine ? '&cuisine='.urlencode($cuisine) : '' }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ !$meal ? 'bg-orange-50 text-orange-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750' }} transition-colors">All Meals</a>
                @foreach($meals as $m)
                    <a href="/recipe?tab={{ $tab }}{{ $cuisine ? '&cuisine='.urlencode($cuisine) : '' }}&meal={{ urlencode($m) }}{{ $search ? '&q='.urlencode($search) : '' }}"
                       class="block px-3 py-2 rounded-lg text-sm font-medium {{ $meal === $m ? 'bg-orange-50 text-orange-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750' }} transition-colors">{{ $m }}</a>
                @endforeach
            </div>
        </div>
        @auth
        <a href="/recipe/create" class="block w-full text-center py-3 rounded-2xl font-bold text-white text-sm transition-all"
           style="background:linear-gradient(135deg,#f97316,#ef4444)">+ Share Recipe</a>
        @endauth
    </aside>

    <div class="flex-1 min-w-0">
        {{-- Tab bar --}}
        <div class="flex items-center gap-1 mb-5 bg-white dark:bg-gray-800 rounded-2xl p-1.5 shadow-sm">
            @foreach(['trending' => 'Trending', 'newest' => 'Newest', 'popular' => 'Most Viewed', 'favourite' => 'Top Rated'] as $key => $label)
                <a href="/recipe?tab={{ $key }}{{ $cuisine ? '&cuisine='.urlencode($cuisine) : '' }}{{ $meal ? '&meal='.urlencode($meal) : '' }}{{ $search ? '&q='.urlencode($search) : '' }}"
                   class="flex-1 text-center py-2 rounded-xl text-sm font-semibold transition-colors {{ $tab === $key ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
            @auth
            <a href="/recipe/create" class="ml-1 px-4 py-2 rounded-xl text-sm font-bold text-white hidden md:block"
               style="background:linear-gradient(135deg,#f97316,#ef4444)">+ Share</a>
            @endauth
        </div>

        {{-- Mobile cuisine scroll --}}
        <div class="lg:hidden flex gap-2 overflow-x-auto pb-3 mb-4 scrollbar-hide">
            <a href="/recipe?tab={{ $tab }}" class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold {{ !$cuisine ? 'bg-orange-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 shadow-sm' }} transition-colors">All</a>
            @foreach($cuisines as $c)
                <a href="/recipe?tab={{ $tab }}&cuisine={{ urlencode($c) }}" class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold {{ $cuisine === $c ? 'bg-orange-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 shadow-sm' }} transition-colors">{{ $c }}</a>
            @endforeach
        </div>

        {{-- Featured (only on default/no-filter page) --}}
        @if(!$search && !$cuisine && !$meal && $tab === 'trending' && $featured->count())
        <div class="mb-6">
            <h2 class="font-bold text-gray-900 dark:text-white mb-3">Featured Recipes</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($featured as $r)
                <a href="/recipe/{{ $r->slug }}" class="group relative rounded-2xl overflow-hidden shadow-sm bg-gray-100 dark:bg-gray-800 aspect-square">
                    @if($r->thumbnail_url)
                        <img src="{{ $r->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="{{ $r->title }}">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-4xl">🍽️</div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-3">
                        <p class="text-white text-xs font-bold leading-tight line-clamp-2">{{ $r->title }}</p>
                    </div>
                    <div class="absolute top-2 right-2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-0.5 rounded-full">Featured</div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Recipe grid --}}
        @if($recipes->count())
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($recipes as $recipe)
            <a href="/recipe/{{ $recipe->slug }}" class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition-all">
                <div class="relative aspect-[4/3] bg-gray-100 dark:bg-gray-700 overflow-hidden">
                    @if($recipe->thumbnail_url)
                        <img src="{{ $recipe->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="{{ $recipe->title }}">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-5xl">🍽️</div>
                    @endif
                    <div class="absolute top-2 left-2">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $recipe->difficulty_color }}">{{ ucfirst($recipe->difficulty) }}</span>
                    </div>
                    @if($recipe->cuisine_type)
                    <div class="absolute top-2 right-2 bg-black/50 text-white text-xs font-medium px-2 py-0.5 rounded-full backdrop-blur-sm">{{ $recipe->cuisine_type }}</div>
                    @endif
                </div>
                <div class="p-3">
                    <h3 class="font-bold text-gray-900 dark:text-white text-sm line-clamp-2 mb-1 group-hover:text-orange-500 transition-colors">{{ $recipe->title }}</h3>
                    <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        @if($recipe->total_time > 0)
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $recipe->total_time }}m
                            </span>
                        @endif
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                            {{ number_format($recipe->likes_count) }}
                        </span>
                        <span>{{ $recipe->views_count }} views</span>
                    </div>
                    <div class="flex items-center gap-1.5 mt-2">
                        @if($recipe->author->avatar_url)
                            <img src="{{ $recipe->author->avatar_url }}" class="w-5 h-5 rounded-full object-cover">
                        @else
                            <div class="w-5 h-5 rounded-full bg-orange-500 flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($recipe->author->name, 0, 1)) }}</div>
                        @endif
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $recipe->author->name }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($recipes->hasPages())
        <div class="mt-8">{{ $recipes->links() }}</div>
        @endif

        @else
        <div class="text-center py-20">
            <p class="text-5xl mb-4">🍽️</p>
            <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-2">No recipes found</h3>
            <p class="text-gray-500 text-sm mb-6">{{ $search ? "No recipes matching \"$search\"" : 'Be the first to share a recipe!' }}</p>
            @auth
            <a href="/recipe/create" class="inline-block px-6 py-3 rounded-xl text-white font-bold text-sm" style="background:linear-gradient(135deg,#f97316,#ef4444)">Share a Recipe</a>
            @endauth
        </div>
        @endif
    </div>
</div>
@endsection
