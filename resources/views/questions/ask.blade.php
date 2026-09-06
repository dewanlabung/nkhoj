@extends('layouts.app')
@section('title', 'Ask a Question')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- Main form --}}
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Ask a Question</h1>
            <p class="text-sm text-gray-400 mb-6">Get answers from the community. Be specific and clear.</p>

            @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            @guest
            <div class="mb-5 px-4 py-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-amber-700 dark:text-amber-400 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>You need to <a href="/login" class="font-semibold underline">sign in</a> to post a question.</span>
            </div>
            @endguest

            <form method="POST" action="/ask-question" enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Title --}}
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">
                        Question Title <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        placeholder="e.g. How do I reverse a string in Python?"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-xs text-gray-400 mt-1">Start with who, what, when, where, why, or how.</p>
                </div>

                {{-- Category --}}
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Category</label>
                    <select name="category_id"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">— Select a category —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name_ne ?? $cat->name_en }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Detail --}}
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Question Details</label>
                    <textarea name="content" rows="7"
                        placeholder="Describe your question in detail. Include what you've tried, any error messages, etc."
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-y">{{ old('content') }}</textarea>
                </div>

                {{-- Tags --}}
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Tags</label>
                    <input type="text" name="tags" value="{{ old('tags') }}"
                        placeholder="e.g. python, string, algorithm (comma separated)"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @if($tags->count())
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach($tags->take(20) as $tag)
                        <button type="button" onclick="addTag('{{ $tag->name_en }}')"
                            class="text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-brand-50 dark:hover:bg-brand-900/30 hover:text-brand-600 text-gray-600 dark:text-gray-300 rounded-full transition-colors">
                            +{{ $tag->name_en }}
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Image --}}
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Attach Image <span class="font-normal text-gray-400">(optional)</span></label>
                    <input type="file" name="featured_image" accept="image/*"
                        class="w-full text-sm text-gray-500 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100">
                </div>

                {{-- Options --}}
                <div class="flex flex-wrap gap-x-6 gap-y-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Post anonymously</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="notify_email" value="1" checked
                            class="w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Notify me of answers by email</span>
                    </label>
                </div>

                {{-- Agreement --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="agree" value="1" required
                            class="mt-0.5 w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 flex-shrink-0">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            I agree to follow the community rules. I understand that posts violating the guidelines will be removed.
                        </span>
                    </label>
                </div>

                <div class="flex gap-3 pt-1">
                    <a href="/questions" class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</a>
                    <button type="submit" @guest disabled @endguest
                        class="flex-1 py-2.5 bg-brand-500 hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-sm rounded-xl transition-colors">
                        Post Question
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-5">
        {{-- Stats --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">Community Stats</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="text-center p-3 bg-brand-50 dark:bg-brand-900/20 rounded-xl">
                    <p class="text-2xl font-bold text-brand-600">{{ $totalQuestions }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Questions</p>
                </div>
                <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                    <p class="text-2xl font-bold text-green-600">{{ $totalAnswers }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Answers</p>
                </div>
                <div class="text-center p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                    <p class="text-2xl font-bold text-amber-600">{{ $bestAnswers }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Best Answers</p>
                </div>
                <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                    <p class="text-2xl font-bold text-purple-600">{{ $totalUsers }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Members</p>
                </div>
            </div>
        </div>

        {{-- Tips --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-3">Tips for a Good Question</h3>
            <ul class="space-y-2.5">
                @foreach(['Be specific and clear in your title', 'Include all relevant details and context', 'Show what you\'ve already tried', 'Add appropriate tags to reach the right experts', 'Proofread before posting'] as $tip)
                <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    {{ $tip }}
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Popular questions --}}
        @if($popularQuestions->count())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-3">Popular Questions</h3>
            <div class="space-y-3">
                @foreach($popularQuestions as $q)
                <a href="/questions/{{ $q->slug }}" class="block group">
                    <p class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-brand-600 leading-snug">{{ Str::limit($q->title, 70) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $q->views_count }} views · {{ $q->answers_count }} answers</p>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function addTag(name) {
    const input = document.querySelector('input[name="tags"]');
    const parts = input.value.split(',').map(t => t.trim()).filter(Boolean);
    if (!parts.includes(name)) parts.push(name);
    input.value = parts.join(', ');
}
</script>
@endsection
