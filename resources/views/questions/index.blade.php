@extends('layouts.app')
@section('title', 'Questions & Answers')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- Question list --}}
    <div class="lg:col-span-2 space-y-4">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Questions</h1>
            <a href="/ask-question"
                class="flex items-center gap-2 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Ask Question
            </a>
        </div>

        {{-- Search bar --}}
        <form method="GET" action="/questions" class="relative">
            @if(request('tab'))
            <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
            @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="q" value="{{ $search }}" placeholder="Search questions…"
                class="w-full pl-10 pr-10 py-2.5 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
            @if($search)
            <a href="/questions?tab={{ request('tab', 'new') }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
            @endif
        </form>

        @if($search)
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Showing results for <span class="font-semibold text-gray-900 dark:text-white">"{{ $search }}"</span>
            · {{ $questions->total() }} {{ Str::plural('result', $questions->total()) }}
        </p>
        @endif

        {{-- Tabs --}}
        <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl overflow-x-auto">
            @foreach(['new' => 'Newest', 'top' => 'Top', 'trending' => 'Trending', 'hot' => 'Hot 🔥', 'must-read' => 'Must Read'] as $key => $label)
            <a href="?tab={{ $key }}{{ $search ? '&q='.urlencode($search) : '' }}{{ request('category') ? '&category='.request('category') : '' }}"
                class="flex-1 text-center py-1.5 text-sm font-medium rounded-lg whitespace-nowrap transition-colors {{ $tab === $key ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        @forelse($questions as $q)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 hover:shadow-md transition-shadow">
            <div class="flex gap-4">
                {{-- Vote/answer counts --}}
                <div class="flex flex-col items-center gap-3 flex-shrink-0 text-center w-14">
                    <div class="text-sm font-bold {{ $q->votes > 0 ? 'text-orange-500' : ($q->votes < 0 ? 'text-blue-500' : 'text-gray-700 dark:text-gray-300') }}">
                        {{ $q->votes >= 1000 ? round($q->votes / 1000, 1).'k' : $q->votes }}
                        <span class="block text-[10px] font-normal text-gray-400">votes</span>
                    </div>
                    <div class="text-sm font-bold {{ $q->best_answer_id ? 'text-green-500' : 'text-gray-700 dark:text-gray-300' }}">
                        {{ $q->answers_count }}
                        <span class="block text-[10px] font-normal text-gray-400">{{ $q->answers_count == 1 ? 'answer' : 'answers' }}</span>
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ number_format($q->views_count) }}
                        <span class="block">views</span>
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <a href="/questions/{{ $q->slug }}"
                        class="text-base font-semibold text-gray-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 leading-snug block mb-2">
                        {{ $q->title }}
                    </a>
                    @if($q->content)
                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">{{ strip_tags($q->content) }}</p>
                    @endif

                    {{-- Tags --}}
                    @if($q->tags && $q->tags->count())
                    <div class="flex flex-wrap gap-1 mb-2">
                        @foreach($q->tags->take(4) as $tag)
                        <a href="/questions?tag={{ $tag->slug }}"
                            class="text-xs px-2 py-0.5 bg-gray-100 dark:bg-gray-700 hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-brand-600 text-gray-500 dark:text-gray-400 rounded-full transition-colors">
                            {{ $tag->name_en }}
                        </a>
                        @endforeach
                    </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-2">
                        @if($q->category)
                        <a href="/questions?category={{ $q->category->slug }}"
                            class="text-xs px-2.5 py-0.5 bg-brand-50 dark:bg-brand-900/20 text-brand-600 rounded-full font-medium hover:bg-brand-100 transition-colors">
                            {{ $q->category->name_ne ?? $q->category->name_en }}
                        </a>
                        @endif
                        @if($q->best_answer_id)
                        <span class="text-xs px-2.5 py-0.5 bg-green-50 dark:bg-green-900/20 text-green-600 rounded-full font-medium flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Answered
                        </span>
                        @endif
                        <span class="ml-auto text-xs text-gray-400 flex items-center gap-1.5">
                            @if(!$q->is_anonymous && $q->user)
                            <span>by <a href="/profile/{{ $q->user->username ?? $q->user->name }}" class="hover:text-brand-600 font-medium">{{ $q->user->name }}</a></span>
                            @else
                            <span>by Anonymous</span>
                            @endif
                            · {{ $q->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @if($search)
            <p class="text-gray-500 dark:text-gray-400 font-medium">No results for "{{ $search }}"</p>
            <p class="text-sm text-gray-400 mt-1">Try a different search term or ask a new question.</p>
            @else
            <p class="text-gray-500 dark:text-gray-400 font-medium">No questions yet</p>
            <p class="text-sm text-gray-400 mt-1">Be the first to ask a question!</p>
            @endif
            <a href="/ask-question" class="inline-block mt-4 px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl transition-colors">Ask a Question</a>
        </div>
        @endforelse

        {{ $questions->appends(request()->query())->links() }}
    </div>

    {{-- Sidebar --}}
    <div class="space-y-5">
        <a href="/ask-question"
            class="w-full flex items-center justify-center gap-2 py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors block">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Ask a Question
        </a>

        {{-- Stats --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-3">Community Stats</h3>
            <div class="space-y-2">
                @foreach([
                    ['Questions', $totalQuestions, 'text-brand-600'],
                    ['Answers', $totalAnswers, 'text-green-600'],
                    ['Best Answers', $bestAnswers, 'text-amber-500'],
                    ['Members', $totalUsers, 'text-purple-600'],
                ] as [$label, $val, $color])
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">{{ $label }}</span>
                    <span class="font-bold {{ $color }}">{{ number_format($val) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Categories --}}
        @if($questionCategories->count())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-3">Browse by Category</h3>
            <div class="space-y-1.5">
                <a href="/questions" class="flex items-center justify-between text-sm py-1 {{ !request('category') ? 'text-brand-600 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:text-brand-600' }}">
                    <span>All Questions</span>
                    <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">{{ $totalQuestions }}</span>
                </a>
                @foreach($questionCategories as $cat)
                <a href="/questions?category={{ $cat->slug }}"
                    class="flex items-center justify-between text-sm py-1 {{ request('category') === $cat->slug ? 'text-brand-600 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:text-brand-600' }}">
                    <span>{{ $cat->name_ne ?? $cat->name_en }}</span>
                    <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">{{ $cat->questions_count }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Popular Tags --}}
        @if($popularTags->count())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-3">Popular Tags</h3>
            <div class="flex flex-wrap gap-1.5">
                @foreach($popularTags as $tag)
                <a href="/questions?tag={{ $tag->slug }}"
                    class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-brand-600 dark:hover:text-brand-400 text-gray-600 dark:text-gray-300 rounded-full transition-colors {{ request('tag') === $tag->slug ? 'bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 font-semibold' : '' }}">
                    {{ $tag->name_en }}
                    <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $tag->questions_count }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Top Members --}}
        @if($topMembers->count())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-3">Top Contributors</h3>
            <div class="space-y-3">
                @foreach($topMembers as $i => $member)
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 flex-shrink-0 text-center text-xs font-bold text-gray-400">{{ $i + 1 }}</div>
                    <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $member->name }}</p>
                        <p class="text-xs text-gray-400">{{ $member->questions_count }}q · {{ $member->answers_count }}a</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
