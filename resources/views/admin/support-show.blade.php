@extends('layouts.admin')
@section('title', '#' . $ticket->id . ' — ' . $ticket->subject)

@section('content')
<div class="max-w-4xl">
    <div class="mb-4">
        <a href="/admin/support" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand-600">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Support Tickets
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
        ✅ {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Main conversation --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Original message --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr($ticket->requester->name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $ticket->requester->name }}</span>
                            <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-2 py-0.5 rounded">Requester</span>
                            <span class="text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $ticket->body }}</div>
                    </div>
                </div>
            </div>

            {{-- Replies --}}
            @foreach($ticket->replies as $reply)
            <div class="bg-white dark:bg-gray-800 rounded-xl border {{ $reply->is_staff ? 'border-brand-200 dark:border-brand-700/50' : 'border-gray-100 dark:border-gray-700' }} shadow-sm p-5">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0
                        {{ $reply->is_staff ? 'bg-brand-500 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200' }}">
                        {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $reply->user->name }}</span>
                            @if($reply->is_staff)
                            <span class="text-[10px] font-bold bg-brand-500 text-white px-1.5 py-0.5 rounded">STAFF</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $reply->body }}</div>
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Admin reply form --}}
            @if($ticket->status !== 'closed')
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Staff Reply</h4>
                <form method="POST" action="/admin/support/{{ $ticket->id }}/reply">
                    @csrf
                    <textarea name="body" rows="5" required maxlength="5000"
                        placeholder="Write a reply to the user..."
                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none mb-3"></textarea>
                    <div class="flex gap-2">
                        <button type="submit" name="action" value="reply"
                            class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl transition-colors text-sm">
                            Send Reply
                        </button>
                        <button type="submit" name="action" value="reply_solve"
                            class="px-5 py-2.5 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl transition-colors text-sm">
                            Reply & Mark Solved
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>

        {{-- Sidebar: ticket details --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Ticket Info</h4>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Status</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $ticket->statusBadge() }}">
                            {{ $ticket->statusLabel() }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Priority</p>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ ucfirst($ticket->priority) }}</span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Requester</p>
                        <p class="font-medium text-gray-700 dark:text-gray-300">{{ $ticket->requester->name }}</p>
                        <p class="text-xs text-gray-400">{{ $ticket->requester->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Created</p>
                        <p class="text-gray-600 dark:text-gray-400">{{ $ticket->created_at->format('M d, Y g:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Assigned Agent</p>
                        <form method="POST" action="/admin/support/{{ $ticket->id }}/assign" class="flex gap-2">
                            @csrf
                            <select name="agent_id"
                                class="flex-1 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="">Unassigned</option>
                                @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ $ticket->agent_id == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                Save
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Status actions --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Change Status</h4>
                <form method="POST" action="/admin/support/{{ $ticket->id }}/status" class="space-y-2">
                    @csrf
                    @foreach(['open' => 'Open', 'in_progress' => 'In Progress', 'pending' => 'Pending', 'solved' => 'Solved', 'closed' => 'Closed'] as $s => $label)
                    <button type="submit" name="status" value="{{ $s }}"
                        class="w-full text-left px-3 py-2 text-xs font-medium rounded-lg transition-colors
                            {{ $ticket->status === $s
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400 font-bold'
                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        {{ $label }}
                        @if($ticket->status === $s) ✓ @endif
                    </button>
                    @endforeach
                </form>
            </div>

            {{-- Delete --}}
            <form method="POST" action="/admin/support/{{ $ticket->id }}" onsubmit="return confirm('Delete this ticket and all replies?')">
                @csrf @method('DELETE')
                <button class="w-full text-xs text-red-500 hover:text-red-700 border border-red-200 dark:border-red-800 px-4 py-2.5 rounded-xl transition-colors hover:bg-red-50 dark:hover:bg-red-900/20">
                    Delete Ticket
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
