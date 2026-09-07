@extends('layouts.app')
@section('title', '#' . $ticket->id . ' — ' . $ticket->subject)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-4 flex items-center justify-between">
        <a href="/support" class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-brand-600">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Support Center
        </a>
        @if($ticket->requester_id === auth()->id() && !in_array($ticket->status, ['closed', 'solved']))
        <form method="POST" action="/support/{{ $ticket->id }}/close">
            @csrf
            <button class="text-xs text-gray-500 hover:text-red-600 border border-gray-200 dark:border-gray-600 px-3 py-1.5 rounded-lg transition-colors">
                Close Ticket
            </button>
        </form>
        @endif
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
        ✅ {{ session('success') }}
    </div>
    @endif

    {{-- Ticket header --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 mb-4">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-mono text-gray-400 dark:text-gray-500">#{{ $ticket->id }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $ticket->statusBadge() }}">
                        {{ $ticket->statusLabel() }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $ticket->priority === 'urgent' ? 'bg-red-100 text-red-700' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </div>
                <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ $ticket->subject }}</h1>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    Opened by <span class="font-medium">{{ $ticket->requester->name }}</span> · {{ $ticket->created_at->diffForHumans() }}
                    @if($ticket->agent)
                    · Agent: <span class="font-medium text-brand-600">{{ $ticket->agent->name }}</span>
                    @else
                    · <span class="text-gray-400">Unassigned</span>
                    @endif
                </p>
            </div>
        </div>

        {{-- Original message --}}
        <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($ticket->requester->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $ticket->requester->name }}</span>
                        <span class="text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $ticket->body }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Replies --}}
    @foreach($ticket->replies as $reply)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border {{ $reply->is_staff ? 'border-brand-200 dark:border-brand-700/50 bg-brand-50/30 dark:bg-brand-900/10' : 'border-gray-100 dark:border-gray-700' }} shadow-sm p-5 mb-3">
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

    {{-- Reply form --}}
    @if(!in_array($ticket->status, ['closed']))
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 mt-4">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Add a Reply</h3>
        <form method="POST" action="/support/{{ $ticket->id }}/reply">
            @csrf
            <textarea name="body" rows="5" required maxlength="5000"
                placeholder="Write your reply..."
                class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none mb-3"></textarea>
            <button type="submit"
                class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl transition-colors text-sm">
                Send Reply
            </button>
        </form>
    </div>
    @else
    <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm">
        This ticket is closed. <a href="/support/create" class="text-brand-600 hover:underline">Open a new ticket</a> if you need further help.
    </div>
    @endif
</div>
@endsection
