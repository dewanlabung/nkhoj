@extends('layouts.app')
@section('title', 'My Analytics — Dashboard')
@push('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Analytics</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Performance overview of your published articles</p>
        </div>
        <a href="/dashboard" class="text-sm text-brand-600 hover:underline">← Back to Dashboard</a>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Views</p>
            <p class="text-3xl font-black text-brand-600 mt-1">{{ number_format($totalViews) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Published</p>
            <p class="text-3xl font-black text-green-600 mt-1">{{ $totalPosts }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Comments</p>
            <p class="text-3xl font-black text-indigo-600 mt-1">{{ number_format($commentCount) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Scheduled</p>
            <p class="text-3xl font-black text-yellow-500 mt-1">{{ $scheduled }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Drafts</p>
            <p class="text-3xl font-black text-gray-400 mt-1">{{ $drafts }}</p>
        </div>
    </div>

    {{-- Views by month chart --}}
    @if($viewsByMonth->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 mb-8">
        <h2 class="font-bold text-gray-900 dark:text-white mb-4">Views by Month (last 6 months)</h2>
        <canvas id="monthChart" height="100"></canvas>
        <script>
        (function(){
            const labels = {!! json_encode($viewsByMonth->pluck('month')->toArray()) !!};
            const views  = {!! json_encode($viewsByMonth->pluck('views')->map(fn($v) => (int)$v)->toArray()) !!};
            const ctx = document.getElementById('monthChart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Views',
                        data: views,
                        backgroundColor: 'rgba(99,102,241,0.7)',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        })();
        </script>
    </div>
    @endif

    {{-- Top posts table --}}
    @if($topPosts->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="font-bold text-gray-900 dark:text-white">Top Articles by Views</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-left">
                        <th class="px-6 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase">#</th>
                        <th class="px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase">Article</th>
                        <th class="px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase text-right">Views</th>
                        <th class="px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase text-right">Published</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach($topPosts as $i => $post)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-3 text-gray-400 font-medium">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <a href="/posts/{{ $post->slug }}" target="_blank"
                               class="text-gray-800 dark:text-gray-200 hover:text-brand-600 dark:hover:text-brand-400 font-medium line-clamp-1">
                                {{ $post->title }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-brand-600">{{ number_format($post->view_count) }}</td>
                        <td class="px-4 py-3 text-right text-gray-400 text-xs">{{ $post->published_at?->format('M j, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="text-center py-16 text-gray-400">
        <p class="text-5xl mb-4">📊</p>
        <p class="font-semibold text-gray-600 dark:text-gray-300">No published articles yet</p>
        <p class="text-sm mt-1">Publish your first article to start tracking analytics.</p>
        <a href="/dashboard/posts/create" class="mt-4 inline-block px-5 py-2.5 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700 transition-colors">
            Write an Article
        </a>
    </div>
    @endif
</div>
@endsection
