@extends('layouts.admin')
@section('title', 'Ad Spaces')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Create ad zone --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 dark:text-white mb-4">Add Ad Zone</h3>
        <form method="POST" action="/admin/ads" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Zone Name *</label>
                <input type="text" name="name" required placeholder="e.g. Header Banner"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Position *</label>
                <select name="position" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="header">Header (above nav)</option>
                    <option value="sidebar">Sidebar</option>
                    <option value="in_content">In Content (mid-article)</option>
                    <option value="after_post">After Post</option>
                    <option value="footer">Footer</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Ad Code (HTML/JS) *</label>
                <textarea name="code" rows="5" required placeholder="&lt;script async src=&quot;...&quot;&gt;&lt;/script&gt;"
                    class="w-full text-sm font-mono border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none bg-gray-50 dark:bg-gray-700"></textarea>
                <p class="text-xs text-gray-400 mt-1">Paste Google AdSense, AdButler, or any ad network code.</p>
            </div>
            <button class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                Create Ad Zone
            </button>
        </form>
    </div>

    {{-- Ad zones list --}}
    <div class="lg:col-span-2 space-y-4">
        @php
            $positions = ['header'=>'Header','sidebar'=>'Sidebar','in_content'=>'In Content','after_post'=>'After Post','footer'=>'Footer'];
            $posColors = ['header'=>'blue','sidebar'=>'purple','in_content'=>'green','after_post'=>'yellow','footer'=>'gray'];
        @endphp
        @forelse($adZones as $zone)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden"
             x-data="{ editing: false }">
            <div class="px-5 py-4 flex items-start justify-between gap-4">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                        @if($zone->position === 'header') bg-blue-50 text-blue-700
                        @elseif($zone->position === 'sidebar') bg-purple-50 text-purple-700
                        @elseif($zone->position === 'in_content') bg-green-50 text-green-700
                        @elseif($zone->position === 'after_post') bg-yellow-50 text-yellow-700
                        @else bg-gray-100 text-gray-600 @endif">
                        {{ $positions[$zone->position] ?? $zone->position }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $zone->name }}</p>
                        <p class="text-xs text-gray-400 truncate font-mono">{{ Str::limit(strip_tags($zone->code), 60) }}</p>
                    </div>
                    <span class="flex-shrink-0 text-xs font-bold {{ $zone->is_active ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $zone->is_active ? 'Active' : 'Paused' }}
                    </span>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button @click="editing = !editing" class="text-xs text-brand-600 hover:underline">Edit</button>
                    <form method="POST" action="/admin/ads/{{ $zone->id }}" onsubmit="return confirm('Delete ad zone?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-400 hover:text-red-600">Delete</button>
                    </form>
                </div>
            </div>

            <div x-show="editing" x-cloak class="border-t border-gray-100 dark:border-gray-700 px-5 py-4">
                <form method="POST" action="/admin/ads/{{ $zone->id }}" class="space-y-3">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $zone->name }}" required
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <textarea name="code" rows="4" required
                        class="w-full text-sm font-mono border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none bg-gray-50 dark:bg-gray-700">{{ $zone->code }}</textarea>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ $zone->is_active ? 'checked' : '' }} class="rounded border-gray-300">
                            Active
                        </label>
                        <button class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg">Save</button>
                        <button type="button" @click="editing = false" class="text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-16 text-center text-gray-400">
            <p class="text-4xl mb-3">📣</p>
            <p class="font-medium">No ad zones yet.</p>
            <p class="text-sm mt-1">Create an ad zone and paste your ad network code.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
