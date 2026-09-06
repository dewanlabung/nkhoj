@extends('layouts.app')
@section('title', ($category->name_ne ?? $category->name_en) . ' — nkhoj')

@section('content')
@php $activeTab = request('tab', 'posts'); @endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        {{-- Header --}}
        <div class="flex items-center gap-3 mb-5">
            <div class="w-1 h-8 bg-brand-500 rounded-full"></div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $category->name_ne ?? $category->name_en }}</h1>
                <p class="text-sm text-gray-400">{{ $posts->total() }} articles · {{ $questions->total() }} questions</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl mb-5">
            <a href="?tab=posts"
                class="flex-1 text-center py-1.5 text-sm font-medium rounded-lg transition-colors {{ $activeTab === 'posts' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                Articles ({{ $posts->total() }})
            </a>
            <a href="?tab=questions"
                class="flex-1 text-center py-1.5 text-sm font-medium rounded-lg transition-colors {{ $activeTab === 'questions' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                Questions ({{ $questions->total() }})
            </a>
        </div>

        {{-- Posts tab --}}
        @if($activeTab === 'posts')
        <div class="space-y-5">
            @forelse($posts as $post)
            <article class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow p-5 flex gap-4">
                @if($post->thumbnail_url)
                <a href="/posts/{{ $post->slug }}" class="flex-shrink-0">
                    <img src="{{ $post->thumbnail_url }}" alt="" class="w-28 h-20 object-cover rounded-lg">
                </a>
                @endif
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 dark:text-white hover:text-brand-600 transition-colors line-clamp-2 mb-1">
                        <a href="/posts/{{ $post->slug }}">{{ $post->title }}</a>
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-2">{{ $post->excerpt }}</p>
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        <span>{{ $post->author->name }}</span>
                        <span>·</span>
                        <span>{{ $post->published_at->diffForHumans() }}</span>
                    </div>
                </div>
            </article>
            @empty
            <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <p class="text-gray-400">No articles in this category yet.</p>
            </div>
            @endforelse
        </div>
        <div class="mt-6">{{ $posts->appends(['tab' => 'posts'])->links() }}</div>
        @endif

        {{-- Questions tab --}}
        @if($activeTab === 'questions')
        <div class="space-y-4">
            @forelse($questions as $q)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 flex gap-4 hover:shadow-md transition-shadow">
                {{-- Vote / answer counts --}}
                <div class="flex flex-col items-center gap-3 flex-shrink-0 text-center w-12">
                    <div class="text-sm font-bold {{ $q->votes > 0 ? 'text-brand-600' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ $q->votes }}
                        <span class="block text-[10px] font-normal text-gray-400">votes</span>
                    </div>
                    <div class="text-sm font-bold {{ $q->best_answer_id ? 'text-green-500' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ $q->answers_count }}
                        <span class="block text-[10px] font-normal text-gray-400">answers</span>
                    </div>
                </div>
                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <a href="/questions/{{ $q->slug }}"
                        class="text-base font-semibold text-gray-900 dark:text-white hover:text-brand-600 leading-snug block mb-1">
                        {{ $q->title }}
                    </a>
                    @if($q->content)
                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-2">{{ strip_tags($q->content) }}</p>
                    @endif
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        @if($q->best_answer_id)
                        <span class="px-2 py-0.5 bg-green-50 dark:bg-green-900/20 text-green-600 rounded-full font-medium flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Answered
                        </span>
                        @endif
                        <span>{{ $q->is_anonymous ? 'Anonymous' : ($q->user->name ?? '') }}</span>
                        <span>·</span>
                        <span>{{ $q->created_at->diffForHumans() }}</span>
                        <span>·</span>
                        <span>{{ $q->views_count }} views</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <p class="text-gray-400">No questions in this category yet.</p>
                <a href="/ask-question" class="inline-block mt-3 px-4 py-2 bg-brand-500 text-white text-sm font-semibold rounded-xl hover:bg-brand-600 transition-colors">Ask the first question</a>
            </div>
            @endforelse
        </div>
        <div class="mt-6">{{ $questions->appends(['tab' => 'questions'])->links() }}</div>
        @endif
    </div>

    <aside class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">All Categories</h3>
            <div class="space-y-1">
                @php
                try {
                    $allCats = \App\Models\Category::withCount(['posts' => fn($q) => $q->published()])
                        ->withCount('questions')
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->get();
                } catch (\Exception $e) { $allCats = collect(); }
                @endphp
                @foreach($allCats as $cat)
                <a href="/category/{{ $cat->slug }}"
                    class="flex justify-between items-center py-2 text-sm {{ $cat->id === $category->id ? 'text-brand-600 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:text-brand-600' }} transition-colors border-b border-gray-50 dark:border-gray-700 last:border-0">
                    <span>{{ $cat->name_ne ?? $cat->name_en }}</span>
                    <div class="flex items-center gap-1 text-xs text-gray-400">
                        <span class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">{{ $cat->posts_count }}p</span>
                        @if($cat->questions_count > 0)
                        <span class="bg-brand-50 dark:bg-brand-900/20 text-brand-600 px-1.5 py-0.5 rounded">{{ $cat->questions_count }}q</span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        <a href="/ask-question"
            class="w-full flex items-center justify-center gap-2 py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors block">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Ask a Question
        </a>
    </aside>
</div>
@endsection
