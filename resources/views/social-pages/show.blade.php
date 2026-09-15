@extends('layouts.app')

@section('title', $page->name)

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" defer></script>
@endpush

@section('content')
<div class="bg-gray-100 dark:bg-gray-900 min-h-screen" x-data="{ activeTab: 'posts' }">

    {{-- SEO & Open Graph --}}
    @section('og')
    {{-- Primary meta --}}
    <meta name="description" content="{{ $page->bio ?: ($page->first_category ? $page->name.' – '.$page->first_category.' page' : $page->name.' – Official Page') }}">
    <meta name="keywords" content="{{ collect($page->categories ?? [])->implode(', ') }}{{ $page->location ? ', '.$page->location : '' }}">
    <link rel="canonical" href="{{ url('/pages/'.$page->slug) }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $page->name }}{{ $page->is_verified ? ' ✓' : '' }}">
    <meta property="og:description" content="{{ $page->bio ?: ($page->first_category ? $page->name.' – '.$page->first_category : $page->name.' page') }}">
    <meta property="og:image" content="{{ $page->avatar_url ? url($page->avatar_url) : '' }}">
    <meta property="og:url" content="{{ url('/pages/'.$page->slug) }}">
    <meta property="og:type" content="{{ $page->page_type === 'business' ? 'business.business' : 'website' }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    @if($page->phone)<meta property="business:contact_data:phone_number" content="{{ $page->phone }}">@endif
    @if($page->location)<meta property="business:contact_data:locality" content="{{ $page->location }}">@endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $page->name }}">
    <meta name="twitter:description" content="{{ $page->bio ?: $page->first_category }}">
    @if($page->avatar_url)<meta name="twitter:image" content="{{ url($page->avatar_url) }}">@endif

    {{-- JSON-LD structured data --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "{{ $page->page_type === 'business' ? 'LocalBusiness' : 'Organization' }}",
      "name": "{{ addslashes($page->name) }}",
      "url": "{{ url('/pages/'.$page->slug) }}",
      @if($page->avatar_url)"logo": "{{ url($page->avatar_url) }}",@endif
      @if($page->bio)"description": "{{ addslashes($page->bio) }}",@endif
      @if($page->email)"email": "{{ $page->email }}",@endif
      @if($page->phone)"telephone": "{{ $page->phone }}",@endif
      @if($page->location)"address": { "@type": "PostalAddress", "addressLocality": "{{ addslashes($page->location) }}" },@endif
      @if($page->lat && $page->lng)"geo": { "@type": "GeoCoordinates", "latitude": {{ $page->lat }}, "longitude": {{ $page->lng }} },@endif
      @if($page->website)"sameAs": ["{{ $page->website }}"],@endif
      "interactionStatistic": {
        "@type": "InteractionCounter",
        "interactionType": "https://schema.org/FollowAction",
        "userInteractionCount": {{ $page->followers_count }}
      }
    }
    </script>
    @endsection

    {{-- Cover photo --}}
    <div class="relative h-52 md:h-72 bg-gray-300 overflow-hidden">
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

    {{-- ── FB-style profile section ────────────────────────── --}}
    <div class="max-w-5xl mx-auto px-0 md:px-4">

        {{-- White card containing avatar + info + actions --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm pb-3 mb-3">

        {{-- Avatar + name row --}}
        <div class="flex items-end gap-4 px-4 -mt-16 mb-3">
            <div class="relative flex-shrink-0">
                <div class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 overflow-hidden bg-gray-200 shadow-md">
                    <img src="{{ $page->avatar }}" alt="{{ $page->name }}" class="w-full h-full object-cover">
                </div>
                @if($page->last_post_at && $page->last_post_at->gt(now()->subDays(7)))
                <span class="absolute bottom-1 right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full" title="Active recently"></span>
                @endif
                @if($isOwner)
                <label class="absolute bottom-0 right-0 bg-gray-100 hover:bg-gray-200 rounded-full p-1.5 cursor-pointer shadow border border-white transition">
                    <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </label>
                @endif
            </div>
            <div class="flex-1 pb-1 min-w-0">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white leading-tight">{{ $page->name }}</h1>
                    @if($page->is_verified)
                        <svg class="w-6 h-6 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @endif
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    @if($page->first_category)<span>{{ $page->first_category }}</span>@if($page->username) &middot; @endif
                    @endif
                    @if($page->username)<span class="text-gray-400">&#64;{{ $page->username }}</span>@endif
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5">
                    <strong>{{ number_format($page->followers_count) }}</strong> followers
                    @if(($posts->total() ?? 0) > 0) &middot; <strong>{{ number_format($posts->total()) }}</strong> posts @endif
                    @if($page->reviews_count > 0) &middot; ⭐ {{ number_format($page->rating_avg, 1) }} @endif
                </p>
            </div>
        </div>

        {{-- Bio + meta --}}
        <div class="px-4 mb-3" x-data="{ expanded: false }">
            @if($page->bio)
            <p class="text-gray-900 text-sm leading-relaxed" :class="expanded ? '' : 'line-clamp-2'">{{ $page->bio }}</p>
            @if(strlen($page->bio) > 120)
            <button @click="expanded = !expanded" class="text-blue-600 text-sm font-semibold mt-0.5" x-text="expanded ? 'See less' : 'See more'"></button>
            @endif
            @endif
            {{-- Category + hours badge --}}
            @if($page->first_category || $isOpen !== null)
            <div class="flex items-center gap-2 mt-2 text-sm text-gray-700 font-semibold">
                <svg class="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>{{ $page->first_category ?: ($page->page_type === 'business' ? 'Business' : 'Organization') }}</span>
                @if($isOpen === true)
                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-semibold">Open Now</span>
                @elseif($isOpen === false)
                    <span class="text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded-full font-semibold">Closed</span>
                @endif
            </div>
            @endif
            @if($page->username)
            <p class="text-sm text-gray-400 mt-1">&#64;{{ $page->username }}</p>
            @endif
        </div>

        {{-- Managing-as banner for non-owner managers --}}
        @if($isManager && !$isOwner)
        <div class="mx-4 mb-3 px-4 py-2.5 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-xl flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span class="text-sm text-blue-700 dark:text-blue-300 font-medium">
                    You are managing this page as <strong class="capitalize">{{ $userRole }}</strong>
                </span>
            </div>
            <a href="/pages/{{ $page->slug }}/dashboard" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline whitespace-nowrap">Dashboard →</a>
        </div>
        @endif

        {{-- Action buttons --}}
        <div class="px-4 mb-3">
            <div class="flex gap-2">
                @if($isManager)
                    <a href="/pages/{{ $page->slug }}/dashboard"
                       class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Dashboard
                    </a>
                    @if($isOwner)
                    <a href="/pages/{{ $page->slug }}/settings"
                       class="flex-1 flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2.5 rounded-xl transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                        Settings
                    </a>
                    @endif
                @else
                    @auth
                        <button id="follow-btn" onclick="toggleFollow()"
                            class="flex-1 flex items-center justify-center gap-2 font-bold py-2.5 rounded-xl transition text-sm {{ $isFollowing ? 'bg-gray-100 hover:bg-gray-200 text-gray-800' : 'bg-blue-600 hover:bg-blue-700 text-white' }}"
                            data-following="{{ $isFollowing ? 'true' : 'false' }}" data-slug="{{ $page->slug }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isFollowing ? 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' : 'M12 4v16m8-8H4' }}"/></svg>
                            <span id="follow-label">{{ $isFollowing ? 'Following' : 'Follow' }}</span>
                        </button>
                        <button onclick="sharePage()"
                            class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl transition text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            Share
                        </button>
                    @else
                        <a href="/login" class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl transition text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Follow
                        </a>
                        <button onclick="sharePage()"
                            class="flex-1 flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2.5 rounded-xl transition text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            Share
                        </button>
                    @endauth
                @endif
                {{-- Action Button (e.g. Book Now, Contact Us) --}}
                @if($page->action_button_type && $page->action_button_url)
                <a href="{{ $page->action_button_url }}" target="_blank" rel="noopener"
                    class="flex-shrink-0 flex items-center justify-center gap-1.5 bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-3 rounded-xl transition text-sm whitespace-nowrap">
                    {{ $page->action_button_text ?: $page->action_button_type }}
                </a>
                @endif
                {{-- Donation button --}}
                @if($page->donation_url)
                <a href="{{ $page->donation_url }}" target="_blank" rel="noopener"
                    class="flex-shrink-0 flex items-center justify-center gap-1.5 bg-rose-500 hover:bg-rose-600 text-white font-bold py-2.5 px-3 rounded-xl transition text-sm whitespace-nowrap">
                    ❤️ {{ $page->donation_label ?: 'Donate' }}
                </a>
                @endif
                {{-- "..." three-dot menu --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open"
                        class="flex items-center justify-center w-11 h-11 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/></svg>
                    </button>
                    <div x-show="open" x-cloak
                        class="absolute right-0 top-full mt-1 w-52 bg-white border border-gray-200 rounded-2xl shadow-2xl z-50 py-1"
                        style="filter:drop-shadow(0 8px 24px rgba(0,0,0,.15))">
                        @auth
                        @if(!$isOwner)
                        {{-- Notification prefs --}}
                        @if($isFollowing)
                        <div x-data="{ notifOpen: false }" class="border-b border-gray-100">
                            <button @click="notifOpen = !notifOpen; $event.stopPropagation()"
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                Notification settings
                            </button>
                            <div x-show="notifOpen" x-cloak class="px-4 pb-3">
                                <form method="POST" action="/pages/{{ $page->slug }}/notification-prefs" class="space-y-2">
                                    @csrf
                                    @php $prefs = $notifPrefs ?? null; @endphp
                                    <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer">
                                        <input type="checkbox" name="notify_posts" value="1" class="accent-blue-600 w-4 h-4" {{ ($prefs && $prefs->notify_posts) ? 'checked' : '' }}>
                                        New posts
                                    </label>
                                    <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer">
                                        <input type="checkbox" name="notify_events" value="1" class="accent-blue-600 w-4 h-4" {{ ($prefs && $prefs->notify_events) ? 'checked' : '' }}>
                                        Events
                                    </label>
                                    <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer">
                                        <input type="checkbox" name="notify_announcements" value="1" class="accent-blue-600 w-4 h-4" {{ ($prefs && $prefs->notify_announcements) ? 'checked' : '' }}>
                                        Announcements
                                    </label>
                                    <button type="submit" class="mt-1 w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-1.5 rounded-lg transition">Save</button>
                                </form>
                            </div>
                        </div>
                        @endif
                        <button id="save-page-btn" onclick="toggleSavePage()" @click="open=false"
                            class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3"
                            data-saved="{{ ($isSaved ?? false) ? 'true' : 'false' }}" data-id="{{ $page->id }}">
                            <svg class="w-5 h-5 text-gray-500" fill="{{ ($isSaved ?? false) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                            <span id="save-page-label">{{ ($isSaved ?? false) ? 'Saved' : 'Save page' }}</span>
                        </button>
                        @endif
                        @endauth
                        <button onclick="sharePage()" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            Share page
                        </button>
                        <button onclick="copyPageUrl()" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Copy link
                        </button>
                        @auth
                        @if(!$isOwner)
                        <div class="my-1 border-t border-gray-100"></div>
                        <button onclick="showReportPage()" @click="open=false" class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Report page
                        </button>
                        @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        {{-- Report page modal --}}
        @auth
        @if(!$isOwner)
        <div id="report-page-modal" class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center p-4 hidden">
            <div class="bg-white rounded-2xl w-full max-w-sm">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Report this page</h3>
                    <button onclick="hideReportPage()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="/pages/{{ $page->slug }}/report" class="p-5 space-y-3">
                    @csrf
                    <div class="space-y-2">
                        @foreach(['spam'=>'Spam','inappropriate'=>'Inappropriate content','harassment'=>'Harassment','fake'=>'Fake / impersonation','other'=>'Other'] as $val => $lbl)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="reason" value="{{ $val }}" class="accent-blue-600" @if($loop->first) checked @endif>
                            <span class="text-sm text-gray-700">{{ $lbl }}</span>
                        </label>
                        @endforeach
                    </div>
                    <textarea name="details" rows="2" placeholder="Additional details (optional)"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 resize-none"></textarea>
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-xl text-sm transition">Submit Report</button>
                </form>
            </div>
        </div>
        @endif
        @endauth

        {{-- Announcement banner --}}
        @if($page->announcement)
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-4 flex items-start gap-3">
            <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"/></svg>
            <p class="text-sm text-amber-800">{{ $page->announcement }}</p>
        </div>
        @endif

        </div>{{-- /white card --}}

        {{-- Sticky tab bar --}}
        <div class="sticky top-0 z-20 bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 flex items-center overflow-x-auto px-2 md:px-4">
            @foreach(['posts'=>'All','photos'=>'Photos','qna'=>'Q&A','events'=>'Events','reviews'=>'Reviews','about'=>'About'] as $tab => $label)
                <button @click="activeTab = '{{ $tab }}'"
                    class="pt-4 pb-4 px-3 md:px-4 text-[15px] font-semibold whitespace-nowrap transition-colors border-b-[3px]"
                    :class="activeTab === '{{ $tab }}' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-t'">
                    {{ $label }}
                    @if($tab === 'reviews' && $page->reviews_count > 0)
                        <span class="ml-1 text-xs bg-gray-100 px-1.5 py-0.5 rounded-full">{{ $page->reviews_count }}</span>
                    @endif
                    @if($tab === 'photos' && $photos->total() > 0)
                        <span class="ml-1 text-xs bg-gray-100 px-1.5 py-0.5 rounded-full">{{ $photos->total() }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- ========== TAB: Posts ========== --}}
        <div x-show="activeTab === 'posts'" class="mt-3 md:flex md:gap-4 md:items-start">

            {{-- ── LEFT SIDEBAR (desktop only) ── --}}
            <div class="hidden md:block w-[360px] flex-shrink-0 space-y-4">

                {{-- Details card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-3">Details</h3>
                    <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                        @if($page->bio)
                        <p class="leading-relaxed">{{ $page->bio }}</p>
                        @endif
                        @if($page->first_category || $isOpen !== null)
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>{{ $page->first_category ?: ($page->page_type === 'business' ? 'Business' : 'Organization') }}</span>
                            @if($isOpen === true)
                                <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-semibold">Always open</span>
                            @elseif($isOpen === false)
                                <span class="text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded-full font-semibold">Closed now</span>
                            @endif
                        </div>
                        @endif
                        @if($page->reviews_count > 0)
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            <span>{{ number_format($page->rating_avg, 0) }}% recommend ({{ number_format($page->reviews_count) }} reviews)</span>
                        </div>
                        @endif
                        @if($page->location)
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span>{{ $page->location }}</span>
                        </div>
                        @endif
                        @if($page->website)
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            <a href="{{ $page->website }}" target="_blank" class="text-blue-600 hover:underline truncate">{{ $page->website }}</a>
                        </div>
                        @endif
                        @if($page->email)
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>{{ $page->email }}</span>
                        </div>
                        @endif
                        @if($page->phone)
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>{{ $page->phone }}</span>
                        </div>
                        @endif
                        @if($page->username)
                        <div class="flex items-center gap-2 text-gray-400">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>&#64;{{ $page->username }}</span>
                        </div>
                        @endif
                    </div>
                    <button @click="activeTab = 'about'" class="mt-4 w-full py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl transition">
                        See all details
                    </button>
                </div>

                {{-- Photos card --}}
                @if($photos->count())
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-gray-900 dark:text-white">Photos</h3>
                        <button @click="activeTab = 'photos'" class="text-sm font-semibold text-blue-600 hover:underline">See all photos</button>
                    </div>
                    <div class="grid grid-cols-3 gap-1 rounded-xl overflow-hidden">
                        @foreach($photos->take(9) as $photo)
                        <a href="{{ $photo->image_url }}" target="_blank" class="aspect-square overflow-hidden bg-gray-100 block">
                            <img src="{{ $photo->image_url }}" class="w-full h-full object-cover hover:opacity-90 transition" alt="">
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Highlights --}}
                @if($page->highlights && count($page->highlights))
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-3">Highlights</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($page->highlights as $hl)
                        <a href="{{ $hl['url'] ?? '#' }}" target="_blank"
                           class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <span>{{ $hl['icon'] ?? '🔗' }}</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $hl['title'] ?? '' }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>{{-- /left sidebar --}}

            {{-- ── RIGHT COLUMN: Posts feed ── --}}
            <div class="flex-1 min-w-0">

            {{-- Stories carousel --}}
            @if(isset($activeStories) && $activeStories->count())
            <div class="bg-white dark:bg-gray-800 rounded-2xl px-4 py-3 overflow-x-auto mb-3">
                <div class="flex gap-3">
                    @foreach($activeStories as $story)
                    <div class="flex-shrink-0 w-20 cursor-pointer" x-data="{ show: false }">
                        <div @click="show = true"
                            class="w-20 h-28 rounded-2xl overflow-hidden relative border-2 border-blue-500"
                            style="{{ $story->image_url ? '' : 'background:'.($story->bg_color ?? '#1877f2') }}">
                            @if($story->image_url)<img src="{{ $story->image_url }}" class="w-full h-full object-cover">@endif
                            @if($story->caption)
                            <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/60 to-transparent px-1 pb-1">
                                <p class="text-white text-[9px] font-medium truncate">{{ $story->caption }}</p>
                            </div>
                            @endif
                        </div>
                        <div x-show="show" x-cloak @click="show = false" class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4">
                            <div class="max-w-sm w-full" @click.stop>
                                <div class="rounded-2xl overflow-hidden relative aspect-[9/16]" style="{{ $story->image_url ? '' : 'background:'.($story->bg_color ?? '#1877f2') }}">
                                    @if($story->image_url)<img src="{{ $story->image_url }}" class="w-full h-full object-cover">@endif
                                    @if($story->caption)<div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/70 to-transparent p-4"><p class="text-white text-base font-medium">{{ $story->caption }}</p></div>@endif
                                    <button @click="show = false" class="absolute top-3 right-3 w-8 h-8 bg-black/40 rounded-full flex items-center justify-center text-white"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            {{-- Posts header --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm px-4 py-3 mb-3 flex items-center justify-between">
                <h2 class="font-bold text-gray-900 dark:text-white text-lg">Posts</h2>
                <div class="relative" x-data="{ fOpen: false }" @click.outside="fOpen = false">
                    <button @click="fOpen = !fOpen" class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0014 13.828V19a1 1 0 01-.553.894l-4 2A1 1 0 018 21v-7.172a1 1 0 00-.293-.707L1.293 6.707A1 1 0 011 6V4z"/></svg>
                        Filters
                    </button>
                </div>
            </div>

            {{-- Create post form (managers only) --}}
            @if($isManager)
            {{-- ── FB-Lite style composer trigger bar ──────────────────── --}}
            <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-2xl px-3 py-2.5 mb-4 shadow-sm"
                 x-data="{ open: false }" @keydown.escape.window="open = false">
                <img src="{{ $page->avatar }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0 border border-gray-200">
                <button @click="open = true"
                    class="flex-1 text-left text-gray-400 bg-gray-100 hover:bg-gray-200 rounded-full px-4 py-2 text-sm transition">
                    What's on your mind?
                </button>
                <button @click="open = true"
                    class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 transition">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </button>

                {{-- ── Full-screen composer modal ─────────────────────── --}}
                <div x-show="open" x-cloak
                     x-data="{ postType: 'text', postFormat: 'standard', pollOptions: ['', ''] }"
                     class="fixed inset-0 z-50 bg-white flex flex-col"
                     style="max-width:480px; margin:0 auto;">

                    {{-- Header --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-white sticky top-0">
                        <button @click="open = false" class="p-1 -ml-1 rounded-full hover:bg-gray-100 transition">
                            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <span class="font-bold text-gray-900 text-base">Create post</span>
                        <button form="composer-form" type="submit"
                            class="text-blue-600 font-bold text-base px-2 hover:text-blue-700 transition">POST</button>
                    </div>

                    {{-- Author row --}}
                    <div class="flex items-center gap-3 px-4 py-3">
                        <img src="{{ $page->avatar }}" class="w-11 h-11 rounded-full object-cover border border-gray-200 flex-shrink-0">
                        <div>
                            <p class="font-bold text-gray-900 text-sm leading-tight">{{ $page->name }}</p>
                            {{-- Format selector pill --}}
                            <div class="relative" x-data="{ fmtOpen: false }" @click.outside="fmtOpen = false">
                                <button type="button" @click="fmtOpen = !fmtOpen"
                                    class="flex items-center gap-1 mt-0.5 px-2.5 py-0.5 border border-gray-300 rounded-full text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                                    <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    <span x-text="{ standard:'📝 Post', article:'📰 Article', poll:'📊 Poll', qna:'❓ Q&A' }[postFormat]"></span>
                                </button>
                                <div x-show="fmtOpen" class="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-10 py-1 min-w-[140px]">
                                    @foreach(['standard'=>'📝 Post','article'=>'📰 Article','poll'=>'📊 Poll','qna'=>'❓ Q&A'] as $fmt => $lbl)
                                    <button type="button" @click="postFormat = '{{ $fmt }}'; fmtOpen = false"
                                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50"
                                        :class="postFormat === '{{ $fmt }}' ? 'text-blue-600 font-bold' : 'text-gray-700'">{{ $lbl }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Composer body (scrollable) --}}
                    <form id="composer-form" method="POST" action="/pages/{{ $page->slug }}/posts"
                          enctype="multipart/form-data" class="flex-1 overflow-y-auto flex flex-col">
                        @csrf
                        <input type="hidden" name="post_format" :value="postFormat">
                        <input type="hidden" name="type" :value="postFormat !== 'standard' ? 'text' : postType">

                        {{-- Article headline --}}
                        <div x-show="postFormat === 'article'" class="px-4 pb-2">
                            <input type="text" name="article_title" placeholder="Headline..."
                                class="w-full text-lg font-bold text-gray-900 border-0 border-b border-gray-200 pb-2 focus:outline-none focus:border-blue-400 bg-transparent">
                        </div>

                        {{-- Main textarea --}}
                        <div x-show="postFormat !== 'poll'" class="flex-1 px-4">
                            <textarea name="body" rows="6"
                                :placeholder="postFormat === 'article' ? 'Write your article...' : postFormat === 'qna' ? 'Ask your community a question...' : 'What\'s on your mind?'"
                                class="w-full text-base text-gray-800 border-0 focus:outline-none resize-none bg-transparent placeholder-gray-400 leading-relaxed"></textarea>
                        </div>

                        {{-- Poll body + options --}}
                        <div x-show="postFormat === 'poll'" class="px-4 space-y-3">
                            <textarea name="body" rows="3" placeholder="Ask a question..."
                                class="w-full text-base text-gray-800 border-0 focus:outline-none resize-none bg-transparent placeholder-gray-400 leading-relaxed"></textarea>
                            <div class="space-y-2">
                                <template x-for="(opt, i) in pollOptions" :key="i">
                                    <div class="flex items-center gap-2 border border-gray-300 rounded-xl px-3 py-2">
                                        <span class="text-gray-400 text-xs font-bold w-5 text-center" x-text="i+1"></span>
                                        <input type="text" :name="'poll_options[' + i + ']'" x-model="pollOptions[i]"
                                            :placeholder="'Option ' + (i+1)"
                                            class="flex-1 text-sm text-gray-800 border-0 focus:outline-none bg-transparent">
                                        <button type="button" x-show="pollOptions.length > 2"
                                            @click="pollOptions.splice(i,1)"
                                            class="text-gray-400 hover:text-red-500 text-lg leading-none">×</button>
                                    </div>
                                </template>
                                <button type="button" x-show="pollOptions.length < 6"
                                    @click="pollOptions.push('')"
                                    class="text-blue-600 text-sm font-semibold hover:underline">+ Add option</button>
                            </div>
                        </div>

                        {{-- Photo attachment preview --}}
                        <div x-show="postFormat === 'standard' && postType === 'photo'" class="px-4 py-2">
                            <label class="flex items-center gap-3 border-2 border-dashed border-gray-300 rounded-xl p-4 cursor-pointer hover:bg-gray-50 transition">
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-sm text-gray-500">Tap to choose photo</span>
                                <input type="file" name="image" accept="image/*" class="hidden">
                            </label>
                        </div>
                        <div x-show="postFormat === 'standard' && postType === 'video'" class="px-4 py-2">
                            <input type="url" name="video_url" placeholder="Video URL (YouTube, TikTok...)"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        <div x-show="postFormat === 'standard' && postType === 'event'" class="px-4 py-2 space-y-2">
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

                        {{-- Action row (media type shortcuts) --}}
                        <div class="border-t border-gray-100 mt-auto">
                            <div x-show="postFormat === 'standard'" class="divide-y divide-gray-100">
                                @foreach([
                                    ['photo',  'Photos/Videos',    'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'text-green-600'],
                                    ['video',  'Video URL',        'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', 'text-red-500'],
                                    ['event',  'Create event',     'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'text-red-500'],
                                ] as [$t, $lbl, $path, $color])
                                <button type="button" @click="postType = '{{ $t }}'"
                                    class="w-full flex items-center gap-4 px-4 py-3 hover:bg-gray-50 transition text-left"
                                    :class="postType === '{{ $t }}' ? 'bg-blue-50' : ''">
                                    <svg class="w-6 h-6 {{ $color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
                                    <span class="text-sm font-medium text-gray-700">{{ $lbl }}</span>
                                    <svg x-show="postType === '{{ $t }}'" class="w-4 h-4 text-blue-600 ml-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </button>
                                @endforeach
                            </div>

                            {{-- Big POST button --}}
                            <div class="px-4 py-3">
                                <button type="submit"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-base transition">
                                    POST
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @if($posts->count())
                <div class="space-y-3">
                    @foreach($posts as $post)
                    @php
                        $myReaction = auth()->check() ? $post->reactionBy(auth()->user()) : null;
                        $topReactions = $post->topReactions(3);
                        $reactionEmojis = ['like'=>'👍','love'=>'❤️','haha'=>'😂','wow'=>'😮','sad'=>'😢','angry'=>'😡'];
                        $isManager = $isOwner || in_array($userRole, ['admin','editor','moderator']);
                    @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-2xl mb-3 py-4 shadow-sm" x-data="{ showComments: false, commentLoaded: false, comments: [], commentBody: '' }">
                        {{-- Post header --}}
                        <div class="flex items-center gap-2.5 px-4 mb-3">
                            <img src="{{ $page->avatar }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <p class="font-bold text-sm text-gray-900">{{ $page->name }}</p>
                                    @if($page->is_verified)
                                        <svg class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400">{{ $post->created_at->diffForHumans() }}</p>
                            </div>
                            {{-- Post "..." menu --}}
                            <div class="relative flex-shrink-0" x-data="{ postMenuOpen: false }" @click.outside="postMenuOpen = false">
                                <button @click="postMenuOpen = !postMenuOpen" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/></svg>
                                </button>
                                <div x-show="postMenuOpen" x-cloak
                                    class="absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden py-1">
                                    <button onclick="copyPostUrl('{{ $page->slug }}', {{ $post->id }})"
                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        Copy link
                                    </button>
                                    @auth
                                    @if($isManager)
                                        @if($page->pinned_post_id === $post->id)
                                        <form method="POST" action="/pages/{{ $page->slug }}/posts/{{ $post->id }}/pin">
                                            @csrf @method('DELETE')
                                            <button class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Unpin post
                                            </button>
                                        </form>
                                        @elseif($isOwner)
                                        <form method="POST" action="/pages/{{ $page->slug }}/posts/{{ $post->id }}/pin">
                                            @csrf
                                            <button class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                                Pin post
                                            </button>
                                        </form>
                                        @endif
                                        <hr class="my-1 border-gray-100">
                                        <form method="POST" action="/pages/{{ $page->slug }}/posts/{{ $post->id }}" onsubmit="return confirm('Delete this post?')">
                                            @csrf @method('DELETE')
                                            <button class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Delete post
                                            </button>
                                        </form>
                                    @else
                                        <hr class="my-1 border-gray-100">
                                        <form method="POST" action="/pages/{{ $page->slug }}/posts/{{ $post->id }}/report">
                                            @csrf
                                            <input type="hidden" name="reason" value="spam">
                                            <button class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                Report post
                                            </button>
                                        </form>
                                    @endif
                                    @endauth
                                </div>
                            </div>
                        </div>

                        {{-- Article title --}}
                        @if(($post->post_format ?? 'standard') === 'article' && $post->article_title)
                        <div class="px-4 mb-2">
                            <span class="text-xs font-bold text-purple-600 uppercase tracking-wide bg-purple-50 px-2 py-0.5 rounded-full">Article</span>
                            <h2 class="text-lg font-bold text-gray-900 mt-1 leading-snug">{{ $post->article_title }}</h2>
                        </div>
                        @endif

                        {{-- QnA format label --}}
                        @if(($post->post_format ?? 'standard') === 'qna')
                        <div class="px-4 mb-1">
                            <span class="text-xs font-bold text-green-600 uppercase tracking-wide bg-green-50 px-2 py-0.5 rounded-full">Q&amp;A</span>
                        </div>
                        @endif

                        {{-- Event card --}}
                        @if($post->type === 'event')
                        <div class="mx-4 bg-blue-50 rounded-xl p-3 mb-3">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-xs font-bold text-blue-700 uppercase tracking-wide">Event</span>
                            </div>
                            <p class="font-bold text-gray-900">{{ $post->event_title }}</p>
                            @if($post->event_start)
                                <p class="text-sm text-gray-600 mt-1">📅 {{ $post->event_start->format('D, M j Y · g:i A') }}</p>
                            @endif
                            @if($post->event_venue)
                                <p class="text-sm text-gray-600">📍 {{ $post->event_venue }}</p>
                            @endif
                            @if($post->event_ticket_url)
                                <a href="{{ $post->event_ticket_url }}" target="_blank" class="inline-block mt-2 px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition">Get Tickets →</a>
                            @endif
                        </div>
                        @endif

                        {{-- Post body --}}
                        @if($post->body)
                            <p class="px-4 text-sm text-gray-800 mb-3 whitespace-pre-line leading-relaxed">{{ $post->body }}</p>
                        @endif
                        @if($post->image_url)
                            <img src="{{ $post->image_url }}" class="w-full mb-3 object-cover max-h-96" alt="">
                        @endif
                        @if($post->video_url)
                            <div class="px-4 mb-3">
                                <a href="{{ $post->video_url }}" target="_blank" class="inline-flex items-center gap-2 text-blue-600 text-sm font-semibold hover:underline">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                                    Watch Video
                                </a>
                            </div>
                        @endif

                        {{-- Poll options --}}
                        @if(($post->post_format ?? 'standard') === 'poll' && $post->pollOptions->count())
                        @php
                            $totalVotes = $post->pollVotes->count();
                            $userVotedOption = $userVoteMap[$post->id] ?? null;
                        @endphp
                        <div class="px-4 mb-3 space-y-2" x-data="{ voted: {{ $userVotedOption ?? 'null' }}, total: {{ $totalVotes }} }">
                            @foreach($post->pollOptions as $opt)
                            @php $pct = $totalVotes > 0 ? round($opt->votes_count / $totalVotes * 100) : 0; @endphp
                            <button type="button"
                                @auth onclick="votePoll('{{ $page->slug }}', {{ $post->id }}, {{ $opt->id }}, this)" @else onclick="window.location='/login'" @endauth
                                data-opt-id="{{ $opt->id }}"
                                class="relative w-full text-left rounded-xl border-2 px-4 py-2.5 text-sm font-medium overflow-hidden transition
                                    {{ $userVotedOption == $opt->id ? 'border-blue-500 text-blue-700' : 'border-gray-200 text-gray-700 hover:border-blue-300' }}">
                                <div class="absolute inset-0 bg-blue-100 transition-all" style="width: {{ $pct }}%; opacity:{{ $userVotedOption ? '1' : '0' }}"></div>
                                <span class="relative flex justify-between">
                                    <span>{{ $opt->text }}</span>
                                    @if($userVotedOption)
                                    <span class="text-xs text-gray-500">{{ $pct }}%</span>
                                    @endif
                                </span>
                            </button>
                            @endforeach
                            <p class="text-xs text-gray-400 text-right">{{ $totalVotes }} {{ Str::plural('vote', $totalVotes) }}</p>
                        </div>
                        @endif

                        {{-- Reaction summary bar --}}
                        @if($post->likes_count > 0)
                        <div class="px-4 pb-2 flex items-center justify-between text-xs text-gray-500">
                            <div class="flex items-center gap-1">
                                @foreach($topReactions as $r => $cnt)
                                    <span>{{ $reactionEmojis[$r] ?? '👍' }}</span>
                                @endforeach
                                <span class="ml-1">{{ $post->likes_count }}</span>
                            </div>
                            <span>{{ $post->comments_count }} {{ Str::plural('comment', $post->comments_count) }}</span>
                        </div>
                        @endif

                        {{-- Action bar --}}
                        <div class="px-4 pt-2 border-t border-gray-100 flex items-center gap-0.5">
                            @auth
                            {{-- Reaction button with hover picker --}}
                            <div class="relative group flex-1"
                                 x-data="{ pickerOpen: false }"
                                 @mouseenter="pickerOpen = true"
                                 @mouseleave="pickerOpen = false">
                                <button
                                    onclick="reactPost(this, '{{ $page->slug }}', {{ $post->id }}, '{{ $myReaction ?? 'like' }}')"
                                    data-reaction="{{ $myReaction }}"
                                    class="flex items-center justify-center gap-1.5 w-full py-2 rounded-xl text-sm font-semibold transition select-none
                                           {{ $myReaction ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:bg-gray-100' }}">
                                    <span class="reaction-emoji text-base">{{ $myReaction ? ($reactionEmojis[$myReaction] ?? '👍') : '👍' }}</span>
                                    <span class="reaction-label">{{ $myReaction ? ucfirst($myReaction) : 'Like' }}</span>
                                </button>
                                {{-- Emoji picker popover --}}
                                <div x-show="pickerOpen" x-cloak
                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-white border border-gray-200 rounded-2xl shadow-xl px-3 py-2 flex gap-1 z-30 whitespace-nowrap">
                                    @foreach($reactionEmojis as $rKey => $rEmoji)
                                    <button type="button"
                                        onclick="reactPost(document.querySelector('[data-post-id=\'{{ $post->id }}\']'), '{{ $page->slug }}', {{ $post->id }}, '{{ $rKey }}'); pickerOpen = false"
                                        class="text-2xl hover:scale-125 transition-transform p-1 rounded-lg hover:bg-gray-50"
                                        title="{{ ucfirst($rKey) }}">{{ $rEmoji }}</button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Comment button --}}
                            <button
                                @click="showComments = !showComments; if(!commentLoaded){ loadComments('{{ $page->slug }}', {{ $post->id }}, $data) }"
                                class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                Comment
                            </button>
                            @else
                            <a href="/login" class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-100 transition">
                                <span class="text-base">👍</span> Like
                            </a>
                            @endauth

                        </div>

                        {{-- Comment section --}}
                        @auth
                        <div x-show="showComments" x-cloak class="border-t border-gray-100 mt-2 px-4 pt-3 pb-1" data-post-id="{{ $post->id }}">
                            {{-- Comment list --}}
                            <div class="space-y-2.5 mb-3" x-ref="commentList">
                                <template x-for="c in comments" :key="c.id">
                                    <div class="flex gap-2.5">
                                        <img :src="c.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(c.author) + '&size=32&background=e5e7eb&color=6b7280'"
                                            class="w-8 h-8 rounded-full object-cover flex-shrink-0 border border-gray-200"
                                            onerror="this.src='https://ui-avatars.com/api/?name=U&size=32&background=e5e7eb&color=6b7280'">
                                        <div class="flex-1">
                                            <div class="bg-gray-100 rounded-2xl px-3 py-2">
                                                <p class="text-xs font-bold text-gray-900" x-text="c.author"></p>
                                                <p class="text-sm text-gray-800 mt-0.5" x-text="c.body"></p>
                                            </div>
                                            <p class="text-xs text-gray-400 mt-1 ml-1" x-text="c.time"></p>
                                        </div>
                                    </div>
                                </template>
                                <p x-show="comments.length === 0 && commentLoaded" class="text-xs text-gray-400 text-center py-2">No comments yet. Be first!</p>
                            </div>
                            {{-- Comment input --}}
                            <div class="flex gap-2 items-start">
                                <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name ?? 'U').'&size=32&background=e5e7eb&color=6b7280' }}"
                                    class="w-8 h-8 rounded-full object-cover flex-shrink-0 border border-gray-200">
                                <div class="flex-1 flex gap-2">
                                    <input type="text" x-model="commentBody"
                                        placeholder="Write a comment..."
                                        class="flex-1 bg-gray-100 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @keydown.enter="postComment('{{ $page->slug }}', {{ $post->id }}, $data)">
                                    <button @click="postComment('{{ $page->slug }}', {{ $post->id }}, $data)"
                                        class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-full transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
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
            </div>{{-- /right column --}}
        </div>

        {{-- ========== TAB: Events ========== --}}
        <div x-show="activeTab === 'events'" class="mt-3">
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
        <div x-show="activeTab === 'reviews'" class="mt-3">
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
        <div x-show="activeTab === 'about'" class="mt-3">
            <div class="space-y-5">
                {{-- Manager: Announcement editor --}}
                @if($isManager)
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4" x-data="{ show: false }">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-bold text-amber-800">📢 Announcement</p>
                        <button @click="show = !show" class="text-xs text-amber-600 font-semibold hover:underline">Edit</button>
                    </div>
                    <div x-show="show" x-cloak class="mt-3">
                        <form method="POST" action="/pages/{{ $page->slug }}/announcement">
                            @csrf
                            <textarea name="announcement" rows="2" placeholder="Post an announcement to your followers..."
                                class="w-full border border-amber-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-amber-500 resize-none mb-2">{{ $page->announcement }}</textarea>
                            <div class="flex gap-2">
                                <button type="submit" class="px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg transition">Save</button>
                                <button type="submit" name="announcement" value=""
                                    class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">Clear</button>
                            </div>
                        </form>
                    </div>
                    @if(!$page->announcement)
                    <p class="text-xs text-amber-600 mt-1">No announcement set</p>
                    @endif
                </div>

                {{-- Manager: Highlights editor --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm" x-data="{ showHl: false, highlights: {{ json_encode($page->highlights ?? []) }} }">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-bold text-gray-900">🔗 Highlights</p>
                        <button @click="showHl = !showHl" class="text-xs text-blue-600 font-semibold hover:underline">Edit</button>
                    </div>
                    <div x-show="showHl" x-cloak>
                        <form method="POST" action="/pages/{{ $page->slug }}/highlights" class="space-y-2">
                            @csrf
                            <template x-for="(hl, i) in highlights" :key="i">
                                <div class="flex gap-2">
                                    <input type="text" :name="'highlights[' + i + '][icon]'" x-model="hl.icon" placeholder="🔗" maxlength="5"
                                        class="w-12 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:border-blue-500">
                                    <input type="text" :name="'highlights[' + i + '][title]'" x-model="hl.title" placeholder="Label" required
                                        class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500">
                                    <input type="url" :name="'highlights[' + i + '][url]'" x-model="hl.url" placeholder="https://..." required
                                        class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500">
                                    <button type="button" @click="highlights.splice(i, 1)" class="text-gray-400 hover:text-red-500 px-1">×</button>
                                </div>
                            </template>
                            <div class="flex gap-2">
                                <button type="button" x-show="highlights.length < 6"
                                    @click="highlights.push({ icon: '', title: '', url: '' })"
                                    class="text-xs text-blue-600 font-semibold hover:underline">+ Add highlight</button>
                                <button type="submit" class="ml-auto px-4 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

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
                        @if($page->username)
                        <div class="flex items-center gap-3 text-gray-600">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-gray-500">&#64;{{ $page->username }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Social Links --}}
                @if($page->social_links && count(array_filter($page->social_links)))
                @php
                    $socialIcons = [
                        'youtube'   => ['label'=>'YouTube',   'color'=>'text-red-600',   'bg'=>'bg-red-50',   'icon'=>'<path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.27 8.27 0 004.84 1.56V6.81a4.85 4.85 0 01-1.07-.12z"/>'],
                        'tiktok'    => ['label'=>'TikTok',    'color'=>'text-gray-900',  'bg'=>'bg-gray-100', 'icon'=>'<path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.27 8.27 0 004.84 1.56V6.81a4.85 4.85 0 01-1.07-.12z"/>'],
                        'instagram' => ['label'=>'Instagram', 'color'=>'text-pink-600',  'bg'=>'bg-pink-50',  'icon'=>'<rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>'],
                        'twitter'   => ['label'=>'Twitter/X', 'color'=>'text-sky-500',  'bg'=>'bg-sky-50',   'icon'=>'<path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>'],
                        'facebook'  => ['label'=>'Facebook',  'color'=>'text-blue-600', 'bg'=>'bg-blue-50',  'icon'=>'<path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>'],
                    ];
                @endphp
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-3">Find us on</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($socialIcons as $key => $info)
                            @if(!empty($page->social_links[$key]))
                            <a href="{{ $page->social_links[$key] }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium transition hover:opacity-80 {{ $info['color'] }} {{ $info['bg'] }}">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">{!! $info['icon'] !!}</svg>
                                {{ $info['label'] }}
                            </a>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

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

                {{-- Milestones --}}
                @php try { $milestones = $page->milestones; } catch (\Exception $e) { $milestones = collect(); } @endphp
                @if($milestones->count())
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-3">🏆 Milestones</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($milestones as $ms)
                        @php
                            $val = $ms->milestone_value;
                            $label = $val >= 1000000 ? number_format($val/1000000, 1).'M' : ($val >= 1000 ? number_format($val/1000, 0).'K' : $val);
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            🎉 {{ $label }} {{ $ms->milestone_type }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- FAQ --}}
                @php try { $faqs = $page->faqs; } catch (\Exception $e) { $faqs = collect(); } @endphp
                @if($faqs->count())
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm" x-data="{ openFaq: null }">
                    <h3 class="font-bold text-gray-900 mb-3">Frequently asked questions</h3>
                    <div class="space-y-1">
                        @foreach($faqs as $i => $faq)
                        <div class="border border-gray-100 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === {{ $i }} ? null : {{ $i }}"
                                class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 transition">
                                <span class="text-sm font-semibold text-gray-800">{{ $faq->question }}</span>
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="openFaq === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="openFaq === {{ $i }}" x-cloak class="px-4 pb-3 text-sm text-gray-600 leading-relaxed">{{ $faq->answer }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
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

        {{-- ========== TAB: Photos ========== --}}
        <div x-show="activeTab === 'photos'" class="mt-3">
            @if($photos->count())
                <div class="grid grid-cols-3 gap-1">
                    @foreach($photos as $photo)
                    <a href="{{ $photo->image_url }}" target="_blank" class="aspect-square overflow-hidden rounded-lg bg-gray-100 block">
                        <img src="{{ $photo->image_url }}" class="w-full h-full object-cover hover:opacity-90 transition" alt="">
                    </a>
                    @endforeach
                </div>
                <div class="mt-4">{{ $photos->links() }}</div>
            @else
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-gray-500 font-medium">No photos yet</p>
                </div>
            @endif
        </div>

        {{-- ========== TAB: Q&A ========== --}}
        <div x-show="activeTab === 'qna'" class="mt-3">
            {{-- Submit question --}}
            @auth
            @if(!$isManager)
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 mb-5"
                x-data="{ question: '', sending: false, sent: false }">
                <div class="flex gap-2">
                    <input type="text" x-model="question" placeholder="Ask something..." maxlength="500"
                        @keydown.enter="if(question.trim()){ sending=true; fetch('/pages/{{ $page->slug }}/qna',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({question:question})}).then(r=>r.json()).then(d=>{ if(d.success||d.id){ sent=true; question=''; setTimeout(()=>{ sent=false; location.reload(); },1200); } }).catch(()=>{ alert('Failed to submit'); }).finally(()=>{ sending=false; }) }"
                        class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                    <button type="button"
                        :disabled="sending || !question.trim()"
                        @click="if(question.trim()){ sending=true; fetch('/pages/{{ $page->slug }}/qna',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({question:question})}).then(r=>r.json()).then(d=>{ if(d.success||d.id){ sent=true; question=''; setTimeout(()=>{ sent=false; location.reload(); },1200); } }).catch(()=>{ alert('Failed to submit'); }).finally(()=>{ sending=false; }) }"
                        class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold rounded-xl text-sm transition flex-shrink-0">
                        <span x-show="!sending && !sent">Ask</span>
                        <span x-show="sending">...</span>
                        <span x-show="sent">✓</span>
                    </button>
                </div>
            </div>
            @endif
            @endauth

            {{-- Manager: show all including unanswered + add answer form --}}
            @if($qnaItems->count())
            <div class="space-y-4">
                @foreach($qnaItems as $item)
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start gap-3 mb-2">
                        <span class="text-xl mt-0.5">❓</span>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 text-sm">{{ $item->question }}</p>
                            @if($item->asker)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $item->asker->name }} · {{ $item->created_at->diffForHumans() }}</p>
                            @else
                            <p class="text-xs text-gray-400 mt-0.5">Anonymous · {{ $item->created_at->diffForHumans() }}</p>
                            @endif
                        </div>
                        @if($isManager)
                        <form method="POST" action="/pages/{{ $page->slug }}/qna/{{ $item->id }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="text-gray-300 hover:text-red-500 text-xs transition">✕</button>
                        </form>
                        @endif
                    </div>
                    @if($item->answer)
                    <div class="bg-blue-50 rounded-xl px-4 py-3 mt-2">
                        <p class="text-xs font-bold text-blue-700 mb-1">{{ $page->name }} answered:</p>
                        <p class="text-sm text-gray-800">{{ $item->answer }}</p>
                    </div>
                    @endif
                    @if($isManager)
                    <div class="mt-3"
                        x-data="{ show: false, answer: '{{ addslashes($item->answer ?? '') }}', featured: {{ $item->is_featured ? 'true' : 'false' }}, saving: false, saved: false }">
                        <button type="button" @click="show = !show" class="text-xs text-blue-600 font-semibold hover:underline">
                            {{ $item->answer ? '✏️ Edit answer' : '💬 Answer' }}
                        </button>
                        <div x-show="show" x-cloak class="mt-2 space-y-2">
                            <textarea x-model="answer" rows="2" placeholder="Your answer..."
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500 resize-none"></textarea>
                            <div class="flex gap-2 items-center">
                                <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
                                    <input type="checkbox" x-model="featured" class="rounded">
                                    Feature this Q&amp;A
                                </label>
                                <button type="button" :disabled="saving"
                                    @click="saving=true; fetch('/pages/{{ $page->slug }}/qna/{{ $item->id }}/answer',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({answer:answer,is_featured:featured})}).then(r=>r.json()).then(d=>{ if(d.success||d.id){ saved=true; setTimeout(()=>{ saved=false; location.reload(); },1000); } }).catch(()=>{ alert('Failed to save'); }).finally(()=>{ saving=false; })"
                                    class="ml-auto px-4 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 transition">
                                    <span x-show="!saving && !saved">Save</span>
                                    <span x-show="saving">...</span>
                                    <span x-show="saved">✓</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $qnaItems->links() }}</div>
            @else
            <div class="text-center py-16">
                <p class="text-gray-500 font-medium">No questions yet</p>
                @auth
                @if(!$isManager)
                <p class="text-sm text-gray-400 mt-1">Be the first to ask!</p>
                @endif
                @endauth
            </div>
            @endif
        </div>

        {{-- ========== TAB: Shop / Products ========== --}}
        <div x-show="activeTab === 'products'">
            {{-- Manager: add product form --}}
            @if($isManager)
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 mb-5" x-data="{ showForm: false }">
                <button @click="showForm = !showForm" class="text-sm font-semibold text-blue-600 hover:underline">+ Add product / service</button>
                <div x-show="showForm" x-cloak class="mt-3">
                    <form method="POST" action="/pages/{{ $page->slug }}/products" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="text" name="name" placeholder="Product name" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                        <textarea name="description" rows="2" placeholder="Description (optional)"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 resize-none"></textarea>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="price" placeholder="Price" step="0.01" min="0"
                                class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                            <select name="currency" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                                <option value="NPR">NPR</option>
                                <option value="USD">USD</option>
                                <option value="INR">INR</option>
                            </select>
                        </div>
                        <input type="url" name="link_url" placeholder="Link URL (optional)"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Product image</label>
                            <input type="file" name="image" accept="image/*" class="text-sm text-gray-600">
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl transition text-sm">Add product</button>
                    </form>
                </div>
            </div>
            @endif

            @if($products->count())
            <div class="grid grid-cols-2 gap-3">
                @foreach($products as $product)
                <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm flex flex-col">
                    @if($product->image_url)
                    <img src="{{ $product->image_url }}" class="w-full h-36 object-cover bg-gray-100" alt="{{ $product->name }}">
                    @else
                    <div class="w-full h-36 bg-gray-100 flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    @endif
                    <div class="p-3 flex flex-col flex-1">
                        <p class="font-bold text-gray-900 text-sm leading-tight">{{ $product->name }}</p>
                        @if($product->description)
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $product->description }}</p>
                        @endif
                        @if($product->price !== null)
                        <p class="text-sm font-bold text-blue-600 mt-2">{{ $product->currency }} {{ number_format($product->price, 2) }}</p>
                        @endif
                        <div class="mt-auto pt-2 flex gap-1.5">
                            @if($product->link_url)
                            <a href="{{ $product->link_url }}" target="_blank"
                               class="flex-1 text-center text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg py-1.5 transition">
                                View →
                            </a>
                            @endif
                            @if($isManager)
                            <form method="POST" action="/pages/{{ $page->slug }}/products/{{ $product->id }}" onsubmit="return confirm('Remove?')">
                                @csrf @method('DELETE')
                                <button class="px-2 py-1.5 text-xs text-red-500 hover:bg-red-50 rounded-lg transition">✕</button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <p class="text-gray-500 font-medium">No products yet</p>
            </div>
            @endif
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

