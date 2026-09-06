@extends('layouts.app')
@section('title', $query ? 'खोज: ' . $query . ' — nkhoj' : 'खोज — nkhoj')

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Hero search bar --}}
    <div class="bg-gradient-to-br from-brand-500 to-indigo-700 rounded-2xl p-8 mb-6 text-center">
        <h1 class="text-2xl font-bold text-white mb-1 font-nepali">खोज्नुहोस्</h1>
        <p class="text-white/70 text-sm mb-5">समाचार, प्रश्न र प्रयोगकर्ता खोज्नुहोस्</p>
        <form action="/search" method="GET">
            @if(request('type'))
            <input type="hidden" name="type" value="{{ request('type') }}">
            @endif
            <div class="flex gap-2 bg-white rounded-xl shadow-lg overflow-hidden p-1">
                <span class="flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" name="q" value="{{ $query }}" autofocus
                    placeholder="समाचार खोज्नुहोस्..."
                    class="flex-1 py-3 px-2 text-base focus:outline-none bg-transparent">
                <button type="submit"
                    class="px-5 py-2.5 bg-brand-500 text-white text-sm font-semibold rounded-lg hover:bg-brand-600 transition-colors">
                    Search
                </button>
            </div>
        </form>
    </div>

    @if($query)

    {{-- Type filter tabs --}}
    <div class="flex gap-2 flex-wrap mb-6">
        @php
            $tabs = [
                'posts'     => ['label' => 'लेखहरू',    'icon' => '📰'],
                'questions' => ['label' => 'प्रश्नहरू',  'icon' => '❓'],
                'users'     => ['label' => 'प्रयोगकर्ता','icon' => '👤'],
            ];
        @endphp
        @foreach($tabs as $key => $tab)
        <a href="/search?q={{ urlencode($query) }}&type={{ $key }}"
            class="flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold border transition-all
                {{ $type === $key
                    ? 'bg-brand-500 text-white border-brand-500 shadow-sm'
                    : 'bg-white text-gray-600 border-gray-200 hover:border-brand-300 hover:text-brand-600' }}">
            <span>{{ $tab['icon'] }}</span>
            <span class="font-nepali">{{ $tab['label'] }}</span>
            @if($counts[$key] > 0)
            <span class="text-xs {{ $type === $key ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }} rounded-full px-1.5 py-0.5 ml-0.5">
                {{ $counts[$key] > 99 ? '99+' : $counts[$key] }}
            </span>
            @endif
        </a>
        @endforeach
    </div>

    {{-- AI enhanced terms --}}
    @if($enhanced && !empty($enhanced['expanded_terms']))
    <div class="mb-5 p-4 bg-brand-50 rounded-xl border border-brand-100">
        <p class="text-xs font-semibold text-brand-600 mb-2">🤖 सम्बन्धित खोज</p>
        <div class="flex flex-wrap gap-2">
            @foreach($enhanced['expanded_terms'] as $term)
            <a href="/search?q={{ urlencode($term) }}&type={{ $type }}"
                class="text-xs px-2.5 py-1 bg-white border border-brand-200 text-brand-600 rounded-full hover:bg-brand-100 transition-colors">
                {{ $term }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Result count --}}
    @if($results && $results->total() > 0)
    <p class="text-sm text-gray-500 mb-4">
        "<span class="font-semibold text-gray-800">{{ $query }}</span>" को लागि
        <span class="font-semibold text-gray-800">{{ $results->total() }}</span> नतिजा
    </p>
    @endif

    {{-- Results --}}
    @if($results)
    <div class="space-y-4">

        {{-- POSTS --}}
        @if($type === 'posts')
        @forelse($results as $post)
        <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex gap-4 hover:shadow-md transition-shadow">
            @if($post->thumbnail_url)
            <a href="/posts/{{ $post->slug }}" class="flex-shrink-0">
                <img src="{{ $post->thumbnail_url }}" alt="" class="w-24 h-16 object-cover rounded-lg">
            </a>
            @endif
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <a href="/category/{{ $post->category->slug }}"
                        class="text-xs font-semibold text-brand-600 uppercase tracking-wide hover:underline">
                        {{ $post->category->name_ne ?? $post->category->name_en }}
                    </a>
                    @if($post->post_format && $post->post_format !== 'article')
                    <span class="text-xs px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded capitalize">{{ $post->post_format }}</span>
                    @endif
                </div>
                <h3 class="font-semibold text-gray-900 hover:text-brand-600 transition-colors mt-0.5 line-clamp-2 font-nepali text-sm leading-snug">
                    <a href="/posts/{{ $post->slug }}">{{ $post->title }}</a>
                </h3>
                @if($post->excerpt)
                <p class="text-xs text-gray-500 line-clamp-2 mt-1 font-nepali">{{ $post->excerpt }}</p>
                @endif
                <div class="flex items-center gap-2 text-xs text-gray-400 mt-2">
                    <span>{{ $post->author->name }}</span>
                    <span>·</span>
                    <span>{{ $post->published_at->diffForHumans() }}</span>
                    <span>·</span>
                    <span>{{ number_format($post->view_count) }} views</span>
                </div>
            </div>
        </article>
        @empty
        <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
            <div class="text-4xl mb-4">📰</div>
            <p class="text-gray-600 font-nepali font-semibold">"{{ $query }}" को कुनै लेख फेला परेन।</p>
            <p class="text-gray-400 text-sm mt-1">अर्को किवर्ड प्रयास गर्नुहोस्।</p>
        </div>
        @endforelse

        {{-- QUESTIONS --}}
        @elseif($type === 'questions')
        @forelse($results as $question)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-sm flex-shrink-0 mt-0.5">?</div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 hover:text-brand-600 transition-colors text-sm font-nepali line-clamp-2">
                        <a href="/questions/{{ $question->slug ?? $question->id }}">{{ $question->title }}</a>
                    </h3>
                    @if($question->content)
                    <p class="text-xs text-gray-500 line-clamp-2 mt-1 font-nepali">{{ strip_tags($question->content) }}</p>
                    @endif
                    <div class="flex items-center gap-2 text-xs text-gray-400 mt-2">
                        <span>{{ $question->user->name ?? 'Anonymous' }}</span>
                        <span>·</span>
                        <span>{{ $question->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
            <div class="text-4xl mb-4">❓</div>
            <p class="text-gray-600 font-nepali font-semibold">"{{ $query }}" को कुनै प्रश्न फेला परेन।</p>
            <p class="text-gray-400 text-sm mt-1">अर्को किवर्ड प्रयास गर्नुहोस्।</p>
        </div>
        @endforelse

        {{-- USERS --}}
        @elseif($type === 'users')
        @forelse($results as $user)
        <a href="/profile/{{ $user->username }}"
            class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4 hover:shadow-md transition-shadow block group">
            <div class="w-12 h-12 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold text-lg flex-shrink-0 group-hover:ring-2 group-hover:ring-brand-300 transition-all">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 group-hover:text-brand-600 transition-colors text-sm">{{ $user->name }}</p>
                <p class="text-xs text-brand-500">@{{ $user->username }}</p>
                @if($user->bio)
                <p class="text-xs text-gray-400 mt-1 line-clamp-1">{{ $user->bio }}</p>
                @endif
            </div>
            <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @empty
        <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
            <div class="text-4xl mb-4">👤</div>
            <p class="text-gray-600 font-semibold">"{{ $query }}" नामको कोही फेला परेन।</p>
            <p class="text-gray-400 text-sm mt-1">अर्को नाम खोज्नुहोस्।</p>
        </div>
        @endforelse
        @endif

    </div>

    @if($results->hasPages())
    <div class="flex justify-center mt-8">{{ $results->links() }}</div>
    @endif

    @else
    <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
        <div class="text-4xl mb-4">🔍</div>
        <p class="text-gray-500">केहि फेला परेन।</p>
    </div>
    @endif

    @endif {{-- end if $query --}}

</div>
@endsection
