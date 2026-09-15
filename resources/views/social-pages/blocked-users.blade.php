@extends('layouts.app')
@section('title', 'Blocked Users — ' . $page->name)
@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
        <a href="/pages/{{ $page->slug }}/dashboard" class="p-2 -ml-2 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1">
            <h1 class="font-bold text-gray-900 text-lg">Blocked Users</h1>
            <p class="text-xs text-gray-400">Blocked users cannot follow or comment on {{ $page->name }}</p>
        </div>
    </div>

    <div class="max-w-xl mx-auto px-4 py-6 space-y-4">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Search to block a user --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden" x-data="{ query: '', results: [], loading: false }"
             @keyup.debounce.400ms="if(query.length > 1){ loading=true; fetch('/search/users?q='+encodeURIComponent(query)).then(r=>r.json()).then(d=>{ results=d; loading=false; }).catch(()=>{ loading=false; }) } else { results=[]; }">
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="font-bold text-gray-900">Block a user</p>
                <p class="text-xs text-gray-400 mt-0.5">Search by name or @username</p>
            </div>
            <div class="p-4">
                <input type="text" x-model="query" placeholder="Search users..."
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-red-400">
                <div x-show="results.length" class="mt-2 space-y-1 border border-gray-200 rounded-xl overflow-hidden">
                    <template x-for="u in results" :key="u.id">
                        <div class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50">
                            <img :src="u.avatar || 'https://ui-avatars.com/api/?name='+encodeURIComponent(u.name)+'&size=32&background=e5e7eb&color=6b7280'"
                                class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900" x-text="u.name"></p>
                                <p class="text-xs text-gray-400" x-text="'@' + (u.username || u.id)"></p>
                            </div>
                            <form :action="'/pages/{{ $page->slug }}/block/' + u.id" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-lg transition">Block</button>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Current blocked list --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="font-bold text-gray-900">Currently blocked ({{ $blocks->total() }})</p>
            </div>
            @forelse($blocks as $block)
            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 last:border-0">
                <img src="{{ $block->blockedUser->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($block->blockedUser->name ?? 'U').'&size=40&background=e5e7eb&color=6b7280' }}"
                    class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900">{{ $block->blockedUser->name }}</p>
                    <p class="text-xs text-gray-400">Blocked {{ $block->created_at->diffForHumans() }}</p>
                </div>
                <form action="/pages/{{ $page->slug }}/block/{{ $block->blocked_user_id }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-blue-600 font-semibold hover:underline">Unblock</button>
                </form>
            </div>
            @empty
            <div class="px-4 py-10 text-center text-gray-400 text-sm">No users blocked.</div>
            @endforelse
        </div>
        @if($blocks->hasPages()) <div class="mt-2">{{ $blocks->links() }}</div> @endif
    </div>
</div>
@endsection
