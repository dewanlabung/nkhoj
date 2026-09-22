@php
$_activeStories = \App\Models\MediaContent\Story::with(['user', 'post'])
    ->active()
    ->latest()
    ->get()
    ->groupBy('user_id');
$_storyHighlights = \App\Models\MediaContent\StoryHighlight::with(['stories' => fn($q) => $q->active()])
    ->whereHas('stories', fn($q) => $q->active())
    ->latest()
    ->limit(5)
    ->get();
@endphp

@if($_activeStories->count() || $_storyHighlights->count())
<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Stories</h3>
        <a href="/stories" class="text-sm text-brand-500 hover:text-brand-600 font-medium">View all</a>
    </div>

    <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
        @auth
        <a href="/stories/create" class="flex-shrink-0 w-24 rounded-xl overflow-hidden border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-brand-500 transition-colors flex items-center justify-center cursor-pointer bg-gray-50 dark:bg-gray-700/50 group" style="aspect-ratio:9/16;">
            <div class="text-center">
                <div class="text-2xl mb-1">+</div>
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 group-hover:text-brand-500">Post</p>
            </div>
        </a>
        @endauth

        @foreach($_storyHighlights as $highlight)
        <div class="flex-shrink-0 w-24">
            <a href="#" class="group relative block rounded-xl overflow-hidden border-2 border-gray-200 dark:border-gray-700 hover:border-brand-500 transition-colors" style="aspect-ratio:9/16;">
                @php $firstStory = $highlight->stories->first(); @endphp
                @if($firstStory && $firstStory->media_type === 'video')
                <video src="{{ $firstStory->media_url }}" class="w-full h-full object-cover" muted></video>
                @elseif($firstStory)
                <img src="{{ $firstStory->media_url }}" alt="{{ $highlight->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                @endif
                <div class="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-colors"></div>
                <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/60 to-transparent">
                    <p class="text-white text-xs font-semibold truncate">{{ $highlight->title }}</p>
                </div>
            </a>
        </div>
        @endforeach

        @foreach($_activeStories->take(6) as $userId => $userStories)
        @php
            $user = $userStories->first()->user;
            $firstStory = $userStories->first();
            $storyLink = ($firstStory->post_id && $firstStory->post)
                ? '/posts/' . $firstStory->post->slug
                : '/stories/' . $firstStory->id;
        @endphp
        <div class="flex-shrink-0 w-24">
            <a href="{{ $storyLink }}" class="group relative block rounded-xl overflow-hidden border-2 border-gray-200 dark:border-gray-700 hover:border-brand-500 transition-colors" style="aspect-ratio:9/16;">
                @if($firstStory->media_type === 'video')
                <video src="{{ $firstStory->media_url }}" class="w-full h-full object-cover" muted></video>
                <div class="absolute top-2 right-2 bg-white/90 rounded-full p-1">
                    <svg class="w-3 h-3 text-gray-900" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </div>
                @else
                <img src="{{ $firstStory->media_url }}" alt="Story" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                @endif
                <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors"></div>
                @if($firstStory->post_id)
                <div class="absolute top-1.5 left-1.5 bg-brand-500 rounded-full px-1.5 py-0.5 text-[9px] font-bold text-white leading-none">📰</div>
                @endif
                <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/60 to-transparent">
                    <p class="text-white text-xs font-semibold truncate">{{ $user->name }}</p>
                </div>
                @if($userStories->count() > 1)
                <div class="absolute top-2 right-2 bg-white/90 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold text-gray-900">{{ $userStories->count() }}</div>
                @endif
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif
