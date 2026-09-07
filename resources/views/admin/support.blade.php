@extends('layouts.admin')
@section('title', 'Support Tickets')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    {{-- Header --}}
    <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h3 class="font-bold text-gray-900 dark:text-white">Support Tickets</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ $tickets->total() }} tickets total</p>
        </div>
        <div class="flex items-center gap-2">
            @if($open > 0)
            <span class="text-xs font-bold bg-red-50 text-red-600 px-3 py-1 rounded-full">{{ $open }} open</span>
            @endif
        </div>
    </div>

    {{-- Status filter tabs --}}
    <div class="px-5 pt-3 flex gap-1.5 flex-wrap border-b border-gray-50 dark:border-gray-700 pb-3">
        @foreach([
            'all'         => 'All',
            'open'        => 'Open',
            'in_progress' => 'In Progress',
            'pending'     => 'Pending',
            'solved'      => 'Solved',
            'closed'      => 'Closed',
        ] as $key => $label)
        <a href="/admin/support?status={{ $key }}"
            class="text-xs px-3 py-1.5 rounded-lg font-medium transition-colors
                {{ ($currentStatus ?? 'all') === $key
                    ? 'bg-brand-500 text-white'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
            {{ $label }}
            <span class="ml-1 opacity-70">{{ $counts[$key] ?? 0 }}</span>
        </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-50 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">#ID</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Requester</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Subject</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Agent</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Status</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Date</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                @forelse($tickets as $ticket)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="px-5 py-3.5 text-xs font-mono text-gray-400 dark:text-gray-500">#{{ $ticket->id }}</td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-300 flex-shrink-0">
                                {{ strtoupper(substr($ticket->requester->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $ticket->requester->name }}</p>
                                <p class="text-[10px] text-gray-400">{{ $ticket->requester->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <a href="/admin/support/{{ $ticket->id }}" class="text-sm font-medium text-gray-800 dark:text-gray-200 hover:text-brand-600 line-clamp-1">
                            {{ $ticket->subject }}
                        </a>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $ticket->replies_count }} {{ Str::plural('reply', $ticket->replies_count) }} · {{ ucfirst($ticket->priority) }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-500 dark:text-gray-400">
                        {{ $ticket->agent?->name ?? '—' }}
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $ticket->statusBadge() }}">
                            {{ $ticket->statusLabel() }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                        {{ $ticket->created_at->diffForHumans() }}
                    </td>
                    <td class="px-5 py-3.5">
                        <a href="/admin/support/{{ $ticket->id }}" class="text-xs px-3 py-1.5 bg-brand-50 text-brand-600 hover:bg-brand-100 rounded-lg font-medium transition-colors">
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-16 text-gray-400 dark:text-gray-500">
                        <p class="text-3xl mb-3">🎫</p>
                        <p>No support tickets yet.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-50 dark:border-gray-700">{{ $tickets->links() }}</div>
</div>
@endsection
