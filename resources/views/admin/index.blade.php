@extends('layouts.admin')
@section('title', 'Dashboard')

@push('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
@endpush

@section('content')
{{-- Stat tiles --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    @foreach([
        ['Total Posts',    $stats['posts'],     'bg-blue-500',   'text-blue-600 dark:text-blue-400'],
        ['Published',      $stats['published'], 'bg-green-500',  'text-green-600 dark:text-green-400'],
        ['Total Users',    $stats['users'],     'bg-purple-500', 'text-purple-600 dark:text-purple-400'],
        ['Comments',       $stats['comments'],  'bg-yellow-500', 'text-yellow-600 dark:text-yellow-400'],
        ['Total Views',    number_format($stats['views']), 'bg-red-500', 'text-red-600 dark:text-red-400'],
        ['Tags',           $stats['tags'],      'bg-indigo-500', 'text-indigo-600 dark:text-indigo-400'],
    ] as [$label, $val, $color, $accent])
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 shadow-sm">
        <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $val }}</div>
        <div class="text-xs {{ $accent }} mt-0.5 font-medium">{{ $label }}</div>
    </div>
    @endforeach
</div>

{{-- Chart + Highlights --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Views chart --}}
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-sm">Earnings & Stats</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500">Unique Pageviews — Last 30 days</p>
            </div>
            <span class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ number_format($stats['views']) }}</span>
        </div>
        <div style="position:relative;height:180px">
            <canvas id="viewChart" role="img" aria-label="Pageviews chart">Pageviews over time.</canvas>
        </div>
    </div>

    {{-- Highlights --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">Highlights</h3>
        <div class="space-y-1">
            @foreach([
                ['Pending Posts',    $stats['pending_posts'] ?? 0,   '/admin/posts?status=draft'],
                ['Contact Messages', $stats['contacts'] ?? 0,        '/admin/contacts'],
                ['Newsletter',       $stats['subscribers'] ?? 0,     '/admin/newsletter'],
                ['Pending Comments', $stats['pending_comments'] ?? 0,'/admin/comments'],
            ] as [$label, $count, $href])
            <a href="{{ $href }}" class="flex items-center justify-between py-2.5 border-b border-dashed border-gray-100 dark:border-gray-700/60 hover:text-blue-600 dark:hover:text-blue-400 transition-colors group">
                <span class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ $label }}</span>
                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $count }}</span>
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- Pending Posts + Recent Comments --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-700/60">
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-sm">Pending Posts</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500">Posts awaiting approval</p>
            </div>
            <a href="/admin/posts?status=draft" class="text-xs text-brand-600 hover:underline">View All</a>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-gray-700/40">
            @forelse($recentPosts->where('status', 'draft')->take(5) as $post)
            <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $post->title }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $post->author?->name }} · {{ $post->created_at->diffForHumans() }}</p>
                </div>
                <a href="/dashboard/posts/{{ $post->id }}/edit" class="text-xs text-brand-600 hover:underline ml-3">Edit</a>
            </div>
            @empty
            <p class="px-5 py-4 text-sm text-gray-400 dark:text-gray-500 text-center">No pending posts.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-700/60">
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-sm">Recent Comments</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500">Latest activity</p>
            </div>
            <a href="/admin/comments" class="text-xs text-brand-600 hover:underline">View All</a>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-gray-700/40">
            @forelse($recentComments ?? [] as $comment)
            <div class="px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <div class="flex items-start gap-2">
                    <div class="w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-300 flex-shrink-0">
                        {{ strtoupper(substr($comment->displayName(), 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $comment->displayName() }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $comment->body }}</p>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @empty
            <p class="px-5 py-4 text-sm text-gray-400 dark:text-gray-500 text-center">No comments yet.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Recent Users --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-700/60">
        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Recently Registered Users</h3>
        <a href="/admin/users" class="text-xs text-brand-600 hover:underline">View All</a>
    </div>
    <div class="px-5 py-3 flex items-center gap-3 flex-wrap">
        @foreach($recentUsers as $u)
        <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700/50 rounded-full pl-1 pr-3 py-1">
            <div class="w-7 h-7 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold">
                {{ strtoupper(substr($u->name, 0, 1)) }}
            </div>
            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $u->name }}</span>
            <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $u->created_at->diffForHumans() }}</span>
        </div>
        @endforeach
    </div>
</div>


@push('scripts')
<script>
const labels = @json($chartLabels ?? []);
const data   = @json($chartData ?? []);
const isDark = document.getElementById('html-root').classList.contains('dark');
const gridColor  = isDark ? 'rgba(255,255,255,.06)' : '#f3f4f6';
const tickColor  = isDark ? '#4a6080' : '#9ca3af';

const viewChartInstance = new Chart(document.getElementById('viewChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Views',
            data: data,
            borderColor: '#6366f1',
            backgroundColor: isDark ? 'rgba(99,102,241,.12)' : 'rgba(99,102,241,.08)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointRadius: 3,
            pointBackgroundColor: '#6366f1',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 }, color: tickColor, maxTicksLimit: 8 } },
            y: { grid: { color: gridColor }, ticks: { font: { size: 10 }, color: tickColor, beginAtZero: true } }
        }
    }
});

// Update chart colors on theme toggle
document.getElementById('html-root').addEventListener('classChange', () => {
    const dark = document.getElementById('html-root').classList.contains('dark');
    viewChartInstance.options.scales.y.grid.color = dark ? 'rgba(255,255,255,.06)' : '#f3f4f6';
    viewChartInstance.options.scales.x.ticks.color = dark ? '#4a6080' : '#9ca3af';
    viewChartInstance.options.scales.y.ticks.color = dark ? '#4a6080' : '#9ca3af';
    viewChartInstance.update();
});
</script>
@endpush
@endsection
