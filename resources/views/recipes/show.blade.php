@extends('layouts.app')
@section('title', $recipe->title . ' – Nkhoj Recipes')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-6">
        <a href="/recipe" class="hover:text-orange-500">Recipes</a>
        @if($recipe->cuisine_type)
        <span>/</span>
        <a href="/recipe?cuisine={{ urlencode($recipe->cuisine_type) }}" class="hover:text-orange-500">{{ $recipe->cuisine_type }}</a>
        @endif
        <span>/</span>
        <span class="text-gray-700 dark:text-gray-300 truncate">{{ $recipe->title }}</span>
    </nav>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden mb-6">
        {{-- Thumbnail --}}
        @if($recipe->thumbnail_url)
        <div class="relative aspect-video overflow-hidden">
            <img src="{{ $recipe->thumbnail_url }}" class="w-full h-full object-cover" alt="{{ $recipe->title }}">
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        </div>
        @endif

        <div class="p-6 md:p-8">
            {{-- Tags row --}}
            <div class="flex flex-wrap gap-2 mb-4">
                @if($recipe->difficulty)
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $recipe->difficulty_color }}">{{ ucfirst($recipe->difficulty) }}</span>
                @endif
                @if($recipe->cuisine_type)
                    <a href="/recipe?cuisine={{ urlencode($recipe->cuisine_type) }}" class="px-3 py-1 rounded-full text-xs font-semibold bg-orange-50 text-orange-600 hover:bg-orange-100 transition-colors">{{ $recipe->cuisine_type }}</a>
                @endif
                @if($recipe->meal_type)
                    <a href="/recipe?meal={{ urlencode($recipe->meal_type) }}" class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">{{ $recipe->meal_type }}</a>
                @endif
            </div>

            <h1 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white mb-4">{{ $recipe->title }}</h1>

            {{-- Author + meta --}}
            <div class="flex items-center justify-between flex-wrap gap-4 mb-6 pb-6 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    @if($recipe->author->avatar_url)
                        <img src="{{ $recipe->author->avatar_url }}" class="w-10 h-10 rounded-full object-cover">
                    @else
                        <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold">{{ strtoupper(substr($recipe->author->name, 0, 1)) }}</div>
                    @endif
                    <div>
                        <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ $recipe->author->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $recipe->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>{{ number_format($recipe->views_count) }}</span>
                    @auth
                    <button id="like-btn" onclick="toggleLike()" class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-semibold transition-all {{ $isLiked ? 'bg-red-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-red-50 hover:text-red-500' }}">
                        <svg class="w-4 h-4" fill="{{ $isLiked ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <span id="like-count">{{ number_format($recipe->likes_count) }}</span>
                    </button>
                    @else
                    <span class="flex items-center gap-1"><svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>{{ number_format($recipe->likes_count) }}</span>
                    @endauth
                </div>
            </div>

            @if($recipe->description)
            <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed mb-6">{{ $recipe->description }}</p>
            @endif

            {{-- Time + servings cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
                @if($recipe->prep_time)
                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-3 text-center">
                    <p class="text-xs font-semibold text-orange-600 dark:text-orange-400 mb-1">Prep Time</p>
                    <p class="font-black text-gray-900 dark:text-white text-lg">{{ $recipe->prep_time }}<span class="text-xs font-medium text-gray-500">m</span></p>
                </div>
                @endif
                @if($recipe->cook_time)
                <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-3 text-center">
                    <p class="text-xs font-semibold text-red-600 dark:text-red-400 mb-1">Cook Time</p>
                    <p class="font-black text-gray-900 dark:text-white text-lg">{{ $recipe->cook_time }}<span class="text-xs font-medium text-gray-500">m</span></p>
                </div>
                @endif
                @if($recipe->total_time > 0)
                <div class="bg-pink-50 dark:bg-pink-900/20 rounded-xl p-3 text-center">
                    <p class="text-xs font-semibold text-pink-600 dark:text-pink-400 mb-1">Total Time</p>
                    <p class="font-black text-gray-900 dark:text-white text-lg">{{ $recipe->total_time }}<span class="text-xs font-medium text-gray-500">m</span></p>
                </div>
                @endif
                @if($recipe->servings)
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-3 text-center">
                    <p class="text-xs font-semibold text-purple-600 dark:text-purple-400 mb-1">Servings</p>
                    <p class="font-black text-gray-900 dark:text-white text-lg">{{ $recipe->servings }}</p>
                </div>
                @endif
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                {{-- Ingredients --}}
                @if($recipe->ingredients && count($recipe->ingredients))
                <div>
                    <h2 class="font-black text-gray-900 dark:text-white text-lg mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-orange-500 flex items-center justify-center text-white text-sm">🧂</span>
                        Ingredients
                    </h2>
                    <ul class="space-y-2">
                        @foreach($recipe->ingredients as $ing)
                        @if(trim($ing))
                        <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <span class="w-5 h-5 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                            </span>
                            {{ $ing }}
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Steps --}}
                @if($recipe->steps && count($recipe->steps))
                <div>
                    <h2 class="font-black text-gray-900 dark:text-white text-lg mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-red-500 flex items-center justify-center text-white text-sm">👨‍🍳</span>
                        Instructions
                    </h2>
                    <ol class="space-y-4">
                        @foreach($recipe->steps as $i => $step)
                        @if(trim($step))
                        <li class="flex gap-3">
                            <span class="w-6 h-6 rounded-full bg-gradient-to-br from-orange-500 to-red-500 text-white text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">{{ $i + 1 }}</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $step }}</p>
                        </li>
                        @endif
                        @endforeach
                    </ol>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Related recipes --}}
    @if($related->count())
    <div class="mb-8">
        <h2 class="font-bold text-gray-900 dark:text-white text-lg mb-4">You might also like</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($related as $r)
            <a href="/recipe/{{ $r->slug }}" class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition-all">
                <div class="relative aspect-[4/3] bg-gray-100 dark:bg-gray-700 overflow-hidden">
                    @if($r->thumbnail_url)
                        <img src="{{ $r->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="{{ $r->title }}">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-4xl">🍽️</div>
                    @endif
                </div>
                <div class="p-3">
                    <h3 class="font-bold text-gray-900 dark:text-white text-sm line-clamp-2 group-hover:text-orange-500 transition-colors">{{ $r->title }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $r->total_time > 0 ? $r->total_time.'m' : ucfirst($r->difficulty) }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>

@auth
<script>
const recipeId = {{ $recipe->id }};
async function toggleLike() {
    const btn = document.getElementById('like-btn');
    btn.disabled = true;
    try {
        const res = await fetch(`/recipe/${recipeId}/like`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        const data = await res.json();
        document.getElementById('like-count').textContent = data.count.toLocaleString();
        if (data.liked) {
            btn.className = 'flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-semibold transition-all bg-red-500 text-white';
            btn.querySelector('svg').setAttribute('fill', 'currentColor');
        } else {
            btn.className = 'flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-semibold transition-all bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-red-50 hover:text-red-500';
            btn.querySelector('svg').setAttribute('fill', 'none');
        }
    } finally { btn.disabled = false; }
}
</script>
@endauth
@endsection
