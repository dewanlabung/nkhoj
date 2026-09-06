@extends('layouts.admin')
@section('title', 'Widgets')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Add Widget form --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white mb-4">Add Widget</h3>
        <form method="POST" action="/admin/widgets" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Widget Type *</label>
                <select name="type" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    @foreach($types as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Title *</label>
                <input type="text" name="title" required placeholder="e.g. Popular Posts"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Where To Display *</label>
                <select name="where_to_display" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    @foreach($positions as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Order</label>
                <input type="number" name="display_order" value="0" min="0" max="999"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <button class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                + Add Widget
            </button>
        </form>
    </div>

    {{-- Widgets list --}}
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-100 dark:border-gray-600">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wide">ID</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wide">Title</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wide hidden md:table-cell">Where To Display</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wide hidden lg:table-cell">Order</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wide">Status</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wide hidden md:table-cell">Date Added</th>
                        <th class="px-5 py-3 text-xs uppercase tracking-wide text-right text-gray-600 dark:text-gray-300">Options</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                    @forelse($widgets as $w)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $w->id }}</td>
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $w->title }}</p>
                            <p class="text-xs text-gray-400">{{ $types[$w->type] ?? $w->type }}</p>
                        </td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300 text-sm hidden md:table-cell">
                            {{ $positions[$w->where_to_display] ?? $w->where_to_display }}
                        </td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300 hidden lg:table-cell">{{ $w->display_order }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $w->is_active ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                                {{ $w->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">{{ $w->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-5 py-3 text-right">
                            <div x-data="{
                                    open: false, top: 0, right: 0,
                                    toggle(btn) {
                                        const r = btn.getBoundingClientRect();
                                        this.top = r.bottom + 4;
                                        this.right = window.innerWidth - r.right;
                                        this.open = !this.open;
                                    }
                                }"
                                class="inline-block" @click.outside="open = false">
                                <button @click="toggle($el)"
                                    class="flex items-center gap-1 px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium">
                                    Select
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-cloak
                                    class="fixed w-36 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-2xl z-[999] overflow-hidden"
                                    :style="`top:${top}px; right:${right}px`">
                                    <a href="/admin/widgets/{{ $w->id }}/edit"
                                        class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </a>
                                    <form method="POST" action="/admin/widgets/{{ $w->id }}" onsubmit="return confirm('Delete widget?')">
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
                    <tr><td colspan="7" class="text-center py-12 text-gray-400 dark:text-gray-500">
                        <p class="text-3xl mb-2">🧩</p>
                        <p>No widgets yet. Add one using the form.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
