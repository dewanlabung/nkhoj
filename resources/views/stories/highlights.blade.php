@extends('layouts.app')
@section('title', 'Manage Story Highlights — नखोज')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Story Highlights</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Create collections of your favorite stories</p>
        </div>
        <a href="/stories" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition-colors">
            ← Back to Stories
        </a>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Create New Highlight Form --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 sticky top-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4">Create Highlight</h2>
                <form method="POST" action="/highlights" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-2">
                            Highlight Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" required maxlength="100" placeholder="e.g., Travel"
                            class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">After creating, you can add stories to this highlight</p>
                    <button type="submit" class="w-full px-3 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-lg transition-colors">
                        Create Highlight
                    </button>
                </form>

                @if($availableStories->isNotEmpty())
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Available Stories</h3>
                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        @foreach($availableStories as $story)
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                            <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 bg-gray-200 dark:bg-gray-600">
                                @if($story->media_type === 'image')
                                <img src="{{ $story->media_url }}" alt="" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex items-center justify-center text-xs">🎥</div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ Str::limit($story->caption ?? 'No caption', 30) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $story->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Highlights List --}}
        <div class="lg:col-span-2 space-y-4">
            @forelse($highlights as $highlight)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if($highlight->stories->isNotEmpty())
                        <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0 bg-gray-200 dark:bg-gray-600">
                            @php $firstStory = $highlight->stories->first(); @endphp
                            @if($firstStory->media_type === 'image')
                            <img src="{{ $firstStory->media_url }}" alt="" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center text-xl">🎥</div>
                            @endif
                        </div>
                        @else
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($highlight->title, 0, 1)) }}
                        </div>
                        @endif
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $highlight->title }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $highlight->stories->count() }} story(ies)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="/highlights/{{ $highlight->id }}" onsubmit="return confirm('Delete this highlight?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs px-3 py-2 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 rounded-lg transition-colors">
                                🗑️ Delete
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Stories in this highlight --}}
                @if($highlight->stories->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-4">
                    @foreach($highlight->stories as $story)
                    <div class="relative group">
                        <a href="/stories/{{ $story->id }}" class="relative block aspect-[9/16] rounded-lg overflow-hidden bg-gray-200 dark:bg-gray-600">
                            @if($story->media_type === 'image')
                            <img src="{{ $story->media_url }}" alt="" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                            <video src="{{ $story->media_url }}" class="w-full h-full object-cover" muted></video>
                            <div class="absolute inset-0 flex items-center justify-center bg-black/20">
                                <span class="text-white text-lg">▶</span>
                            </div>
                            @endif
                        </a>
                        <form method="POST" action="/highlights/{{ $highlight->id }}/add" onsubmit="return confirm('Remove from highlight?')" class="absolute inset-0 hidden group-hover:flex items-center justify-center bg-black/50 rounded-lg transition-all">
                            @csrf
                            <input type="hidden" name="story_id" value="{{ $story->id }}">
                            <button type="submit" class="px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded transition-colors">
                                Remove
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <p class="text-sm">No stories in this highlight yet.</p>
                    <p class="text-xs mt-1">Stories will appear here when you add them</p>
                </div>
                @endif
            </div>
            @empty
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-12 text-center">
                <p class="text-3xl mb-3">✨</p>
                <p class="text-gray-700 dark:text-gray-300 font-medium">No highlights yet</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create your first highlight above to get started</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<style>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection
