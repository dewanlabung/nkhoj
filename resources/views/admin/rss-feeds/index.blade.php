@extends('layouts.admin')
@section('title', 'RSS Feeds')

@push('head')
<style>
.opt-menu { position:fixed; min-width:128px; background:#1f2937; border:1px solid rgba(255,255,255,.1); border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.4); z-index:9999; overflow:hidden; }
html:not(.dark) .opt-menu { background:#fff; border-color:#e5e7eb; box-shadow:0 4px 16px rgba(0,0,0,.12); }
.opt-menu a,.opt-menu button { display:flex; align-items:center; gap:8px; width:100%; padding:9px 14px; font-size:13px; color:#d1d5db; text-decoration:none; background:none; border:none; cursor:pointer; text-align:left; transition:background .12s; }
html:not(.dark) .opt-menu a, html:not(.dark) .opt-menu button { color:#374151; }
.opt-menu button:hover,.opt-menu a:hover { background:rgba(255,255,255,.07); }
html:not(.dark) .opt-menu button:hover, html:not(.dark) .opt-menu a:hover { background:#f3f4f6; }
.opt-menu .del { color:#f87171; }
</style>
@endpush

@section('content')
<div x-data="rssFeedsApp()" @click="openId=null">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">RSS Feeds</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                <a href="/admin" class="hover:text-brand-500">Home</a>
                <span class="mx-1.5 text-gray-300 dark:text-gray-600">›</span>
                RSS Feeds
            </p>
        </div>
        <a href="/admin/rss-feeds/create"
            class="flex items-center gap-1.5 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Add Feed
        </a>
    </div>

    @if(session('success'))
    <div class="mb-5 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm rounded-xl border border-green-100 dark:border-green-800/40">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-5 px-4 py-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm rounded-xl border border-red-100 dark:border-red-800/40">{{ session('error') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700">
                    <th class="text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-5 py-3.5 w-10">ID</th>
                    <th class="text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-5 py-3.5">Feed Name</th>
                    <th class="text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-5 py-3.5">Feed URL</th>
                    <th class="text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-5 py-3.5 w-24">Language</th>
                    <th class="text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-5 py-3.5 w-28">Category</th>
                    <th class="text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-5 py-3.5 w-20">Posts</th>
                    <th class="text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-5 py-3.5 w-32">Date Added</th>
                    <th class="text-right text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-5 py-3.5 w-24">Options</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                @forelse($feeds as $feed)
                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/20 transition-colors">
                    <td class="px-5 py-3.5 text-sm text-gray-400">{{ $feed->id }}</td>
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $feed->name }}</p>
                        @if($feed->auto_update)
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/30 px-2 py-0.5 rounded-full mt-0.5">
                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Auto Update
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono truncate max-w-[220px]">{{ $feed->url }}</p>
                        <form method="POST" action="/admin/rss-feeds/{{ $feed->id }}/import" class="inline mt-1">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 text-[11px] font-medium text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 hover:bg-brand-100 dark:hover:bg-brand-900/40 border border-brand-200 dark:border-brand-800/40 px-2 py-0.5 rounded-full transition-colors">
                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Import Posts
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="text-sm text-gray-700 dark:text-gray-300 uppercase">{{ $feed->language }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $feed->category?->name_en ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $feed->post_count }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-400">{{ $feed->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-5 py-3.5 text-right" @click.stop>
                        <button @click.stop="toggleMenu($event, {{ $feed->id }})"
                            class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                            Select
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="opt-menu" x-show="openId === {{ $feed->id }}" x-cloak :style="menuPos" @click.stop>
                            <a href="/admin/rss-feeds/{{ $feed->id }}/edit">
                                <svg class="w-3.5 h-3.5 fill-blue-400" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 000-1.41l-2.34-2.34a1 1 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                Edit
                            </a>
                            <form method="POST" action="/admin/rss-feeds/{{ $feed->id }}" onsubmit="return confirm('Delete this feed?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="del">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="w-10 h-10 text-gray-200 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                            <p class="text-sm text-gray-400 dark:text-gray-500">No RSS feeds yet.</p>
                            <a href="/admin/rss-feeds/create" class="text-sm text-brand-500 hover:text-brand-600 font-medium">+ Add your first feed</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($feeds->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700">
            {{ $feeds->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function rssFeedsApp() {
    return {
        openId: null, menuPos: '',
        toggleMenu(e, id) {
            if (this.openId === id) { this.openId = null; return; }
            const r = e.currentTarget.getBoundingClientRect();
            this.menuPos = `top:${r.bottom+4}px;right:${window.innerWidth-r.right}px`;
            this.openId = id;
        }
    };
}
</script>
@endpush

@endsection