function copyPageUrl() {
    navigator.clipboard.writeText('{{ url("/pages/".$page->slug) }}').then(() => {
        showToast('Link copied!');
    });
}

function showReportPage() {
    document.getElementById('report-page-modal').classList.remove('hidden');
}

function hideReportPage() {
    document.getElementById('report-page-modal').classList.add('hidden');
}

function copyPostUrl(slug, postId) {
    const url = window.location.origin + '/pages/' + slug + '#post-' + postId;
    navigator.clipboard.writeText(url).then(() => showToast('Link copied!'));
}

function reactPost(btn, slug, postId, reaction) {
    fetch(`/pages/${slug}/posts/${postId}/react`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ reaction })
    }).then(r => r.json()).then(data => {
        const card = btn.closest('[id^="post-"]');
        if (!card) return;
        const countEl = card.querySelector('.reaction-count');
        if (countEl) countEl.textContent = data.likes_count;
        const reactionBar = card.querySelector('.reaction-bar');
        if (reactionBar && data.top_reactions) {
            const emojis = { like:'👍', love:'❤️', haha:'😂', wow:'😮', sad:'😢', angry:'😡' };
            const parts = Object.entries(data.top_reactions).slice(0,3).map(([r,c]) => `<span>${emojis[r]||'👍'}</span>`).join('');
            reactionBar.innerHTML = parts + `<span class="ml-1 text-gray-500 text-xs">${data.likes_count}</span>`;
        }
        if (data.reacted) {
            btn.classList.add('text-blue-600');
        } else {
            btn.classList.remove('text-blue-600');
        }
    });
}

