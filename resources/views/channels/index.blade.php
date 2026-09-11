@extends('layouts.app')
@section('title', 'Channels — नखोज')
@section('content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">📢 Broadcast Channels</h1>
        <p class="text-sm text-gray-400 mt-0.5">Follow channels for one-way updates from creators</p>
    </div>
    @auth
    <a href="/channels/create" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors">
        + Create Channel
    </a>
    @endauth
</div>

@if($myChannels->count())
<h2 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-3">My Channels</h2>
<div class="flex gap-3 overflow-x-auto pb-2 mb-6 scrollbar-hide">
    @foreach($myChannels as $ch)
    <a href="/channels/{{ $ch->slug }}" class="flex-shrink-0 flex flex-col items-center gap-1 w-20">
        <div class="w-14 h-14 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold text-xl">
            {{ strtoupper(substr($ch->name, 0, 1)) }}
        </div>
        <span class="text-xs text-gray-600 dark:text-gray-400 truncate w-full text-center">{{ $ch->name }}</span>
    </a>
    @endforeach
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($channels as $channel)
    <a href="/channels/{{ $channel->slug }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 hover:shadow-md transition-shadow group">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 font-bold text-xl flex-shrink-0">
                {{ strtoupper(substr($channel->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-900 dark:text-white truncate group-hover:text-brand-500 transition-colors">{{ $channel->name }}</p>
                <p class="text-xs text-gray-500">by {{ $channel->owner->name }}</p>
            </div>
        </div>
        @if($channel->description)
        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">{{ $channel->description }}</p>
        @endif
        <div class="flex items-center justify-between">
            <span class="text-xs text-gray-400">{{ number_format($channel->subscriber_count) }} subscribers</span>
        </div>
    </a>
    @empty
    <div class="col-span-full text-center py-12 text-gray-400">
        <div class="text-4xl mb-2">📡</div>
        <p>No channels yet.</p>
    </div>
    @endforelse
</div>
{{ $channels->links() }}
@endsection
