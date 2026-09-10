@extends('layouts.app')

@section('title', $page->name)

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" defer></script>
@endpush

@section('content')
<div class="bg-white min-h-screen" x-data="{ activeTab: 'posts' }">

    {{-- Open Graph meta --}}
    @section('og')
    <meta property="og:title" content="{{ $page->name }}">
    <meta property="og:description" content="{{ $page->bio ?: $page->first_category }}">
    <meta property="og:image" content="{{ $page->avatar_url ?: '' }}">
    <meta property="og:url" content="{{ url('/pages/'.$page->slug) }}">
    <meta property="og:type" content="website">
    @endsection

    {{-- Cover photo --}}
    <div class="relative h-36 md:h-56 bg-gray-300 overflow-hidden">
        @if($page->cover_url)
            <img src="{{ $page->cover_url }}" alt="Cover" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-600"></div>
        @endif
        @if($isOwner)
            <label class="absolute bottom-3 right-3 bg-white/90 hover:bg-white rounded-full p-2 cursor-pointer shadow-md transition">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </label>
        @endif
    </div>

    {{-- Profile row --}}
    <div class="max-w-2xl mx-auto px-4">
        <div class="flex items-end gap-4 -mt-10 mb-4">
            <div class="relative flex-shrink-0">
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-full border-4 border-white overflow-hidden bg-gray-200 shadow">
                    <img src="{{ $page->avatar }}" alt="{{ $page->name }}" class="w-full h-full object-cover">
                </div>
            </div>
            <div class="flex-1 pb-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-bold text-gray-900">{{ $page->name }}</h1>
                    @if($page->is_verified)
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                    {{-- Business hours badge --}}
                    @if($isOpen === true)
                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-semibold">Open Now</span>
                    @elseif($isOpen === false)
                        <span class="text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded-full font-semibold">Closed</span>
                    @endif
                </div>
                <p class="text-gray-500 text-sm">
                    {{ number_format($page->followers_count) }} followers
                    @if($page->first_category) &middot; {{ $page->first_category }} @endif
                    @if($page->reviews_count > 0)
                        &middot; ⭐ {{ number_format($page->rating_avg, 1) }} ({{ $page->reviews_count }})
                    @endif
                </p>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex gap-2 mb-5 flex-wrap">
            @if($isOwner)
                <a href="/pages/{{ $page->slug }}/dashboard"
                   class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl transition text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Dashboard
                </a>
                <a href="/pages/{{ $page->slug }}/settings"
                   class="flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2.5 px-4 rounded-xl transition text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                    Settings
                </a>
            @else
                @auth
                    <button id="follow-btn" onclick="toggleFollow()"
                        class="flex-1 flex items-center justify-center gap-2 font-semibold py-2.5 px-4 rounded-xl transition text-sm {{ $isFollowing ? 'bg-gray-100 hover:bg-gray-200 text-gray-800' : 'bg-blue-600 hover:bg-blue-700 text-white' }}"
                        data-following="{{ $isFollowing ? 'true' : 'false' }}" data-slug="{{ $page->slug }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isFollowing ? 'M5 13l4 4L19 7' : 'M12 4v16m8-8H4' }}"/></svg>
                        <span id="follow-label">{{ $isFollowing ? 'Following' : 'Follow' }}</span>
                    </button>
                    <button id="save-page-btn" onclick="toggleSavePage()"
                        class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl transition text-sm font-semibold {{ ($isSaved ?? false) ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                        data-saved="{{ ($isSaved ?? false) ? 'true' : 'false' }}" data-id="{{ $page->id }}">
                        <svg class="w-4 h-4" fill="{{ ($isSaved ?? false) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg>
                        <span id="save-page-label">{{ ($isSaved ?? false) ? 'Saved' : 'Save' }}</span>
                    </button>
                @else
                    <a href="/login" class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl transition text-sm">Follow</a>
                @endauth
            @endif

            {{-- Share button --}}
            <button onclick="sharePage()" id="share-btn"
                class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition text-sm font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                <span id="share-label">Share</span>
            </button>
        </div>

        @if($page->bio)
            <p class="text-gray-700 text-sm mb-4">{{ $page->bio }}</p>
        @endif

        {{-- Tabs --}}
        <div class="border-b border-gray-200 flex gap-1 mb-6 overflow-x-auto">
            @foreach(['posts' => 'Posts', 'events' => 'Events', 'reviews' => 'Reviews', 'about' => 'About', 'followers' => 'Followers'] as $tab => $label)
                <button @click="activeTab = '{{ $tab }}'"
                    class="pb-3 px-3 text-sm font-semibold whitespace-nowrap transition"
                    :class="activeTab === '{{ $tab }}' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'">
                    {{ $label }}
                    @if($tab === 'reviews' && $page->reviews_count > 0)
                        <span class="ml-1 text-xs bg-gray-100 px-1.5 py-0.5 rounded-full">{{ $page->reviews_count }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- ========== TAB: Posts ========== --}}
        <div x-show="activeTab === 'posts'">
            {{-- Create post form (owner only) --}}
            @if($isOwner)
            <div class="bg-gray-50 rounded-2xl p-4 mb-4 border border-gray-200" x-data="{ postType: 'text', showForm: false }">
                <button @click="showForm = !showForm" class="w-full text-left text-sm text-gray-500 bg-white rounded-xl px-4 py-3 border border-gray-200 hover:bg-gray-50 transition">
                    What's on your mind? Share a post, photo or event...
                </button>
                <div x-show="showForm" x-cloak class="mt-3">
                    <form method="POST" action="/pages/{{ $page->slug }}/posts" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div class="flex gap-2 flex-wrap">
                            @foreach(['text' => '✏️ Text', 'photo' => '📷 Photo', 'video' => '🎬 Video', 'event' => '📅 Event'] as $t => $l)
                            <button type="button" @click="postType = '{{ $t }}'"
                                class="px-3 py-1.5 rounded-full text-xs font-semibold transition"
                                :class="postType === '{{ $t }}' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'">{{ $l }}</button>
                            @endforeach
                        </div>
                        <input type="hidden" name="type" :value="postType">
                        <textarea name="body" rows="3" placeholder="Write something..."
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 resize-none"></textarea>
                        <div x-show="postType === 'photo'">
                            <input type="file" name="image" accept="image/*" class="text-sm text-gray-600">
                        </div>
                        <div x-show="postType === 'video'" class="space-y-2">
                            <input type="url" name="video_url" placeholder="Video URL (YouTube, etc.)"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        <div x-show="postType === 'event'" class="space-y-2">
                            <input type="text" name="event_title" placeholder="Event title"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="datetime-local" name="event_start"
                                    class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                                <input type="datetime-local" name="event_end"
                                    class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                            </div>
                            <input type="text" name="event_venue" placeholder="Venue / Location"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                            <input type="url" name="event_ticket_url" placeholder="Ticket URL (optional)"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl transition text-sm">
                            Publish
                        </button>
                    </form>
                </div>
            </div>
            @endif

            @if($posts->count())
                <div class="space-y-4">
                    @foreach($posts as $post)
                    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                        <div class="flex items-center gap-2 mb-3">
                            <img src="{{ $page->avatar }}" class="w-9 h-9 rounded-full object-cover">
                            <div>
                                <p class="font-semibold text-sm text-gray-900">{{ $page->name }}</p>
                                <p class="text-xs text-gray-400">{{ $post->created_at->diffForHumans() }}</p>
                            </div>
                            @if($isOwner)
                            <form method="POST" action="/pages/{{ $page->slug }}/posts/{{ $post->id }}" class="ml-auto">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-500 text-xs" onclick="return confirm('Delete this post?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>

                        @if($post->type === 'event')
                            <div class="bg-blue-50 rounded-xl p-3 mb-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-xs font-semibold text-blue-700 uppercase">Event</span>
                                </div>
                                <p class="font-bold text-gray-900">{{ $post->event_title }}</p>
                                @if($post->event_start)
                                    <p class="text-sm text-gray-600 mt-1">📅 {{ $post->event_start->format('D, M j Y · g:i A') }}</p>
                                @endif
                                @if($post->event_venue)
                                    <p class="text-sm text-gray-600">📍 {{ $post->event_venue }}</p>
                                @endif
                                @if($post->event_ticket_url)
                                    <a href="{{ $post->event_ticket_url }}" target="_blank" class="inline-block mt-2 text-xs text-blue-600 font-semibold hover:underline">Get Tickets →</a>
                                @endif
                            </div>
                        @endif

                        @if($post->body)
                            <p class="text-sm text-gray-800 mb-3 whitespace-pre-line">{{ $post->body }}</p>
                        @endif
                        @if($post->image_url)
                            <img src="{{ $post->image_url }}" class="w-full rounded-xl mb-3 object-cover max-h-80" alt="">
                        @endif
                        @if($post->video_url)
                            <div class="mb-3">
                                <a href="{{ $post->video_url }}" target="_blank" class="inline-flex items-center gap-2 text-blue-600 text-sm font-semibold hover:underline">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                                    Watch Video
                                </a>
                            </div>
                        @endif

                        {{-- Like button --}}
                        @auth
                        <button onclick="likePost(this, '{{ $page->slug }}', {{ $post->id }})"
                            data-liked="{{ $post->isLikedBy(auth()->user()) ? 'true' : 'false' }}"
                            class="flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-xl transition
                                   {{ $post->isLikedBy(auth()->user()) ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            <svg class="w-4 h-4" fill="{{ $post->isLikedBy(auth()->user()) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                            </svg>
                            <span class="like-count">{{ $post->likes_count }}</span> Like
                        </button>
                        @endauth
                    </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $posts->links() }}</div>
            @else
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="text-gray-500 font-medium">No posts yet</p>
                </div>
            @endif
        </div>

        {{-- ========== TAB: Events ========== --}}
        <div x-show="activeTab === 'events'">
            @if($events->count())
                <div class="space-y-4">
                    @foreach($events as $ev)
                    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm flex gap-4">
                        <div class="w-14 flex-shrink-0 text-center">
                            <div class="bg-blue-600 text-white rounded-t-lg py-1 text-xs font-bold uppercase">{{ $ev->event_start->format('M') }}</div>
                            <div class="bg-white border border-gray-200 rounded-b-lg py-1 text-xl font-bold text-gray-900">{{ $ev->event_start->format('j') }}</div>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-900">{{ $ev->event_title }}</p>
                            <p class="text-sm text-gray-500 mt-0.5">{{ $ev->event_start->format('g:i A') }}@if($ev->event_end) – {{ $ev->event_end->format('g:i A') }}@endif</p>
                            @if($ev->event_venue) <p class="text-sm text-gray-500">📍 {{ $ev->event_venue }}</p> @endif
                            @if($ev->body) <p class="text-sm text-gray-700 mt-2 line-clamp-2">{{ $ev->body }}</p> @endif
                            @if($ev->event_ticket_url)
                                <a href="{{ $ev->event_ticket_url }}" target="_blank" class="inline-block mt-2 px-4 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700">Get Tickets</a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16">
                    <p class="text-gray-500 font-medium">No upcoming events</p>
                </div>
            @endif
        </div>

        {{-- ========== TAB: Reviews ========== --}}
        <div x-show="activeTab === 'reviews'">
            {{-- Write a review --}}
            @auth
            @if(!$isOwner)
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 mb-5" x-data="{ rating: {{ $userReview?->rating ?? 0 }}, hover: 0 }">
                <h3 class="font-bold text-gray-900 mb-3">{{ $userReview ? 'Your review' : 'Rate this page' }}</h3>
                <form method="POST" action="/pages/{{ $page->slug }}/reviews">
                    @csrf
                    <div class="flex gap-1 mb-3">
                        @for($s = 1; $s <= 5; $s++)
                        <button type="button" @click="rating = {{ $s }}" @mouseover="hover = {{ $s }}" @mouseleave="hover = 0"
                            class="text-3xl transition" :class="(hover || rating) >= {{ $s }} ? 'text-yellow-400' : 'text-gray-300'">★</button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" :value="rating">
                    <textarea name="body" rows="2" placeholder="Tell others about your experience (optional)"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 resize-none mb-3">{{ $userReview?->body }}</textarea>
                    <div class="flex gap-2">
                        <button type="submit" :disabled="rating === 0"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold py-2.5 rounded-xl transition text-sm">
                            {{ $userReview ? 'Update Review' : 'Submit Review' }}
                        </button>
                        @if($userReview)
                        <form method="POST" action="/pages/{{ $page->slug }}/reviews">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 font-semibold rounded-xl text-sm transition">Delete</button>
                        </form>
                        @endif
                    </div>
                </form>
            </div>
            @endif
            @endauth

            {{-- Rating summary --}}
            @if($page->reviews_count > 0)
            <div class="bg-white border border-gray-100 rounded-2xl p-4 mb-4 flex items-center gap-6">
                <div class="text-center">
                    <p class="text-4xl font-bold text-gray-900">{{ number_format($page->rating_avg, 1) }}</p>
                    <p class="text-yellow-400 text-xl">{{ str_repeat('★', round($page->rating_avg)) }}{{ str_repeat('☆', 5 - round($page->rating_avg)) }}</p>
                    <p class="text-xs text-gray-400">{{ $page->reviews_count }} {{ Str::plural('review', $page->reviews_count) }}</p>
                </div>
                <div class="flex-1 space-y-1">
                    @for($s = 5; $s >= 1; $s--)
                    @php $cnt = $page->reviews()->where('rating', $s)->count(); $pct = $page->reviews_count ? round($cnt / $page->reviews_count * 100) : 0; @endphp
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-gray-500 w-3">{{ $s }}</span>
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="text-gray-400 w-5">{{ $pct }}%</span>
                    </div>
                    @endfor
                </div>
            </div>
            @endif

            {{-- Review list --}}
            @forelse($reviews as $review)
            <div class="bg-white border border-gray-100 rounded-2xl p-4 mb-3 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                        {{ strtoupper(substr($review->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm text-gray-900">{{ $review->user->name }}</p>
                        <div class="flex items-center gap-1">
                            <span class="text-yellow-400 text-sm">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                            <span class="text-xs text-gray-400">· {{ $review->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
                @if($review->body)
                    <p class="text-sm text-gray-700">{{ $review->body }}</p>
                @endif
            </div>
            @empty
            <div class="text-center py-12">
                <p class="text-gray-500 font-medium">No reviews yet</p>
                <p class="text-sm text-gray-400 mt-1">Be the first to leave a review</p>
            </div>
            @endforelse
            @if($reviews->hasPages()) <div class="mt-4">{{ $reviews->links() }}</div> @endif
        </div>

        {{-- ========== TAB: About ========== --}}
        <div x-show="activeTab === 'about'">
            <div class="space-y-5">
                {{-- Details --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-3">Details</h3>
                    <div class="space-y-3 text-sm">
                        @if($page->categories && count($page->categories))
                        <div class="flex items-center gap-3 text-gray-600">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            {{ collect($page->categories)->implode(', ') }}
                        </div>
                        @endif
                        @if($page->location)
                        <div class="flex items-start gap-3 text-gray-600">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $page->location }}
                        </div>
                        @endif
                        @if($page->website)
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            <a href="{{ $page->website }}" target="_blank" class="text-blue-600 hover:underline">{{ $page->website }}</a>
                        </div>
                        @endif
                        @if($page->email)
                        <div class="flex items-center gap-3 text-gray-600">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $page->email }}
                        </div>
                        @endif
                        @if($page->phone)
                        <div class="flex items-center gap-3 text-gray-600">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $page->phone }}
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Business Hours --}}
                @if($page->business_hours)
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Hours
                        @if($isOpen === true)
                            <span class="ml-auto text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-semibold">Open Now</span>
                        @elseif($isOpen === false)
                            <span class="ml-auto text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded-full font-semibold">Closed</span>
                        @endif
                    </h3>
                    @php $days = ['mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thu'=>'Thursday','fri'=>'Friday','sat'=>'Saturday','sun'=>'Sunday']; @endphp
                    <div class="space-y-1.5 text-sm">
                        @foreach($days as $key => $label)
                        @php $slot = $page->business_hours[$key] ?? null; @endphp
                        <div class="flex justify-between text-gray-700">
                            <span class="font-medium">{{ $label }}</span>
                            <span class="text-gray-500">
                                @if(!$slot || ($slot['closed'] ?? false)) Closed
                                @else {{ $slot['open'] ?? '' }} – {{ $slot['close'] ?? '' }}
                                @endif
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Map --}}
                @if($page->lat && $page->lng)
                <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                    <h3 class="font-bold text-gray-900 p-4 pb-0">Location</h3>
                    <div id="page-map" class="w-full h-48 mt-2"></div>
                    <div class="p-3">
                        <a href="https://www.openstreetmap.org/?mlat={{ $page->lat }}&mlon={{ $page->lng }}&zoom=15" target="_blank"
                           class="text-sm text-blue-600 font-semibold hover:underline">Open in map →</a>
                    </div>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const map = L.map('page-map', { zoomControl: false, attributionControl: false }).setView([{{ $page->lat }}, {{ $page->lng }}], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                    L.marker([{{ $page->lat }}, {{ $page->lng }}]).addTo(map).bindPopup('{{ addslashes($page->name) }}').openPopup();
                });
                </script>
                @endif

                {{-- Share --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-3">Share this page</h3>
                    <div class="flex gap-2 flex-wrap">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/pages/'.$page->slug)) }}" target="_blank"
                           class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition">
                            Facebook
                        </a>
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($page->name.' '.url('/pages/'.$page->slug)) }}" target="_blank"
                           class="flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-xl text-sm font-semibold transition">
                            WhatsApp
                        </a>
                        <button onclick="sharePage()" class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition">
                            Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========== TAB: Followers ========== --}}
        <div x-show="activeTab === 'followers'">
            <p class="text-gray-500 text-sm text-center py-12">
                {{ number_format($page->followers_count) }} {{ Str::plural('follower', $page->followers_count) }}
            </p>
        </div>

        <div class="h-10"></div>
    </div>
