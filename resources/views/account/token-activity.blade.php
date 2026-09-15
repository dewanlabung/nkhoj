@extends('layouts.account')
@section('title', 'Token Activity')

@section('main')
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
    <div class="flex items-center gap-3 mb-6">
        <a href="/account/tokens" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="font-bold text-gray-900 dark:text-white">Token Activity</h1>
            <p class="text-xs text-gray-400">{{ $token->name }}</p>
        </div>
    </div>

    @if($logs->isEmpty())
    <p class="text-sm text-gray-400 text-center py-8">No activity recorded for this token yet.</p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-semibold text-gray-400 border-b border-gray-100 dark:border-gray-700">
                    <th class="pb-2 pr-4">Method</th>
                    <th class="pb-2 pr-4">Path</th>
                    <th class="pb-2 pr-4">Status</th>
                    <th class="pb-2 pr-4">Duration</th>
                    <th class="pb-2 pr-4">IP</th>
                    <th class="pb-2">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                @foreach($logs as $log)
                <tr class="text-xs text-gray-600 dark:text-gray-400">
                    <td class="py-2 pr-4">
                        <span class="font-mono font-semibold {{ $log->method === 'GET' ? 'text-green-600' : ($log->method === 'DELETE' ? 'text-red-500' : 'text-blue-500') }}">{{ $log->method }}</span>
                    </td>
                    <td class="py-2 pr-4 font-mono truncate max-w-[200px]">{{ $log->path }}</td>
                    <td class="py-2 pr-4">
                        <span class="font-semibold {{ $log->response_status >= 400 ? 'text-red-500' : 'text-green-600' }}">{{ $log->response_status }}</span>
                    </td>
                    <td class="py-2 pr-4 text-gray-400">{{ $log->duration_ms }}ms</td>
                    <td class="py-2 pr-4 font-mono text-gray-400">{{ $log->ip }}</td>
                    <td class="py-2 text-gray-400">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
