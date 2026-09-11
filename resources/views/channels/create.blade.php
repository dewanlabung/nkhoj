@extends('layouts.app')
@section('title', 'Create Channel — नखोज')
@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">📢 Create Broadcast Channel</h1>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <form action="/channels" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Channel Name *</label>
                <input type="text" name="name" required maxlength="150" value="{{ old('name') }}"
                       class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                       placeholder="My Awesome Channel">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Description <span class="font-normal text-gray-400">(optional)</span></label>
                <textarea name="description" maxlength="500" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 resize-none"
                          placeholder="What will you broadcast?">{{ old('description') }}</textarea>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-3 text-sm text-blue-700 dark:text-blue-300">
                <strong>Broadcast channels</strong> are one-way: only you post, subscribers read and react. Perfect for news feeds, announcements, or creator updates.
            </div>
            <button type="submit" class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-colors">
                Create Channel
            </button>
        </form>
    </div>
</div>
@endsection
