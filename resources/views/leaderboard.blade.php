@extends('layouts.app')
@section('title', 'Leaderboard — NKHOJ')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-900 dark:text-white">🏆 Leaderboard</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Top contributors on NKHOJ</p>
    </div>

    @php
    $tabs = [
        ['id'=>'followers', 'label'=>'Most Followed', 'icon'=>'👥', 'data'=>$topByFollowers, 'metric'=>'followers_count', 'unit'=>'followers'],
        ['id'=>'views',     'label'=>'Most Viewed',   'icon'=>'👁️', 'data'=>$topByViews,     'metric'=>'total_views',      'unit'=>'views'],
        ['id'=>'posts',     'label'=>'Most Published','icon'=>'📝', 'data'=>$topByPosts,     'metric'=>'published_posts_count', 'unit'=>'posts'],
    ];
    @endphp

    <div x-data="{ tab: 'followers' }">
        {{-- Tab bar --}}
        <div class="flex gap-2 mb-6 border-b border-gray-100 dark:border-gray-700 pb-0">
            @foreach($tabs as $t)
            <button @click="tab = '{{ $t['id'] }}'"
                :class="tab === '{{ $t['id'] }}' ? 'border-b-2 border-brand-500 text-brand-600 dark:text-brand-400 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="flex items-center gap-1.5 px-4 py-3 text-sm transition-colors -mb-px">
                <span>{{ $t['icon'] }}</span>
                <span>{{ $t['label'] }}</span>
            </button>
            @endforeach
        </div>

        @foreach($tabs as $t)
        <div x-show="tab === '{{ $t['id'] }}'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                @foreach($t['data'] as $i => $author)
                @php $rank = $i + 1; @endphp
                <div class="flex items-center gap-4 px-5 py-4 {{ !$loop->last ? 'border-b border-gray-50 dark:border-gray-700' : '' }} hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                    {{-- Rank --}}
                    <div class="w-8 text-center flex-shrink-0">
                        @if($rank === 1)
                            <span class="text-2xl">🥇</span>
                        @elseif($rank === 2)
                            <span class="text-2xl">🥈</span>
                        @elseif($rank === 3)
                            <span class="text-2xl">🥉</span>
                        @else
                            <span class="text-sm font-bold text-gray-400 dark:text-gray-500">#{{ $rank }}</span>
                        @endif
                    </div>
                    {{-- Avatar --}}
                    <a href="/profile/{{ $author->username }}" class="flex-shrink-0">
                        @if($author->avatar_url)
                        <img src="{{ $author->avatar_url }}" alt="{{ $author->name }}" class="w-11 h-11 rounded-full object-cover ring-2 ring-white dark:ring-gray-700 shadow-sm">
                        @else
                        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                            {{ mb_substr($author->name, 0, 1) }}
                        </div>
                        @endif
                    </a>
                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <a href="/profile/{{ $author->username }}" class="font-semibold text-gray-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 transition-colors block truncate">{{ $author->name }}</a>
                        <p class="text-xs text-gray-400 truncate">@{{ $author->username }}</p>
                    </div>
                    {{-- Metric --}}
                    <div class="text-right flex-shrink-0">
                        <p class="text-lg font-black text-gray-900 dark:text-white">{{ number_format($author->{ $t['metric'] } ?? 0) }}</p>
                        <p class="text-xs text-gray-400">{{ $t['unit'] }}</p>
                    </div>
                </div>
                @endforeach
                @if($t['data']->isEmpty())
                <div class="py-16 text-center text-gray-400">No data yet.</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
