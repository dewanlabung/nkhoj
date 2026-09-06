@extends('layouts.admin')
@section('title', 'Update Widget')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <nav class="text-xs text-gray-400">
        <a href="/admin" class="hover:text-brand-600">Home</a>
        <span class="mx-1">›</span>
        <a href="/admin/widgets" class="hover:text-brand-600">Widgets</a>
        <span class="mx-1">›</span>
        <span class="text-gray-600 dark:text-gray-300">Update Widget</span>
    </nav>
    <div class="ml-auto">
        <a href="/admin/widgets" class="flex items-center gap-1.5 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            Widgets
        </a>
    </div>
</div>

<form method="POST" action="/admin/widgets/{{ $widget->id }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @csrf @method('PUT')

    {{-- Settings panel --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-5">
        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Settings</h3>

        <div>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Widget Type <span class="text-red-500">*</span></label>
            <div class="relative">
                <select name="type"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white appearance-none pr-8">
                    @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ $widget->type === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-3 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Where To Display <span class="text-red-500">*</span></label>
            <div class="relative">
                <select name="where_to_display"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white appearance-none pr-8">
                    @foreach($positions as $key => $label)
                    <option value="{{ $key }}" {{ $widget->where_to_display === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-3 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Order <span class="text-red-500">*</span></label>
            <input type="number" name="display_order" value="{{ $widget->display_order }}" min="0" max="999"
                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-2">Status</label>
            <div x-data="{ active: {{ $widget->is_active ? 'true' : 'false' }} }" class="flex items-center gap-3">
                <button type="button" @click="active = !active"
                    :class="active ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200">
                    <span :class="active ? 'translate-x-5' : 'translate-x-0.5'"
                        class="inline-block h-5 w-5 translate-y-0.5 transform rounded-full bg-white shadow transition duration-200"></span>
                </button>
                <input type="hidden" name="is_active" :value="active ? '1' : '0'">
            </div>
        </div>
    </div>

    {{-- General panel --}}
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">General</h3>
            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ $widget->title }}" required
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
        </div>

        <div class="flex justify-end">
            <button class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                Save Changes
            </button>
        </div>
    </div>

</form>
@endsection