function votePoll(slug, postId, optionId, btn) {
    fetch(`/pages/${slug}/posts/${postId}/vote`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ option_id: optionId })
    }).then(r => r.json()).then(data => {
        const container = btn.closest('[x-data]');
        const allBtns = container.querySelectorAll('[data-opt-id]');
        allBtns.forEach(b => {
            const oid = parseInt(b.dataset.optId);
            const opt = data.options.find(o => o.id === oid);
            const bar = b.querySelector('div');
            const pctEl = b.querySelector('span span:last-child');
            if (opt) {
                if (bar) bar.style.width = opt.votes + ' / ' + data.total_votes * 100 + '%';
                if (pctEl && data.voted_option_id) {
                    const pct = data.total_votes > 0 ? Math.round(opt.votes / data.total_votes * 100) : 0;
                    pctEl.textContent = pct + '%';
                    if (bar) bar.style.width = pct + '%';
                    bar.style.opacity = '1';
                }
            }
            b.classList.toggle('border-blue-500', oid === data.voted_option_id);
            b.classList.toggle('text-blue-700', oid === data.voted_option_id);
            b.classList.toggle('border-gray-200', oid !== data.voted_option_id);
        });
        const totalEl = container.querySelector('p');
        if (totalEl) totalEl.textContent = data.total_votes + ' ' + (data.total_votes === 1 ? 'vote' : 'votes');
    });
}

function loadComments(slug, postId, alpineData) {
    if (alpineData.commentLoaded) return;
    fetch(`/pages/${slug}/posts/${postId}/comments`, {
        headers: { 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        alpineData.comments = Array.isArray(data) ? data : (data.comments || []);
        alpineData.commentLoaded = true;
    });
}

function postComment(slug, postId, alpineData) {
    const body = alpineData.commentBody.trim();
    if (!body) return;
    fetch(`/pages/${slug}/posts/${postId}/comments`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ body })
    }).then(r => r.json()).then(data => {
        if (data.comment) {
            alpineData.comments.push(data.comment);
            alpineData.commentBody = '';
            const card = document.getElementById('post-' + postId);
            if (card) {
                const ccEl = card.querySelector('.comment-count');
                if (ccEl) ccEl.textContent = parseInt(ccEl.textContent || '0') + 1;
            }
        }
    });
}

function showToast(msg) {
    const t = document.createElement('div');
    t.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-sm px-4 py-2 rounded-full shadow-lg z-50 transition-opacity';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 2000);
}
</script>
@endsection
