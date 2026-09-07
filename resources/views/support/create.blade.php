@extends('layouts.app')
@section('title', 'New Support Ticket')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-4">
        <a href="/support" class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-brand-600">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Support Center
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-8">
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-1">Create Support Ticket</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Describe your issue and we'll respond as soon as possible.</p>

        <form method="POST" action="/support" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">Subject *</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="255"
                    placeholder="Brief summary of your issue"
                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 @error('subject') border-red-400 @enderror">
                @error('subject')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">Priority</label>
                <select name="priority"
                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">Description *</label>
                <textarea name="body" rows="8" required maxlength="5000"
                    placeholder="Please describe your issue in detail..."
                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none @error('body') border-red-400 @enderror">{{ old('body') }}</textarea>
                @error('body')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="flex-1 py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl transition-colors text-sm">
                    Submit Ticket
                </button>
                <a href="/support"
                    class="px-6 py-3 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
