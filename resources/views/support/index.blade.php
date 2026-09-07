@extends('layouts.app')
@section('title', 'Support Center')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Hero --}}
    <div class="bg-gradient-to-br from-brand-500 to-brand-700 rounded-2xl p-8 mb-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-black mb-1">Support Center</h1>
                <p class="text-brand-100 text-sm">Submit a ticket and our team will get back to you.</p>
            </div>
            <a href="/support/create"
                class="inline-flex items-center gap-2 bg-white text-brand-600 font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-brand-50 transition-colors shadow">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Ticket
            </a>
        </div>

        {{-- Status filter chips --}}
        <div class="flex flex-wrap gap-2 mt-6">
            @foreach([
                'all'         => ['label' => 'All',         'key' => 'all'],
                'open'        => ['label' => 'Open',        'key' => 'open'],
                'in_progress' => ['label' => 'In Progress', 'key' => 'in_progress'],
                'pending'     => ['label' => 'Pending',     'key' => 'pending'],
                'solved'      => ['label' => 'Solved',      'key' => 'solved'],
                'closed'      => ['label' => 'Closed',      'key' => 'closed'],
            ] as $key => $item)
            @php $active = ($status ?? 'all') === $key; @endphp
            <a href="/support?status={{ $key }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors
                    {{ $active ? 'bg-white text-brand-700 shadow' : 'bg-brand-600/50 text-brand-100 hover:bg-brand-600' }}">
                {{ $item['label'] }}
                <span class="font-bold">{{ $counts[$key] ?? 0 }}</span>
            </a>
            @endforeach
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
        ✅ {{ session('success') }}
    </div>
    @endif

    {{-- Ticket list --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        @if($tickets->isEmpty())
        <div class="text-center py-20 text-gray-400 dark:text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <p class="font-medium">No tickets found</p>
            <p class="text-sm mt-1">
                <a href="/support/create" class="text-brand-600 hover:underline">Create your first ticket</a>
            </p>
        </div>
        @else
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">#ID</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Subject</th>
                    <th class="hidden sm:table-cell text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Agent</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Status</th>
                    <th class="hidden md:table-cell text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                @foreach($tickets as $ticket)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors cursor-pointer" onclick="location.href='/support/{{ $ticket->id }}'">
                    <td class="px-5 py-4 text-sm text-gray-400 dark:text-gray-500 font-mono">#{{ $ticket->id }}</td>
                    <td class="px-5 py-4">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1">{{ $ticket->subject }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $ticket->replies->count() }} {{ Str::plural('reply', $ticket->replies->count()) }}</p>
                    </td>
                    <td class="hidden sm:table-cell px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $ticket->agent?->name ?? '—' }}
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $ticket->statusBadge() }}">
                            {{ $ticket->statusLabel() }}
                        </span>
                    </td>
                    <td class="hidden md:table-cell px-5 py-4 text-xs text-gray-400 dark:text-gray-500">
                        {{ $ticket->created_at->diffForHumans() }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-50 dark:border-gray-700">{{ $tickets->links() }}</div>
        @endif
    </div>
</div>
@endsection
