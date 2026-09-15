@extends('layouts.admin')
@section('title', 'Search Analytics')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">Search Analytics</h1>
        <span class="text-sm text-gray-400">Last 30 days</span>
    </div>

    {{-- KPI row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Total Searches</p>
            <p class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($totalSearches) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Unique Searchers</p>
            <p class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($uniqueSearchers) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Unique Queries</p>
            <p class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($topQueries->count()) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Zero-Result Queries</p>
            <p class="text-3xl font-black text-red-500">{{ number_format($zeroResults->count()) }}</p>
        </div>
    </div>

    {{-- Daily volume spark --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 mb-6">
        <h2 class="font-bold text-gray-700 dark:text-gray-300 mb-4 text-sm uppercase tracking-wide">Daily Search Volume (14 days)</h2>
        <div class="flex items-end gap-1 h-20">
            @php $maxVol = $dailyVolume->max('total') ?: 1; @endphp
            @foreach($dailyVolume as $day)
            <div class="flex-1 flex flex-col items-center gap-1" title="{{ $day->date }}: {{ $day->total }} searches">
                <div class="w-full bg-indigo-400 dark:bg-indigo-500 rounded-t" style="height:{{ max(4, round(($day->total / $maxVol) * 72)) }}px"></div>
                <span class="text-[9px] text-gray-400 rotate-45 origin-left">{{ \Carbon\Carbon::parse($day->date)->format('d/m') }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        {{-- Top queries --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <h2 class="font-bold text-gray-700 dark:text-gray-300 mb-4 text-sm uppercase tracking-wide">Top 20 Queries</h2>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($topQueries as $i => $row)
                <div class="flex items-center justify-between py-2">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="text-xs text-gray-400 w-5 text-right flex-shrink-0">{{ $i + 1 }}</span>
                        <a href="/search?q={{ urlencode($row->query) }}" target="_blank"
                           class="text-sm text-gray-800 dark:text-gray-200 hover:text-indigo-600 truncate font-medium font-nepali">{{ $row->query }}</a>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0 ml-3">
                        <span class="text-xs text-gray-400">{{ round($row->avg_results) }} results</span>
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded-full">{{ $row->total }}×</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Zero-result queries --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <h2 class="font-bold text-red-500 mb-1 text-sm uppercase tracking-wide">Zero-Result Queries</h2>
            <p class="text-xs text-gray-400 mb-4">Content gaps — these searches returned nothing</p>
            @if($zeroResults->isEmpty())
            <p class="text-sm text-green-600 font-medium">🎉 No zero-result queries in the last 30 days!</p>
            @else
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($zeroResults as $i => $row)
                <div class="flex items-center justify-between py-2">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="text-xs text-gray-400 w-5 text-right flex-shrink-0">{{ $i + 1 }}</span>
                        <span class="text-sm text-gray-800 dark:text-gray-200 truncate font-nepali">{{ $row->query }}</span>
                    </div>
                    <span class="text-xs font-bold text-red-500 bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full flex-shrink-0 ml-3">{{ $row->total }}×</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
