@extends('layouts.admin')
@section('title', 'Queue Settings')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Queue Settings</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
        <a href="/admin" class="hover:text-brand-500">Home</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">›</span>
        Queue Settings
    </p>
</div>

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm rounded-xl border border-green-100 dark:border-green-800/40">
    ✓ {{ session('success') }}
</div>
@endif

@php
$current = $s['queue']['connection'] ?? config('queue.default', 'sync');
$drivers = [
    'sync'       => ['label' => 'None (Sync)',  'desc' => 'Jobs run immediately, inline. No worker needed. Slowest for end-users.'],
    'database'   => ['label' => 'Database',     'desc' => 'Jobs stored in the `jobs` table. Run `php artisan queue:work` or a cPanel cron. Best for shared hosting.'],
    'redis'      => ['label' => 'Redis',        'desc' => 'Requires Redis on the server. Fast and reliable for high-traffic sites.'],
    'beanstalkd' => ['label' => 'Beanstalkd',  'desc' => 'Requires the Beanstalkd daemon and the `pda/pheanstalk` package.'],
    'sqs'        => ['label' => 'Amazon SQS',   'desc' => 'Requires AWS credentials (SQS_KEY, SQS_SECRET, SQS_QUEUE, SQS_REGION).'],
];
@endphp

<div class="max-w-xl">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-1">Queue Method</h2>
        <p class="text-sm text-gray-400 mb-6">
            Queues defer time-consuming tasks — sending emails, notifications, data exports — so users don't wait.
            The setting is saved to <code class="text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">storage/app/site_settings.json</code>
            and takes effect on the next request without a server restart.
        </p>

        <form method="POST" action="/admin/queue-settings" x-data="{ picked: '{{ $current }}' }">
            @csrf

            <div class="space-y-3 mb-6">
                @foreach($drivers as $value => $info)
                <label class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition-colors"
                    :class="picked === '{{ $value }}'
                        ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20'
                        : 'border-gray-200 dark:border-gray-700 hover:border-brand-300 dark:hover:border-brand-600'">
                    <input type="radio" name="queue_connection" value="{{ $value }}"
                        x-model="picked"
                        class="mt-0.5 accent-brand-600 flex-shrink-0">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            {{ $info['label'] }}
                            @if($value === $current)
                            <span class="px-2 py-0.5 text-xs font-semibold bg-green-100 dark:bg-green-800/50 text-green-700 dark:text-green-300 rounded-full">Active</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $info['desc'] }}</p>
                    </div>
                </label>
                @endforeach
            </div>

            <button type="submit"
                class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-colors">
                Save Queue Setting
            </button>
        </form>
    </div>

    {{-- Database queue setup guide --}}
    <div class="mt-4 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-100 dark:border-blue-800/40 p-5">
        <h3 class="text-sm font-bold text-blue-800 dark:text-blue-300 mb-2">Using the Database driver on cPanel?</h3>
        <ol class="text-xs text-blue-700 dark:text-blue-400 space-y-1.5 list-decimal list-inside">
            <li>Select <strong>Database</strong> above and click Save.</li>
            <li>Run <code class="bg-blue-100 dark:bg-blue-900/40 px-1 rounded">php artisan migrate</code> to create the <code>jobs</code> table (already done if you ran the latest migrations).</li>
            <li>Add a cPanel Cron Job: <br>
                <code class="block mt-1 bg-blue-100 dark:bg-blue-900/40 px-2 py-1 rounded text-xs break-all">* * * * * cd /home/youruser/public_html && php artisan queue:work --stop-when-empty --tries=3 >> /dev/null 2>&1</code>
            </li>
            <li>Or run the worker manually: <code class="bg-blue-100 dark:bg-blue-900/40 px-1 rounded">php artisan queue:work --tries=3</code></li>
        </ol>
    </div>
</div>
@endsection
