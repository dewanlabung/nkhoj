@extends('layouts.admin')
@section('title', 'System Logs')

@section('content')
<div class="mb-6">
    <nav class="text-xs text-gray-400 flex items-center gap-1.5 mb-1">
        <a href="/admin" class="hover:text-brand-500">Home</a><span>›</span><span>System Logs</span>
    </nav>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">System Logs</h1>
            <p class="text-xs text-gray-400 mt-0.5">CRON schedule history · Laravel error log · Outgoing email log</p>
        </div>
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-2.5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-300">
    {{ session('success') }}
</div>
@endif

@if($errors->has('rerun'))
<div class="mb-4 px-4 py-2.5 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-300">
    {{ $errors->first('rerun') }}
</div>
@endif

{{-- Tab bar --}}
<div x-data="{ tab: '{{ $tab }}' }" class="w-full">
    <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-5">
        @foreach([
            ['schedule', 'Schedule', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['error',    'Error Log', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            ['email',    'Email',     'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ] as [$key, $label, $icon])
        <button @click="tab = '{{ $key }}'; history.replaceState(null,'','/admin/logs?tab={{ $key }}')"
                :class="tab === '{{ $key }}' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                class="flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ── Schedule tab ─────────────────────────────────────────────────── --}}
    <div x-show="tab === 'schedule'" x-cloak>
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs text-gray-400">CRON job run history · grouped by command+exit within the hour</p>
            <a href="/admin/logs/schedule/download" class="text-xs text-brand-500 hover:underline flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export JSON
            </a>
        </div>

        @if($scheduleLogs->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-8 text-center">
            <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-gray-400">No schedule log entries yet. Logs are recorded after the next CRON run.</p>
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 text-left text-xs text-gray-400 uppercase tracking-wide">
                            <th class="px-4 py-3 font-medium">Command</th>
                            <th class="px-4 py-3 font-medium">Exit</th>
                            <th class="px-4 py-3 font-medium">Duration</th>
                            <th class="px-4 py-3 font-medium">Ran At</th>
                            <th class="px-4 py-3 font-medium">Count/hr</th>
                            <th class="px-4 py-3 font-medium">Output</th>
                            <th class="px-4 py-3 font-medium w-20"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach($scheduleLogs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300 max-w-xs truncate" title="{{ $log->command }}">
                                {{ $log->command }}
                            </td>
                            <td class="px-4 py-3">
                                @if($log->exit_code === 0)
                                <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> OK
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> {{ $log->exit_code }}
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 tabular-nums">
                                @if($log->duration >= 1000)
                                    {{ number_format($log->duration / 1000, 1) }}s
                                @else
                                    {{ $log->duration }}ms
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 tabular-nums whitespace-nowrap">
                                {{ $log->ran_at?->format('d M Y, H:i:s') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-center">
                                @if($log->count_in_last_hour > 1)
                                <span class="bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-[11px] font-medium px-2 py-0.5 rounded-full">×{{ $log->count_in_last_hour }}</span>
                                @else
                                <span class="text-gray-400">1</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 max-w-xs">
                                @if($log->output)
                                <span x-data="{ open: false }">
                                    <button @click="open = !open" class="text-brand-500 hover:underline text-[11px]" x-text="open ? 'hide' : 'show'">show</button>
                                    <pre x-show="open" x-cloak class="mt-1 text-[10px] bg-gray-50 dark:bg-gray-900 rounded p-2 whitespace-pre-wrap break-all max-h-32 overflow-y-auto font-mono">{{ $log->output }}</pre>
                                </span>
                                @else
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="/admin/logs/schedule/{{ $log->id }}/rerun">
                                    @csrf
                                    <button type="submit" class="text-xs text-brand-500 hover:text-brand-700 font-medium"
                                            onclick="return confirm('Re-run this command?')">Re-run</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($scheduleLogs->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                {{ $scheduleLogs->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- ── Error log tab ─────────────────────────────────────────────────── --}}
    <div x-show="tab === 'error'" x-cloak>
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs text-gray-400">Last 200 entries from <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">storage/logs/laravel.log</code> · newest first</p>
        </div>

        @if(empty($errorLogLines))
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-8 text-center">
            <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-gray-400">Log file is empty or does not exist.</p>
        </div>
        @else
        <div class="space-y-2">
            @foreach($errorLogLines as $entry)
            @php
                $isError   = str_contains($entry, '.ERROR') || str_contains($entry, '.CRITICAL') || str_contains($entry, '.ALERT') || str_contains($entry, '.EMERGENCY');
                $isWarning = str_contains($entry, '.WARNING') || str_contains($entry, '.NOTICE');
                $color     = $isError ? 'border-red-300 dark:border-red-800 bg-red-50/50 dark:bg-red-900/10'
                           : ($isWarning ? 'border-amber-300 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-900/10'
                           : 'border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800');
            @endphp
            <div x-data="{ open: false }" class="rounded-lg border {{ $color }} overflow-hidden">
                @php
                    $firstLine  = strtok($entry, "\n");
                    $rest       = trim(substr($entry, strlen($firstLine)));
                @endphp
                <button @click="open = !open" class="w-full text-left px-4 py-2.5 flex items-start justify-between gap-3 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                    <span class="font-mono text-[11px] text-gray-700 dark:text-gray-300 break-all leading-relaxed">{{ $firstLine }}</span>
                    @if($rest)
                    <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-gray-400 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    @endif
                </button>
                @if($rest)
                <pre x-show="open" x-cloak class="px-4 pb-3 text-[10px] text-gray-600 dark:text-gray-400 font-mono whitespace-pre-wrap break-all max-h-64 overflow-y-auto border-t border-gray-100 dark:border-gray-700 pt-2">{{ $rest }}</pre>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── Email log tab ─────────────────────────────────────────────────── --}}
    <div x-show="tab === 'email'" x-cloak>
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs text-gray-400">Outgoing email log · last 7 days retained</p>
            <a href="/admin/logs/email/download" class="text-xs text-brand-500 hover:underline flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export JSON
            </a>
        </div>

        @if($emailLogs->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-8 text-center">
            <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <p class="text-sm text-gray-400">No outgoing emails logged yet.</p>
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 text-left text-xs text-gray-400 uppercase tracking-wide">
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">To</th>
                            <th class="px-4 py-3 font-medium">Subject</th>
                            <th class="px-4 py-3 font-medium">From</th>
                            <th class="px-4 py-3 font-medium">Sent At</th>
                            <th class="px-4 py-3 font-medium">MIME</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach($emailLogs as $email)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3">
                                @if($email->status === 'sent')
                                <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Sent
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Failed
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300 max-w-[180px] truncate" title="{{ $email->to }}">
                                {{ $email->to }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300 max-w-xs truncate" title="{{ $email->subject }}">
                                {{ $email->subject ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 max-w-[180px] truncate" title="{{ $email->from }}">
                                {{ $email->from ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 tabular-nums whitespace-nowrap">
                                {{ $email->created_at?->format('d M Y, H:i:s') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if($email->makeVisible('mime')->mime)
                                <span x-data="{ open: false }">
                                    <button @click="open = !open" class="text-brand-500 hover:underline text-[11px]" x-text="open ? 'hide' : 'view'">view</button>
                                    <pre x-show="open" x-cloak class="mt-1 text-[10px] bg-gray-50 dark:bg-gray-900 rounded p-2 whitespace-pre-wrap break-all max-h-40 overflow-y-auto font-mono">{{ $email->makeVisible('mime')->mime }}</pre>
                                </span>
                                @else
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($emailLogs->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                {{ $emailLogs->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
