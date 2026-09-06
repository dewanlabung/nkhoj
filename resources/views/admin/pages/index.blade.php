@extends('layouts.admin')
@section('title', 'Pages')

@section('content')

{{-- Header bar --}}
<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-2 flex-wrap">
        {{-- Filter badges --}}
        <a href="/admin/pages" class="px-3 py-1.5 text-xs font-semibold rounded-lg {{ !request('status') && !request('type') ? 'bg-brand-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }} transition-colors">All</a>
        <a href="/admin/pages?status=active" class="px-3 py-1.5 text-xs font-semibold rounded-lg {{ request('status') === 'active' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }} transition-colors">Active</a>
        <a href="/admin/pages?status=inactive" class="px-3 py-1.5 text-xs font-semibold rounded-lg {{ request('status') === 'inactive' ? 'bg-gray-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }} transition-colors">Inactive</a>
        <a href="/admin/pages?type=custom" class="px-3 py-1.5 text-xs font-semibold rounded-lg {{ request('type') === 'custom' ? 'bg-indigo-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }} transition-colors">Custom</a>
        <a href="/admin/pages?type=default" class="px-3 py-1.5 text-xs font-semibold rounded-lg {{ request('type') === 'default' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }} transition-colors">Default</a>
    </div>
    <a href="/admin/pages/create"
        class="flex items-center gap-1.5 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Page
    </a>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-2.5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm rounded-xl">
    {{ session('success') }}
</div>
@endif

{{-- Table --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-100 dark:border-gray-600">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide w-14">ID</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">Title</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">Status</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide hidden md:table-cell">Page Type</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide hidden lg:table-cell">Date Added</th>
                <th class="px-5 py-3 text-xs uppercase tracking-wide text-right text-gray-500 dark:text-gray-400">Options</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
            @forelse($pages as $page)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <td class="px-5 py-3 text-xs text-gray-400">{{ $page->id }}</td>
                <td class="px-5 py-3">
                    <p class="font-medium text-gray-900 dark:text-white">{{ $page->title }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        {{ $page->menu_position ? ucwords(str_replace('_', ' ', $page->menu_position)) : 'No Menu' }}
                        · {{ strtoupper($page->language) }}
                    </p>
                </td>
                <td class="px-5 py-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                        {{ $page->status === 'active' ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                        {{ ucfirst($page->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 hidden md:table-cell">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                        {{ $page->page_type === 'custom' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' }}">
                        {{ ucfirst($page->page_type) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">{{ $page->created_at->format('Y-m-d H:i') }}</td>
                <td class="px-5 py-3 text-right">
                    <div x-data="{ open: false, top:0, right:0, toggle(btn){ const r=btn.getBoundingClientRect(); this.top=r.bottom+4; this.right=window.innerWidth-r.right; this.open=!this.open; } }"
                        class="inline-block" @click.outside="open=false">
                        <button @click="toggle($el)"
                            class="flex items-center gap-1 px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium">
                            Select
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak
                            class="fixed w-36 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-2xl z-[999] overflow-hidden"
                            :style="`top:${top}px; right:${right}px`">
                            <a href="/admin/pages/{{ $page->id }}/edit"
                                class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                            <a href="/pages/{{ $page->slug }}" target="_blank"
                                class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                View
                            </a>
                            <form method="POST" action="/admin/pages/{{ $page->id }}" onsubmit="return confirm('Delete this page?')">
                                @csrf @method('DELETE')
                                <button class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-12 text-gray-400 dark:text-gray-500">
                <p class="text-3xl mb-2">📄</p>
                <p>No pages yet. <a href="/admin/pages/create" class="text-brand-600 hover:underline">Create your first page</a>.</p>
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($pages->hasPages())
<div class="mt-4">{{ $pages->links() }}</div>
@endif

@endsection
