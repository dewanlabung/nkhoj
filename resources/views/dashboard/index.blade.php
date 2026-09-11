@extends('layouts.app')
@section('title', 'Dashboard — nkhoj')
@push('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="flex items-center justify-between mb-8 flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome, {{ auth()->user()->name }} 👋</h1>
        <p class="text-gray-400 text-sm mt-1">Manage your articles and use AI tools</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="/dashboard/analytics"
            class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-semibold rounded-xl transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Analytics
        </a>
        <a href="/dashboard/posts/create"
            class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Article
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm text-gray-500">Total Articles</p>
        <p class="text-3xl font-black text-gray-900 mt-1">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm text-gray-500">Published</p>
        <p class="text-3xl font-black text-green-600 mt-1">{{ $stats['published'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm text-gray-500">Total Views</p>
        <p class="text-3xl font-black text-brand-600 mt-1">{{ number_format($stats['views']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm text-gray-500">Drafts</p>
        <p class="text-3xl font-black text-yellow-500 mt-1">{{ $stats['drafts'] }}</p>
    </div>
</div>

{{-- Top posts chart --}}
@php
    $topPosts = \App\Models\Post::where('author_id', auth()->id())
        ->where('status', 'published')
        ->orderByDesc('view_count')
        ->limit(5)
        ->get(['title', 'view_count', 'slug']);
@endphp
@if($topPosts->isNotEmpty())
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-8">
    <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
        <span class="text-lg">📊</span> Top Performing Articles
    </h2>
    <canvas id="viewsChart" height="120"></canvas>
    <script>
    (function(){
        const labels = {!! json_encode($topPosts->map(fn($p) => \Illuminate\Support\Str::limit($p->title, 30))->toArray()) !!};
        const data   = {!! json_encode($topPosts->pluck('view_count')->toArray()) !!};
        const ctx = document.getElementById('viewsChart');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Views',
                    data,
                    backgroundColor: 'rgba(99,102,241,0.7)',
                    borderColor: 'rgb(99,102,241)',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { ticks: { font: { size: 11 } } }
                }
            }
        });
    })();
    </script>
</div>
@endif

{{-- Articles list --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-bold text-gray-900">Your Articles</h2>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($posts as $post)
        <div class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
            <div class="flex-1 min-w-0">
                <h3 class="font-medium text-gray-900 truncate">{{ $post->title }}</h3>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $post->status === 'published' ? 'bg-green-100 text-green-700' : ($post->status === 'draft' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ ucfirst($post->status) }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $post->updated_at->diffForHumans() }}</span>
                    @if($post->status === 'published')
                    <span class="text-xs text-gray-400">· {{ number_format($post->view_count) }} views</span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="/dashboard/posts/{{ $post->id }}/edit"
                    class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Edit
                </a>
                @if($post->status === 'published')
                <a href="/posts/{{ $post->slug }}"
                    class="px-3 py-1.5 text-xs font-medium text-brand-600 bg-brand-50 hover:bg-brand-100 rounded-lg transition-colors">
                    View
                </a>
                @endif
            </div>
        </div>
        @empty
        <div class="px-6 py-16 text-center">
            <div class="text-4xl mb-3">✍️</div>
            <p class="text-gray-500">You haven't written anything yet.</p>
            <a href="/dashboard/posts/create" class="mt-3 inline-block text-sm font-semibold text-brand-600 hover:text-brand-700">
                Write your first article →
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection
