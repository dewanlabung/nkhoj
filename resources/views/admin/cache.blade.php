@extends('layouts.admin')
@section('title', 'Cache System')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Cache System</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
        <a href="/admin" class="hover:text-brand-500">Home</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">›</span>
        Cache System
    </p>
</div>

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm rounded-xl border border-green-100 dark:border-green-800/40">
    ✓ {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 px-4 py-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm rounded-xl border border-red-100 dark:border-red-800/40">
    {{ session('error') }}
</div>
@endif

@php
$cacheDriver = config('cache.default', 'file');
$viewCacheFiles = count(glob(storage_path('framework/views/*.php')) ?: []);
$routeCached = file_exists(base_path('bootstrap/cache/routes-v7.php'));
$configCached = file_exists(base_path('bootstrap/cache/config.php'));
@endphp

{{-- Overview cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Cache Driver</p>
        <p class="text-lg font-bold text-gray-900 dark:text-white capitalize">{{ $cacheDriver }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">View Cache</p>
        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $viewCacheFiles }} <span class="text-sm font-normal text-gray-400">files</span></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Routes</p>
        @if($routeCached)
        <span class="inline-flex items-center gap-1 text-sm font-semibold text-green-600 dark:text-green-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Cached
        </span>
        @else
        <span class="text-sm font-semibold text-gray-400">Not cached</span>
        @endif
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Config</p>
        @if($configCached)
        <span class="inline-flex items-center gap-1 text-sm font-semibold text-green-600 dark:text-green-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Cached
        </span>
        @else
        <span class="text-sm font-semibold text-gray-400">Not cached</span>
        @endif
    </div>
</div>

{{-- Clear cache actions --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Application Cache --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-sm">Application Cache</h3>
                <p class="text-xs text-gray-400 mt-0.5">Clears the main Laravel application cache store (database queries, sessions stored in cache, etc.).</p>
            </div>
        </div>
        <form method="POST" action="/admin/cache/clear">
            @csrf <input type="hidden" name="type" value="app">
            <button class="w-full py-2.5 bg-yellow-50 dark:bg-yellow-900/20 hover:bg-yellow-100 dark:hover:bg-yellow-900/40 text-yellow-700 dark:text-yellow-400 text-sm font-semibold rounded-lg border border-yellow-200 dark:border-yellow-800/40 transition-colors">
                Clear Application Cache
            </button>
        </form>
    </div>

    {{-- View Cache --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-sm">View Cache</h3>
                <p class="text-xs text-gray-400 mt-0.5">Clears compiled Blade template files ({{ $viewCacheFiles }} files). Views will recompile on next request.</p>
            </div>
        </div>
        <form method="POST" action="/admin/cache/clear">
            @csrf <input type="hidden" name="type" value="view">
            <button class="w-full py-2.5 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 text-blue-700 dark:text-blue-400 text-sm font-semibold rounded-lg border border-blue-200 dark:border-blue-800/40 transition-colors">
                Clear View Cache
            </button>
        </form>
    </div>

    {{-- Route Cache --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-sm">Route Cache</h3>
                <p class="text-xs text-gray-400 mt-0.5">Clears the route cache file. Required when adding or modifying routes. Status: {{ $routeCached ? 'Cached' : 'Not cached' }}.</p>
            </div>
        </div>
        <form method="POST" action="/admin/cache/clear">
            @csrf <input type="hidden" name="type" value="route">
            <button class="w-full py-2.5 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/40 text-purple-700 dark:text-purple-400 text-sm font-semibold rounded-lg border border-purple-200 dark:border-purple-800/40 transition-colors">
                Clear Route Cache
            </button>
        </form>
    </div>

    {{-- Config Cache --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-sm">Config Cache</h3>
                <p class="text-xs text-gray-400 mt-0.5">Clears the configuration cache. Required after changing .env or config files. Status: {{ $configCached ? 'Cached' : 'Not cached' }}.</p>
            </div>
        </div>
        <form method="POST" action="/admin/cache/clear">
            @csrf <input type="hidden" name="type" value="config">
            <button class="w-full py-2.5 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/40 text-green-700 dark:text-green-400 text-sm font-semibold rounded-lg border border-green-200 dark:border-green-800/40 transition-colors">
                Clear Config Cache
            </button>
        </form>
    </div>

</div>

{{-- Danger zone --}}
<div class="mt-5 bg-white dark:bg-gray-800 rounded-xl border border-red-100 dark:border-red-900/40 shadow-sm p-5">
    <div class="flex items-start gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <h3 class="font-bold text-gray-900 dark:text-white text-sm">Clear All Caches</h3>
            <p class="text-xs text-gray-400 mt-0.5">Clears all cache types at once: application, views, routes, and config. All pages will recompile on next visit — expect a brief slowdown.</p>
        </div>
    </div>
    <form method="POST" action="/admin/cache/clear" onsubmit="return confirm('Clear ALL caches? First requests will be slower while caches rebuild.')">
        @csrf <input type="hidden" name="type" value="all">
        <button class="px-6 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-lg transition-colors">
            Clear All Caches
        </button>
    </form>
</div>

@endsection
