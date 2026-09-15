@extends('layouts.admin')
@section('title', 'Error Logs')

@section('content')
<div class="max-w-6xl">
    <nav class="text-xs text-gray-400 mb-4 flex items-center gap-1.5">
        <a href="/admin" class="hover:text-brand-500">Home</a>
        <span>›</span><span>Error Logs</span>
    </nav>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Error Logs</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Search, filter, and monitor application errors</p>
        </div>

        <div class="p-6 space-y-6">
            <!-- File & Level Selector -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Log File</label>
                    <select onchange="window.location.href='?file=' + this.value + '&page=1'" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Select File --</option>
                        @foreach($files as $file)
                        <option value="{{ $file->name }}" {{ $selected === $file->name ? 'selected' : '' }}>
                            {{ $file->name }} ({{ $file->size }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Level</label>
                    <select onchange="window.location.href='?file={{ $selected }}&level=' + this.value + '&page=1'" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="all" {{ $level === 'all' ? 'selected' : '' }}>All Levels</option>
                        <option value="error" {{ $level === 'error' ? 'selected' : '' }}>Error</option>
                        <option value="warning" {{ $level === 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="info" {{ $level === 'info' ? 'selected' : '' }}>Info</option>
                        <option value="debug" {{ $level === 'debug' ? 'selected' : '' }}>Debug</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Search</label>
                    <form method="GET" class="flex gap-2">
                        <input type="hidden" name="file" value="{{ $selected }}">
                        <input type="hidden" name="level" value="{{ $level }}">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search logs..."
                            class="flex-1 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                            Search
                        </button>
                    </form>
                </div>
            </div>

            @if($selected && $logs)
            <!-- Actions -->
            <div class="flex gap-3">
                <a href="/admin/error-logs/download?file={{ $selected }}" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download
                </a>
                <form method="POST" action="/admin/error-logs/clear" class="inline" onsubmit="return confirm('Clear all logs in this file? This cannot be undone.');">
                    @csrf
                    <input type="hidden" name="file" value="{{ $selected }}">
                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Clear
                    </button>
                </form>
            </div>

            <!-- Logs Table -->
            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">Time</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">Level</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">Message</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap text-xs">
                                {{ $log->datetime?->format('M d, H:i:s') ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full
                                    @if(strtolower($log->level) === 'error') bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400
                                    @elseif(strtolower($log->level) === 'warning') bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400
                                    @elseif(strtolower($log->level) === 'info') bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400
                                    @else bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400
                                    @endif">
                                    {{ strtoupper($log->level) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-2xl overflow-hidden text-ellipsis" title="{{ $log->text }}">
                                {{ Str::limit($log->text, 150) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No logs found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($pagination && $pagination['last_page'] > 1)
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Showing <span class="font-semibold">{{ $pagination['from'] }}</span> to <span class="font-semibold">{{ $pagination['to'] }}</span> of <span class="font-semibold">{{ $pagination['total'] }}</span> logs
                </div>
                <div class="flex gap-2">
                    @if($pagination['current_page'] > 1)
                    <a href="?file={{ $selected }}&level={{ $level }}&search={{ urlencode($search) }}&page={{ $pagination['current_page'] - 1 }}"
                        class="px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        ← Previous
                    </a>
                    @endif

                    @for($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++)
                    <a href="?file={{ $selected }}&level={{ $level }}&search={{ urlencode($search) }}&page={{ $i }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $i === $pagination['current_page'] ? 'bg-brand-500 text-white' : 'border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        {{ $i }}
                    </a>
                    @endfor

                    @if($pagination['current_page'] < $pagination['last_page'])
                    <a href="?file={{ $selected }}&level={{ $level }}&search={{ urlencode($search) }}&page={{ $pagination['current_page'] + 1 }}"
                        class="px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Next →
                    </a>
                    @endif
                </div>
            </div>
            @endif

            @elseif($selected)
            <div class="py-12 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p>No logs in this file</p>
            </div>
            @else
            <div class="py-12 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m0 0h6"/>
                </svg>
                <p>Select a log file to view</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