</div>

<script>
function toggleFollow() {
    const btn = document.getElementById('follow-btn');
    const label = document.getElementById('follow-label');
    const isFollowing = btn.dataset.following === 'true';
    fetch(`/pages/${btn.dataset.slug}/follow`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        btn.dataset.following = data.following ? 'true' : 'false';
        label.textContent = data.following ? 'Following' : 'Follow';
        if (data.following) {
            btn.classList.remove('bg-blue-600','hover:bg-blue-700','text-white');
            btn.classList.add('bg-gray-100','hover:bg-gray-200','text-gray-800');
        } else {
            btn.classList.remove('bg-gray-100','hover:bg-gray-200','text-gray-800');
            btn.classList.add('bg-blue-600','hover:bg-blue-700','text-white');
        }
    });
}

function toggleSavePage() {
    const btn = document.getElementById('save-page-btn');
    const label = document.getElementById('save-page-label');
    fetch('/bookmarks/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ type: 'page', id: btn.dataset.id })
    }).then(r => r.json()).then(data => {
        btn.dataset.saved = data.saved ? 'true' : 'false';
        label.textContent = data.saved ? 'Saved' : 'Save';
        const icon = btn.querySelector('svg');
        icon.setAttribute('fill', data.saved ? 'currentColor' : 'none');
        if (data.saved) {
            btn.classList.replace('bg-gray-100','bg-yellow-50');
            btn.classList.replace('text-gray-700','text-yellow-700');
        } else {
            btn.classList.replace('bg-yellow-50','bg-gray-100');
            btn.classList.replace('text-yellow-700','text-gray-700');
        }
    });
}

function likePost(btn, slug, postId) {
    fetch(`/pages/${slug}/posts/${postId}/like`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        btn.dataset.liked = data.liked ? 'true' : 'false';
        btn.querySelector('.like-count').textContent = data.likes_count;
        const icon = btn.querySelector('svg');
        icon.setAttribute('fill', data.liked ? 'currentColor' : 'none');
        if (data.liked) {
            btn.classList.remove('bg-gray-100','text-gray-600','hover:bg-gray-200');
            btn.classList.add('bg-blue-50','text-blue-600');
        } else {
            btn.classList.remove('bg-blue-50','text-blue-600');
            btn.classList.add('bg-gray-100','text-gray-600','hover:bg-gray-200');
        }
    });
}

function sharePage() {
    const url = '{{ url("/pages/".$page->slug) }}';
    if (navigator.share) {
        navigator.share({ title: '{{ addslashes($page->name) }}', url });
    } else {
        navigator.clipboard.writeText(url).then(() => {
            const lbl = document.getElementById('share-label');
            if (lbl) { lbl.textContent = 'Copied!'; setTimeout(() => lbl.textContent = 'Share', 2000); }
        });
    }
}
</script>
@endsection
