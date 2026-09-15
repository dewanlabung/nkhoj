@extends('layouts.app')
@section('title', 'Watch Parties — नखोज')
@section('content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">🎉 Watch Parties</h1>
        <p class="text-sm text-gray-400 mt-0.5">Watch together, chat together</p>
    </div>
    @auth
    <a href="/watch-party/create" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors">
        + Create Party
    </a>
    @endauth
</div>

@if($active->count())
<h2 class="text-sm font-bold text-green-600 dark:text-green-400 uppercase tracking-widest mb-3 flex items-center gap-2">
    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Active Now
</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @foreach($active as $party)
    <a href="/watch-party/{{ $party->id }}" class="group block bg-white dark:bg-gray-800 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
        <div class="relative aspect-video bg-gray-900 flex items-center justify-center">
            <div class="text-5xl">🎬</div>
            <span class="absolute top-2 left-2 bg-green-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">LIVE</span>
            <span class="absolute top-2 right-2 bg-black/70 text-white text-xs px-2 py-0.5 rounded-full flex items-center gap-1">
                🔑 {{ $party->join_code }}
            </span>
        </div>
        <div class="p-3">
            <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ $party->title }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $party->host->name }} · {{ $party->members->count() }} watching</p>
        </div>
    </a>
    @endforeach
</div>
@else
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-12 text-center mb-8">
    <div class="text-4xl mb-3">🍿</div>
    <p class="text-gray-500 dark:text-gray-400">No active watch parties right now.</p>
    @auth
    <a href="/watch-party/create" class="mt-4 inline-block px-5 py-2 bg-brand-500 text-white text-sm font-semibold rounded-xl hover:bg-brand-600 transition-colors">Start a watch party</a>
    @endauth
</div>
@endif

@if($past->count())
<h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-3">Recent Parties</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach($past as $party)
    <a href="/watch-party/{{ $party->id }}" class="block bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
        <div class="aspect-video bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
            <div class="text-3xl opacity-40">🎬</div>
        </div>
        <div class="p-3">
            <p class="font-medium text-gray-900 dark:text-white text-sm truncate">{{ $party->title }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $party->host->name }} · {{ $party->ended_at?->diffForHumans() }}</p>
        </div>
    </a>
    @endforeach
</div>
@endif
@endsection
