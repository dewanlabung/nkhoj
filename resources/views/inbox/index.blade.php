@extends('layouts.app')
@section('title', 'Inbox — नखोज')
@section('content')
<div class="max-w-lg mx-auto">
    <div class="flex items-center justify-between mb-5">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">💬 Inbox</h1>
    </div>

    @if($conversations->count())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden">
        @foreach($conversations as $conv)
        @php $other = $conv->otherUser(auth()->id()); @endphp
        <a href="/inbox/{{ $conv->id }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="w-11 h-11 rounded-full bg-brand-400 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr($other->name ?? '?', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $other->name ?? 'Unknown' }}</span>
                    @if($conv->lastMessage)
                    <span class="text-xs text-gray-400">{{ $conv->lastMessage->created_at->diffForHumans(null, true) }}</span>
                    @endif
                </div>
                @if($conv->lastMessage)
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                    @if($conv->lastMessage->is_view_once)
                        <span class="italic">View once message</span>
                    @elseif($conv->lastMessage->expires_at)
                        <span class="italic">⏱ Disappearing message</span>
                    @else
                        {{ $conv->lastMessage->body }}
                    @endif
                </p>
                @endif
            </div>
            @if($conv->unreadCount(auth()->id()) > 0)
            <span class="w-5 h-5 rounded-full bg-brand-500 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                {{ $conv->unreadCount(auth()->id()) }}
            </span>
            @endif
        </a>
        @endforeach
    </div>
    {{ $conversations->links() }}
    @else
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-16 text-center">
        <div class="text-4xl mb-3">💬</div>
        <p class="text-gray-500 dark:text-gray-400">No messages yet.</p>
        <p class="text-sm text-gray-400 mt-1">Visit someone's profile and hit "Message" to start a conversation.</p>
    </div>
    @endif
</div>
@endsection
