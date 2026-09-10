@extends('layouts.app')
@section('title', $query ? 'खोज: ' . $query . ' — nkhoj' : 'खोज — nkhoj')

@section('content')
<div class="max-w-3xl mx-auto px-4">

    {{-- Hero search bar --}}
    <div class="bg-gradient-to-br from-brand-500 to-indigo-700 rounded-2xl p-6 mb-5 text-center">
        <h1 class="text-xl font-bold text-white mb-4">खोज्नुहोस्</h1>
        <form action="/search" method="GET">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="flex gap-2 bg-white rounded-xl overflow-hidden p-1 shadow-lg">
                <span class="flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" name="q" value="{{ $query }}" autofocus
                    placeholder="समाचार, पेजहरू, घटनाहरू खोज्नुहोस्..."
                    class="flex-1 py-3 px-2 text-base focus:outline-none bg-transparent">
                <button type="submit"
                    class="px-5 py-2.5 bg-brand-500 text-white text-sm font-semibold rounded-lg hover:bg-brand-600 transition-colors">
                    Search
                </button>
            </div>
        </form>
    </div>

    @if(!$query && $trending->isNotEmpty())
    {{-- Trending searches (shown when search box is empty) --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">🔥 ट्रेन्डिङ खोजहरू</p>
        <div class="flex flex-wrap gap-2">
            @foreach($trending as $trend)
            <a href="/search?q={{ urlencode($trend) }}"
               class="px-3 py-1.5 bg-gray-50 hover:bg-brand-50 hover:text-brand-700 border border-gray-200 hover:border-brand-300 rounded-full text-sm text-gray-700 font-semibold transition">
                🔍 {{ $trend }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($query)

    {{-- Type filter tabs --}}
    <div class="flex gap-2 flex-wrap mb-5 overflow-x-auto pb-1">
        @php
            $tabs = [
                'all'       => ['label' => 'सबै',           'icon' => '🔍'],
                'posts'     => ['label' => 'लेखहरू',         'icon' => '📰'],
                'pages'     => ['label' => 'पेजहरू',         'icon' => '🏢'],
                'events'    => ['label' => 'घटनाहरू',        'icon' => '📅'],
                'recipes'   => ['label' => 'रेसिपीहरू',      'icon' => '🍽️'],
                'users'     => ['label' => 'प्रयोगकर्ता',   'icon' => '👤'],
                'questions' => ['label' => 'प्रश्नहरू',      'icon' => '❓'],
            ];
        @endphp
        @foreach($tabs as $key => $tab)
        <a href="/search?q={{ urlencode($query) }}&type={{ $key }}"
            class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold border transition-all
                {{ $type === $key
                    ? 'bg-brand-500 text-white border-brand-500 shadow-sm'
                    : 'bg-white text-gray-600 border-gray-200 hover:border-brand-300 hover:text-brand-600' }}">
            <span>{{ $tab['icon'] }}</span>
            <span class="font-nepali">{{ $tab['label'] }}</span>
            @if(isset($counts[$key]) && $counts[$key] > 0)
            <span class="text-xs {{ $type === $key ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }} rounded-full px-1.5 py-0.5 ml-0.5">
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
        <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
            <div class="text-4xl mb-4">🔍</div>
            <p class="text-gray-600 font-nepali font-semibold">"{{ $query }}" को कुनै नतिजा फेला परेन।</p>
            <p class="text-gray-400 text-sm mt-1">अर्को किवर्ड प्रयास गर्नुहोस्।</p>
        </div>
        @else

        {{-- Posts section --}}
        @if($results['posts']->isNotEmpty())
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-gray-800 text-sm">📰 लेखहरू</h2>
                <a href="/search?q={{ urlencode($query) }}&type=posts" class="text-xs text-brand-600 hover:underline">सबै हेर्नुहोस् →</a>
            </div>
            <div class="space-y-3">
                @foreach($results['posts'] as $post)
                @include('search._post', ['post' => $post, 'query' => $query])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Pages section --}}
        @if($results['pages']->isNotEmpty())
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-gray-800 text-sm">🏢 पेजहरू</h2>
                <a href="/search?q={{ urlencode($query) }}&type=pages" class="text-xs text-brand-600 hover:underline">सबै हेर्नुहोस् →</a>
            </div>
            <div class="space-y-3">
                @foreach($results['pages'] as $page)
                @include('search._page', ['page' => $page, 'query' => $query])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Events section --}}
        @if($results['events']->isNotEmpty())
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-gray-800 text-sm">📅 घटनाहरू</h2>
                <a href="/search?q={{ urlencode($query) }}&type=events" class="text-xs text-brand-600 hover:underline">सबै हेर्नुहोस् →</a>
            </div>
            <div class="space-y-3">
                @foreach($results['events'] as $event)
                @include('search._event', ['event' => $event, 'query' => $query])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Users section --}}
        @if($results['users']->isNotEmpty())
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-gray-800 text-sm">👤 प्रयोगकर्ता</h2>
                <a href="/search?q={{ urlencode($query) }}&type=users" class="text-xs text-brand-600 hover:underline">सबै हेर्नुहोस् →</a>
            </div>
            <div class="space-y-3">
                @foreach($results['users'] as $user)
                @include('search._user', ['user' => $user])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Recipes section --}}
        @if($results['recipes']->isNotEmpty())
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-gray-800 text-sm">🍽️ रेसिपीहरू</h2>
                <a href="/search?q={{ urlencode($query) }}&type=recipes" class="text-xs text-brand-600 hover:underline">सबै हेर्नुहोस् →</a>
            </div>
            <div class="space-y-3">
                @foreach($results['recipes'] as $recipe)
                @include('search._recipe', ['recipe' => $recipe, 'query' => $query])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Questions section --}}
        @if($results['questions']->isNotEmpty())
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-gray-800 text-sm">❓ प्रश्नहरू</h2>
                <a href="/search?q={{ urlencode($query) }}&type=questions" class="text-xs text-brand-600 hover:underline">सबै हेर्नुहोस् →</a>
            </div>
            <div class="space-y-3">
                @foreach($results['questions'] as $question)
                @include('search._question', ['question' => $question, 'query' => $query])
                @endforeach
            </div>
        </div>
        @endif

        @endif {{-- anyResult --}}

    {{-- PAGINATED tabs --}}
    @else

    <p class="text-sm text-gray-500 mb-4">
        "<span class="font-semibold text-gray-800">{{ $query }}</span>" को लागि
        <span class="font-semibold text-gray-800">{{ $results->total() }}</span> नतिजा
    </p>

    <div class="space-y-3">
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

        @elseif($type === 'users')
            @forelse($results as $user)
                @include('search._user', ['user' => $user])
            @empty
                @include('search._empty', ['icon' => '👤', 'query' => $query])
            @endforelse

        @elseif($type === 'recipes')
            @forelse($results as $recipe)
                @include('search._recipe', ['recipe' => $recipe, 'query' => $query])
            @empty
                @include('search._empty', ['icon' => '🍽️', 'query' => $query])
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
    <div class="flex justify-center mt-8">{{ $results->links() }}</div>
    @endif

    @endif {{-- end all vs paginated --}}

    @else
    <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
        <div class="text-4xl mb-4">🔍</div>
        <p class="text-gray-500">केहि फेला परेन।</p>
    </div>
    @endif

    @endif {{-- end if $query --}}

</div>
@endsection
