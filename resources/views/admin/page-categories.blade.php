@extends('layouts.admin')
@section('title', 'Page Categories')

@section('content')

@if(session('success'))
<div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-400 rounded-xl px-4 py-3 text-sm mb-5">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-400 rounded-xl px-4 py-3 text-sm mb-5">
    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Add category form --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm">Add New Category</h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Categories are used to classify pages on discover</p>
        </div>
        <form method="POST" action="/admin/page-categories" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Category Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Health & Wellness"
                    class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Icon (emoji, optional)</label>
                <input type="text" name="icon" value="{{ old('icon') }}" placeholder="e.g. 💊"
                    class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Single emoji shown alongside the category name</p>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-lg transition">
                Add Category
            </button>
        </form>
    </div>

    {{-- Category list --}}
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-sm">All Categories</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $categories->count() }} total · {{ $categories->where('is_active', true)->count() }} active</p>
            </div>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
            @forelse($categories as $cat)
            <div class="px-5 py-3 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <span class="text-xl w-8 text-center flex-shrink-0">{{ $cat->icon ?? '📌' }}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-gray-900 dark:text-white truncate">{{ $cat->name }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $cat->slug }}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($cat->is_active)
                        <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-semibold rounded-full">Active</span>
                    @else
                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs font-semibold rounded-full">Hidden</span>
                    @endif

                    {{-- Toggle --}}
                    <form method="POST" action="/admin/page-categories/{{ $cat->id }}/toggle">
                        @csrf
                        <button type="submit"
                            class="p-1.5 rounded-lg transition {{ $cat->is_active ? 'bg-green-50 dark:bg-green-900/20 text-green-600 hover:bg-green-100 dark:hover:bg-green-900/40' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                            title="{{ $cat->is_active ? 'Disable' : 'Enable' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($cat->is_active)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                @endif
                            </svg>
                        </button>
                    </form>

                    {{-- Delete --}}
                    <form method="POST" action="/admin/page-categories/{{ $cat->id }}"
                          onsubmit="return confirm('Delete this category? Pages using it keep their existing category data.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center text-gray-400 dark:text-gray-500 text-sm">No categories yet. Add one above.</div>
            @endforelse
        </div>
    </div>
</div>

@endsection
