@extends('layouts.admin')
@section('title', 'Settings Overview')

@section('content')
<div class="mb-6">
    <nav class="text-xs text-gray-400 flex items-center gap-1.5 mb-1">
        <a href="/admin" class="hover:text-brand-500">Home</a><span>›</span><span>Settings</span>
    </nav>
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Settings Overview</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">All configuration areas in one place.</p>
</div>

@php
$groups = [
    [
        'label' => 'Site & Brand',
        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>',
        'color' => 'blue',
        'items' => [
            ['url' => '/admin/settings',          'label' => 'Global Settings',     'desc' => 'Site name, logos, favicon, social links, analytics'],
            ['url' => '/admin/localized-settings','label' => 'Localized Settings',  'desc' => 'Region-specific content, cookie notices, contact info'],
            ['url' => '/admin/languages',         'label' => 'Language Settings',   'desc' => 'Interface languages available to users'],
        ],
    ],
    [
        'label' => 'Content & Features',
        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>',
        'color' => 'purple',
        'items' => [
            ['url' => '/admin/content-settings', 'label' => 'Content Settings', 'desc' => 'Post types, approval, comments, guest access, breaking news'],
            ['url' => '/admin/ai-content',       'label' => 'AI Content',       'desc' => 'Gemini API, AI post generation, scheduling'],
        ],
    ],
    [
        'label' => 'SEO & Discovery',
        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
        'color' => 'green',
        'items' => [
            ['url' => '/admin/seo',         'label' => 'SEO Tools',    'desc' => 'Meta tags, Open Graph, structured data, sitemap settings'],
            ['url' => '/admin/google-news', 'label' => 'Google News',  'desc' => 'Google News sitemap, publication settings'],
            ['url' => '/admin/rss-feeds',   'label' => 'RSS Feeds',    'desc' => 'Manage RSS/Atom feed sources and aggregation'],
        ],
    ],
    [
        'label' => 'Email & Notifications',
        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
        'color' => 'orange',
        'items' => [
            ['url' => '/admin/email-settings', 'label' => 'Email Settings', 'desc' => 'SMTP configuration, email templates, test delivery'],
            ['url' => '/admin/newsletter',     'label' => 'Newsletter',     'desc' => 'Subscriber list and newsletter broadcast'],
        ],
    ],
    [
        'label' => 'Security',
        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        'color' => 'red',
        'items' => [
            ['url' => '/admin/security', 'label' => 'Security Settings', 'desc' => 'Login protection, rate limits, 2FA, CAPTCHA'],
        ],
    ],
    [
        'label' => 'Infrastructure',
        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>',
        'color' => 'gray',
        'items' => [
            ['url' => '/admin/storage',       'label' => 'Storage',        'desc' => 'File storage driver, disk usage, media paths'],
            ['url' => '/admin/cache',         'label' => 'Cache System',   'desc' => 'Clear application, view, and route caches'],
            ['url' => '/admin/queue-settings','label' => 'Queue Settings', 'desc' => 'Job queue driver, failed jobs, scheduling'],
            ['url' => '/admin/backup',        'label' => 'Database Backup','desc' => 'Download a backup of your database'],
            ['url' => '/admin/deploy',        'label' => 'Deploy',         'desc' => 'Pull latest code and run post-deploy steps'],
        ],
    ],
];

$colorMap = [
    'blue'   => ['ring' => 'ring-blue-100 dark:ring-blue-900/30',   'bg' => 'bg-blue-50 dark:bg-blue-900/20',   'icon' => 'text-blue-600 dark:text-blue-400',   'head' => 'text-blue-700 dark:text-blue-300'],
    'purple' => ['ring' => 'ring-purple-100 dark:ring-purple-900/30','bg' => 'bg-purple-50 dark:bg-purple-900/20','icon' => 'text-purple-600 dark:text-purple-400','head' => 'text-purple-700 dark:text-purple-300'],
    'green'  => ['ring' => 'ring-green-100 dark:ring-green-900/30',  'bg' => 'bg-green-50 dark:bg-green-900/20',  'icon' => 'text-green-600 dark:text-green-400',  'head' => 'text-green-700 dark:text-green-300'],
    'orange' => ['ring' => 'ring-orange-100 dark:ring-orange-900/30','bg' => 'bg-orange-50 dark:bg-orange-900/20','icon' => 'text-orange-600 dark:text-orange-400','head' => 'text-orange-700 dark:text-orange-300'],
    'red'    => ['ring' => 'ring-red-100 dark:ring-red-900/30',      'bg' => 'bg-red-50 dark:bg-red-900/20',      'icon' => 'text-red-600 dark:text-red-400',      'head' => 'text-red-700 dark:text-red-300'],
    'gray'   => ['ring' => 'ring-gray-100 dark:ring-gray-700',       'bg' => 'bg-gray-50 dark:bg-gray-800',       'icon' => 'text-gray-500 dark:text-gray-400',    'head' => 'text-gray-700 dark:text-gray-300'],
];
@endphp

<div class="space-y-6">
    @foreach($groups as $group)
    @php $c = $colorMap[$group['color']]; @endphp
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl overflow-hidden">
        {{-- Group header --}}
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="w-8 h-8 rounded-lg {{ $c['bg'] }} {{ $c['ring'] }} ring-1 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 {{ $c['icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    {!! $group['icon'] !!}
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">{{ $group['label'] }}</h2>
        </div>
        {{-- Items --}}
        <div class="divide-y divide-gray-50 dark:divide-gray-700/60">
            @foreach($group['items'] as $item)
            <a href="{{ $item['url'] }}"
               class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ $item['label'] }}</p>
                    <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">{{ $item['desc'] }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-brand-500 flex-shrink-0 ml-4 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
