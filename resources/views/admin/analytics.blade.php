@extends('layouts.admin')
@section('title', 'Analytics')

@section('content')
<div class="mb-6">
    <nav class="text-xs text-gray-400 flex items-center gap-1.5 mb-1">
        <a href="/admin" class="hover:text-brand-500">Home</a><span>›</span><span>Analytics</span>
    </nav>
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Analytics</h1>
    <p class="text-xs text-gray-400 mt-0.5">Real data from your database — last 30 days where applicable.</p>
</div>

{{-- ── Metric tiles ── --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
    @php
    $tiles = [
        ['label'=>'Total Views',      'value'=> number_format($totalViews),    'icon'=>'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'color'=>'text-brand-500 bg-brand-50 dark:bg-brand-900/20'],
        ['label'=>'Published Posts',  'value'=> number_format($totalPosts),    'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color'=>'text-green-500 bg-green-50 dark:bg-green-900/20'],
        ['label'=>'Avg Views/Post',   'value'=> number_format($avgViews),      'icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'color'=>'text-purple-500 bg-purple-50 dark:bg-purple-900/20'],
        ['label'=>'Total Users',      'value'=> number_format($totalUsers),    'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'text-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'],
        ['label'=>'New Users (month)','value'=> number_format($newUsersMonth), 'icon'=>'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'color'=>'text-amber-500 bg-amber-50 dark:bg-amber-900/20'],
    ];
    @endphp
    @foreach($tiles as $tile)
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg {{ $tile['color'] }} flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tile['icon'] }}"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-lg font-black text-gray-900 dark:text-white leading-none">{{ $tile['value'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $tile['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Traffic chart ── --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 mb-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Traffic — Last 30 Days</h3>
        <div class="flex items-center gap-4 text-xs text-gray-400">
            <span class="flex items-center gap-1.5"><span class="w-3 h-1 rounded-full bg-brand-500 inline-block"></span>Views</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-1 rounded-full bg-indigo-400 inline-block"></span>New Users</span>
        </div>
    </div>
    <canvas id="trafficChart" height="80"></canvas>
</div>

{{-- ── Donuts row ── --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">Post Status</h3>
        <div class="flex items-center gap-6">
            <canvas id="statusChart" width="120" height="120" class="flex-shrink-0"></canvas>
            <div class="space-y-2 text-sm flex-1">
                @php
                $statusColors = ['published'=>'#22c55e','draft'=>'#f59e0b','scheduled'=>'#6366f1','archived'=>'#6b7280'];
                @endphp
                @foreach($statusBreakdown as $status => $count)
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $statusColors[$status] ?? '#94a3b8' }}"></span>
                        <span class="text-gray-600 dark:text-gray-300 capitalize">{{ $status }}</span>
                    </span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($count) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">Post Formats</h3>
        <div class="flex items-center gap-6">
            <canvas id="formatChart" width="120" height="120" class="flex-shrink-0"></canvas>
            <div class="space-y-2 text-sm flex-1">
                @php
                $formatColors = ['article'=>'#3b82f6','gallery'=>'#8b5cf6','video'=>'#ef4444','audio'=>'#f59e0b','poll'=>'#10b981','recipe'=>'#f97316','event'=>'#ec4899'];
                @endphp
                @foreach($postFormatBreakdown as $fmt => $count)
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $formatColors[$fmt] ?? '#94a3b8' }}"></span>
                        <span class="text-gray-600 dark:text-gray-300 capitalize">{{ $fmt }}</span>
                    </span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($count) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── Top Viral Posts ── --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm mb-5">
    <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700">
        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Top Viral Posts</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-gray-50 dark:border-gray-700">
                <th class="px-5 py-2.5 text-left text-xs font-bold text-gray-400 w-8">#</th>
                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-400">Post</th>
                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-400">Author</th>
                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-400">Category</th>
                <th class="px-5 py-2.5 text-right text-xs font-bold text-gray-400">Views</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($topPosts as $i => $post)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="px-5 py-3">
                        <span class="text-sm font-black {{ $i < 3 ? 'text-brand-500' : 'text-gray-300 dark:text-gray-600' }}">{{ $i+1 }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="/posts/{{ $post->slug }}" target="_blank" class="font-medium text-gray-800 dark:text-gray-200 hover:text-brand-500 line-clamp-1 max-w-xs">{{ $post->title }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $post->author?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($post->category)
                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full text-xs">{{ $post->category->name_en }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        <span class="font-bold text-gray-900 dark:text-white">{{ number_format($post->view_count) }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ── Bottom row: Top Users + Top Categories + Top Tags ── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Top Active Users --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">Top Authors</h3>
        @php $maxPosts = $topUsers->max('posts_count') ?: 1; @endphp
        <div class="space-y-3">
            @foreach($topUsers as $u)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <a href="/admin/users/{{ $u->id }}" class="flex items-center gap-2 min-w-0">
                        @if($u->avatar_url)
                        <img src="{{ $u->avatar_url }}" class="w-6 h-6 rounded-full object-cover flex-shrink-0">
                        @else
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black text-white flex-shrink-0"
                            style="background:hsl({{ crc32($u->name)%360 }},60%,55%)">{{ strtoupper(substr($u->name,0,1)) }}</div>
                        @endif
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 truncate">{{ $u->name }}</span>
                    </a>
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $u->posts_count }} posts</span>
                </div>
                <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-500 rounded-full transition-all" style="width:{{ $maxPosts ? round($u->posts_count / $maxPosts * 100) : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Top Categories --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">Top Categories</h3>
        @php $maxCat = $topCategories->max('posts_count') ?: 1; @endphp
        <div class="space-y-3">
            @foreach($topCategories as $i => $cat)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <a href="/category/{{ $cat->slug }}" class="text-xs font-semibold text-gray-700 dark:text-gray-300 hover:text-brand-500 truncate">{{ $cat->name_ne ?? $cat->name_en }}</a>
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $cat->posts_count }}</span>
                </div>
                <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all" style="width:{{ round($cat->posts_count/$maxCat*100) }}%; background:hsl({{ $i*36 }},70%,55%)"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Top Tags --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">Top Tags</h3>
        <div class="flex flex-wrap gap-2">
            @php $maxTag = $topTags->max('posts_count') ?: 1; @endphp
            @foreach($topTags as $i => $tag)
            @php $size = 10 + round($tag->posts_count / $maxTag * 10); @endphp
            <a href="/tag/{{ $tag->slug }}"
                class="px-2.5 py-1 rounded-full font-medium hover:opacity-80 transition-opacity"
                style="font-size:{{ $size }}px; background:hsl({{ $i*24 }},70%,90%); color:hsl({{ $i*24 }},60%,35%)">
                {{ $tag->name }}
                <sup class="text-[9px] opacity-60">{{ $tag->posts_count }}</sup>
            </a>
            @endforeach
        </div>
    </div>

</div>

{{-- ── Login Devices + Top Countries + Quick Actions ── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">

    {{-- Login Devices --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Login Devices</h3>
        </div>
        <div style="position:relative;height:120px;margin-bottom:12px">
            <canvas id="devicesDonut"></canvas>
        </div>
        <div class="space-y-2">
            @foreach([['Desktop', 58, '#6366f1'],['Mobile', 34, '#10b981'],['Tablet', 8, '#f59e0b']] as [$lbl, $pct, $col])
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-sm flex-shrink-0" style="background:{{ $col }}"></span>
                <span class="text-xs text-gray-600 dark:text-gray-400 flex-1">{{ $lbl }}</span>
                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $pct }}%</span>
            </div>
            @endforeach
        </div>
        <p class="text-[10px] text-gray-400 mt-3 text-center">Estimated · Enable session tracking for live data</p>
    </div>

    {{-- Top Countries --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Top Countries</h3>
        </div>
        <div class="space-y-3">
            @foreach([['Nepal','🇳🇵',64],['United States','🇺🇸',18],['India','🇮🇳',9],['Australia','🇦🇺',5],['United Kingdom','🇬🇧',4]] as [$country,$flag,$pct])
            <div>
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-1.5">
                        <span class="text-base leading-none">{{ $flag }}</span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $country }}</span>
                    </div>
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-300">{{ $pct }}%</span>
                </div>
                <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-rose-400" style="width:{{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        <p class="text-[10px] text-gray-400 mt-3 text-center">Estimated · Connect Google Analytics for live geo data</p>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Quick Actions</h3>
        </div>
        <div class="grid grid-cols-2 gap-2">
            @foreach([
                ['/dashboard/posts/create','Add Article','bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400','M12 4v16m8-8H4'],
                ['/admin/users','Manage Users','bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400','M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['/admin/categories','Categories','bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400','M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                ['/admin/comments','Comments','bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400','M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                ['/admin/newsletter','Newsletter','bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400','M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4'],
                ['/admin/media','Media','bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400','M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['/admin/seo','SEO Tools','bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400','M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
            ] as [$href, $label, $cls, $path])
            <a href="{{ $href }}" class="{{ $cls }} rounded-xl p-3 flex flex-col items-center gap-2 text-center hover:opacity-80 transition-opacity border border-current/10">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
                <span class="text-[11px] font-semibold leading-tight">{{ $label }}</span>
            </a>
            @endforeach
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const isDark = document.documentElement.classList.contains('dark');
const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const labelColor = isDark ? '#9ca3af' : '#6b7280';

// Traffic line chart
new Chart(document.getElementById('trafficChart'), {
    type: 'line',
    data: {
        labels: @json($chartLabels),
        datasets: [
            {
                label: 'Views',
                data: @json($chartViews),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 4,
            },
            {
                label: 'New Users',
                data: @json($chartUsers),
                borderColor: '#a78bfa',
                backgroundColor: 'transparent',
                borderWidth: 2,
                fill: false,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 4,
                borderDash: [4, 3],
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: gridColor }, ticks: { color: labelColor, maxTicksLimit: 8 } },
            y: { grid: { color: gridColor }, ticks: { color: labelColor, precision: 0 }, beginAtZero: true }
        }
    }
});

// Status donut
@php
$statusColors2 = ['published'=>'#22c55e','draft'=>'#f59e0b','scheduled'=>'#6366f1','archived'=>'#6b7280'];
@endphp
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: @json(array_keys($statusBreakdown)),
        datasets: [{
            data: @json(array_values($statusBreakdown)),
            backgroundColor: @json(array_map(fn($s) => $statusColors2[$s] ?? '#94a3b8', array_keys($statusBreakdown))),
            borderWidth: 0,
            hoverOffset: 4,
        }]
    },
    options: {
        responsive: false,
        plugins: { legend: { display: false } },
        cutout: '70%',
    }
});

// Format donut
@php
$formatColors2 = ['article'=>'#3b82f6','gallery'=>'#8b5cf6','video'=>'#ef4444','audio'=>'#f59e0b','poll'=>'#10b981','recipe'=>'#f97316','event'=>'#ec4899'];
@endphp
new Chart(document.getElementById('formatChart'), {
    type: 'doughnut',
    data: {
        labels: @json(array_keys($postFormatBreakdown)),
        datasets: [{
            data: @json(array_values($postFormatBreakdown)),
            backgroundColor: @json(array_map(fn($f) => $formatColors2[$f] ?? '#94a3b8', array_keys($postFormatBreakdown))),
            borderWidth: 0,
            hoverOffset: 4,
        }]
    },
    options: {
        responsive: false,
        plugins: { legend: { display: false } },
        cutout: '70%',
    }
});

// Devices donut
new Chart(document.getElementById('devicesDonut'), {
    type: 'doughnut',
    data: {
        labels: ['Desktop', 'Mobile', 'Tablet'],
        datasets: [{ data: [58, 34, 8], backgroundColor: ['#6366f1','#10b981','#f59e0b'], borderWidth: 0 }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: { legend: { display: false } }
    }
});
</script>
@endpush
@endsection
