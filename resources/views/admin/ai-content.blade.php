@extends('layouts.admin')
@section('title', 'AI Content')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 rounded-xl text-sm">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-xl text-sm">
    {{ session('error') }}
</div>
@endif

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">AI Content Generator</h1>
        <p class="text-xs text-gray-400 mt-0.5">Gemini API auto-generates English + Nepali drafts daily from RSS feeds</p>
    </div>
    <form method="POST" action="/admin/ai-content/run-all">
        @csrf
        <button class="px-4 py-2 bg-brand-500 text-white text-sm rounded-lg hover:bg-brand-600 font-medium flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Run All Now
        </button>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

{{-- LEFT: Topics + Settings --}}
<div class="lg:col-span-1 space-y-5">

    {{-- Gemini Settings --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h2 class="font-semibold text-gray-900 dark:text-white text-sm mb-4 flex items-center gap-2">
            <span class="text-lg">🤖</span> Gemini API Settings
        </h2>
        <form method="POST" action="/admin/ai-content/settings" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">API Key</label>
                <input type="password" name="gemini_api_key" value="{{ $geminiKey }}"
                    placeholder="AIza..."
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <p class="text-xs text-gray-400 mt-1">Get free key at <a href="https://aistudio.google.com" target="_blank" class="text-brand-500 underline">aistudio.google.com</a></p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Model</label>
                <select name="gemini_model" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="gemini-1.5-flash" {{ $geminiModel === 'gemini-1.5-flash' ? 'selected' : '' }}>gemini-1.5-flash (fast, free)</option>
                    <option value="gemini-1.5-pro" {{ $geminiModel === 'gemini-1.5-pro' ? 'selected' : '' }}>gemini-1.5-pro (better quality)</option>
                    <option value="gemini-2.0-flash" {{ $geminiModel === 'gemini-2.0-flash' ? 'selected' : '' }}>gemini-2.0-flash (latest)</option>
                </select>
            </div>
            <button class="w-full py-2 bg-gray-800 dark:bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 font-medium">Save Settings</button>
        </form>
    </div>

    {{-- Add Topic --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <h2 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">+ Add Topic</h2>
        <form method="POST" action="/admin/ai-content/topics" class="space-y-3">
            @csrf
            <input type="text" name="keyword" placeholder="e.g. Nepal AI technology" required
                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">

            <select name="category_id" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="">— Category (optional) —</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name_en }}</option>
                @endforeach
            </select>

            <select name="language" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="both">Both (English + Nepali)</option>
                <option value="en">English only</option>
                <option value="ne">Nepali only</option>
            </select>

            <select name="frequency" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="manual">Manual only</option>
            </select>

            <input type="url" name="rss_source" placeholder="RSS feed URL (optional)"
                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">

            <p class="text-xs text-gray-400">Popular feeds: TechCrunch, BBC Health, WHO, Reuters Nepal</p>

            <button class="w-full py-2 bg-brand-500 text-white text-sm rounded-lg hover:bg-brand-600 font-medium">Add Topic</button>
        </form>
    </div>

    {{-- RSS Presets --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800 p-4">
        <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 mb-2">📡 Useful RSS Feeds</p>
        <div class="space-y-1 text-xs text-blue-600 dark:text-blue-400 font-mono">
            <p>TechCrunch AI: feeds.feedburner.com/TechCrunch/</p>
            <p>BBC Health: feeds.bbci.co.uk/news/health/rss.xml</p>
            <p>WHO: who.int/rss-feeds/news-english.xml</p>
            <p>Reuters: feeds.reuters.com/reuters/technologyNews</p>
        </div>
    </div>
</div>

{{-- RIGHT: Topics list + AI Drafts --}}
<div class="lg:col-span-2 space-y-6">

    {{-- Active Topics --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 dark:text-white text-sm">Topics ({{ $topics->count() }})</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                <tr>
                    <th class="text-left px-5 py-3">Keyword</th>
                    <th class="text-left px-5 py-3 hidden sm:table-cell">Lang</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Frequency</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Last Run</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                @forelse($topics as $topic)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $topic->keyword }}</p>
                        @if($topic->category)
                        <p class="text-xs text-gray-400">{{ $topic->category->name_en }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3 hidden sm:table-cell">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $topic->language === 'both' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }} font-medium">
                            {{ strtoupper($topic->language) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 hidden md:table-cell text-gray-600 dark:text-gray-300 capitalize">{{ $topic->frequency }}</td>
                    <td class="px-5 py-3 hidden md:table-cell text-gray-400 text-xs">
                        {{ $topic->last_run_at ? $topic->last_run_at->diffForHumans() : 'Never' }}
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <form method="POST" action="/admin/ai-content/topics/{{ $topic->id }}/run">
                                @csrf
                                <button class="text-xs px-2 py-1 bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-lg hover:bg-brand-100 font-medium">▶ Run</button>
                            </form>
                            <form method="POST" action="/admin/ai-content/topics/{{ $topic->id }}/toggle">
                                @csrf
                                <button class="text-xs px-2 py-1 rounded-lg font-medium {{ $topic->is_active ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-400' }} hover:opacity-80">
                                    {{ $topic->is_active ? 'Active' : 'Off' }}
                                </button>
                            </form>
                            <form method="POST" action="/admin/ai-content/topics/{{ $topic->id }}" onsubmit="return confirm('Delete topic?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-8 text-gray-400">No topics yet. Add one on the left.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- AI Drafts review --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
            <h2 class="font-semibold text-gray-900 dark:text-white text-sm">AI Draft Posts — Review & Publish ({{ $drafts->total() }})</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                <tr>
                    <th class="text-left px-5 py-3">Title</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Category</th>
                    <th class="text-left px-5 py-3 hidden sm:table-cell">Created</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                @forelse($drafts as $draft)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-5 py-3">
                        <a href="/dashboard/posts/{{ $draft->id }}/edit" class="font-medium text-gray-900 dark:text-white hover:text-brand-600 line-clamp-1 block max-w-xs" target="_blank">
                            {{ $draft->title }}
                        </a>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $draft->sources }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-500 dark:text-gray-400 hidden md:table-cell text-xs">{{ $draft->category?->name_en ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-400 text-xs hidden sm:table-cell">{{ $draft->created_at->diffForHumans() }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <form method="POST" action="/admin/ai-content/drafts/{{ $draft->id }}/publish">
                                @csrf
                                <button class="text-xs px-2 py-1 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-100 font-medium">Publish</button>
                            </form>
                            <form method="POST" action="/admin/ai-content/drafts/{{ $draft->id }}" onsubmit="return confirm('Delete this draft?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-8 text-gray-400">No AI drafts yet. Run a topic to generate posts.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($drafts->hasPages())
        <div class="px-5 py-3 border-t border-gray-50 dark:border-gray-700">{{ $drafts->links() }}</div>
        @endif
    </div>

</div>
</div>

@endsection
