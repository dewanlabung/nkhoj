@extends('layouts.app')
@section('title', 'Create Series')

@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">📚 Create New Series</h1>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <form method="POST" action="/series" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Series Title *</label>
                <input type="text" name="title" required value="{{ old('title') }}"
                    placeholder="e.g. Nepal Tech Deep Dive"
                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" rows="3" placeholder="What is this series about?"
                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none font-nepali">{{ old('description') }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl transition-colors">
                    Create Series
                </button>
                <a href="/series" class="px-6 py-2.5 text-gray-600 dark:text-gray-300 hover:text-gray-900 font-medium">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
