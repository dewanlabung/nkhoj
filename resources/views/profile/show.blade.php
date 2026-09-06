@extends('layouts.app')
@section('title', $user->name . ' — nkhoj')

@section('content')
@php
$isOwn = auth()->check() && auth()->id() === $user->id;
$social = $user->social_links ?? [];
$socialDefs = [
    'twitter'   => ['icon' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z', 'label' => 'X (Twitter)', 'prefix' => 'https://x.com/'],
    'instagram' => ['icon' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z', 'label' => 'Instagram', 'prefix' => 'https://instagram.com/'],
    'facebook'  => ['icon' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z', 'label' => 'Facebook', 'prefix' => 'https://facebook.com/'],
    'youtube'   => ['icon' => 'M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z', 'label' => 'YouTube', 'prefix' => 'https://youtube.com/'],
    'tiktok'    => ['icon' => 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z', 'label' => 'TikTok', 'prefix' => 'https://tiktok.com/@'],
    'website'   => ['icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064', 'label' => 'Website', 'prefix' => ''],
];
@endphp

<div x-data="{ activeTab: 'posts', searchQ: '' }" class="-mt-8">

    {{-- ═══ COVER BANNER ═══════════════════════════════════════ --}}
    <div class="relative mb-0">
        <div class="h-52 w-full overflow-hidden bg-gradient-to-br from-brand-600 via-indigo-700 to-purple-800 rounded-b-none">
            @if($user->cover_url)
            <img src="{{ $user->cover_url }}" class="w-full h-full object-cover">
            @endif
            {{-- Edit cover (own profile) --}}
            @if($isOwn)
            <a href="/account/settings" class="absolute top-3 right-3 flex items-center gap-1.5 bg-black/40 hover:bg-black/60 text-white text-xs font-medium px-3 py-1.5 rounded-lg backdrop-blur-sm transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Edit Cover
            </a>
            @endif
        </div>

        {{-- Avatar --}}
        <div class="absolute -bottom-12 left-6">
            <div class="relative">
                <div class="w-24 h-24 rounded-full border-4 border-white dark:border-gray-900 shadow-xl overflow-hidden bg-brand-500 flex items-center justify-center">
                    @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" class="w-full h-full object-cover">
                    @else
                    <span class="text-white text-3xl font-black">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                @if($user->last_seen_at && now()->diffInMinutes($user->last_seen_at) < 15)
                <span class="absolute bottom-1 right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></span>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ NAME ROW ════════════════════════════════════════════ --}}
    <div class="flex items-end justify-between pl-36 pr-6 pt-2 pb-4 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                @if($user->role === 'admin')
                <span class="text-xs font-bold bg-brand-500 text-white px-2 py-0.5 rounded-full">Admin</span>
                @elseif($user->role === 'editor')
                <span class="text-xs font-bold bg-teal-500 text-white px-2 py-0.5 rounded-full">Editor</span>
                @endif
                @if($user->last_seen_at)
                <span class="text-xs text-gray-400">Last seen: {{ $user->lastSeenLabel() }}</span>
                @endif
            </div>
            <p class="text-sm text-gray-400 mt-0.5">&#64;{{ $user->username }}</p>
        </div>

        {{-- Follow / Edit --}}
        <div class="flex items-center gap-2">
            @if($isOwn)
            <a href="/account/settings" class="px-4 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-full hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Edit Profile
            </a>
            @else
            @auth
            <div x-data="{ following: {{ $isFollowing ? 'true' : 'false' }}, loading: false }">
                <button
                    @click="loading = true; fetch('/follow/{{ $user->id }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } }).then(r=>r.json()).then(d=>{following=d.action==='followed';loading=false;}).catch(()=>loading=false)"
                    :disabled="loading"
                    :class="following ? 'bg-white dark:bg-gray-800 text-brand-600 border-brand-300' : 'bg-brand-500 text-white border-brand-500'"
                    class="px-5 py-1.5 rounded-full text-sm font-semibold border transition-all disabled:opacity-50">
                    <span x-text="following ? '✓ फलो गरिएको' : '+ फलो गर्नुहोस्'"></span>
                </button>
            </div>
            @else
            <a href="/login" class="px-5 py-1.5 bg-brand-500 text-white text-sm font-semibold rounded-full border border-brand-500">+ फलो गर्नुहोस्</a>
            @endauth
            @endif
        </div>
    </div>

    {{-- ═══ MAIN CONTENT ════════════════════════════════════════ --}}
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── LEFT SIDEBAR ────────────────────────────────────── --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Stats bar --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
                <div class="grid grid-cols-4 gap-2 text-center">
                    <div>
                        <p class="text-lg font-black text-gray-900 dark:text-white">{{ $user->posts()->published()->count() }}</p>
                        <p class="text-[11px] text-gray-400 font-nepali">लेखहरू</p>
                    </div>
                    <div x-data="{ c: {{ $followerCount }} }">
                        <p class="text-lg font-black text-gray-900 dark:text-white" x-text="c">{{ $followerCount }}</p>
                        <p class="text-[11px] text-gray-400 font-nepali">फलोअर</p>
                    </div>
                    <div>
                        <p class="text-lg font-black text-gray-900 dark:text-white">{{ $followingCount }}</p>
                        <p class="text-[11px] text-gray-400 font-nepali">फलोइङ</p>
                    </div>
                    <div>
                        <p class="text-lg font-black text-gray-900 dark:text-white">{{ number_format($totalViews) }}</p>
                        <p class="text-[11px] text-gray-400 font-nepali">दृश्य</p>
                    </div>
                </div>
            </div>

            {{-- Bio & Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 space-y-3">
                @if($user->bio)
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $user->bio }}</p>
                @else
                <p class="text-sm text-gray-400 italic">No bio yet.</p>
                @endif

                <div class="pt-2 border-t border-gray-50 dark:border-gray-700 space-y-2 text-xs text-gray-500">
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Member since {{ $user->created_at->format('M d, Y') }}
                    </div>
                    @if($isOwn || $user->role === 'admin')
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $user->email }}
                    </div>
                    @endif
                    @if($user->website)
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                        <a href="{{ $user->website }}" target="_blank" class="text-brand-600 hover:underline truncate">{{ $user->website }}</a>
                    </div>
                    @endif
                </div>

                {{-- Social links --}}
                @php $hasSocial = collect($social)->filter()->isNotEmpty(); @endphp
                @if($hasSocial)
                <div class="flex items-center gap-2.5 pt-2 flex-wrap">
                    @foreach($socialDefs as $platform => $def)
                    @if(!empty($social[$platform]))
                    <a href="{{ $def['prefix'] }}{!! ltrim($social[$platform], '@') !!}" target="_blank"
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-brand-50 dark:hover:bg-brand-900/30 hover:text-brand-600 dark:hover:text-brand-400 transition-colors"
                        title="{{ $def['label'] }}">
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor">
                            <path d="{{ $def['icon'] }}"/>
                        </svg>
                    </a>
                    @endif
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Category breakdown (Naver style) --}}
            @if($categoryBreakdown->count())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Category</h3>
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                </div>
                <ul class="space-y-1.5">
                    <li class="flex items-center justify-between text-sm">
                        <a href="/profile/{{ $user->username }}" class="text-brand-600 hover:underline font-medium">View all</a>
                        <span class="text-gray-400 text-xs">({{ $user->posts()->published()->count() }})</span>
                    </li>
                    @foreach($categoryBreakdown as $cat)
                    <li class="flex items-center justify-between text-sm border-t border-gray-50 dark:border-gray-700 pt-1.5">
                        <a href="/category/{{ $cat['slug'] }}" class="text-gray-600 dark:text-gray-300 hover:text-brand-600 font-nepali truncate">{{ $cat['name'] }}</a>
                        <span class="text-gray-400 text-xs ml-2">({{ $cat['count'] }})</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Activity Information (Naver style) --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Activity Information</h3>
                <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-300">
                    <li class="flex items-center gap-2">
                        <span class="text-gray-400">{{ $followerCount }}</span>
                        <span>blog neighbors</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-gray-400">{{ $user->posts()->published()->count() }}</span>
                        <span>posts</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-gray-400">{{ number_format($totalViews) }}</span>
                        <span>total views</span>
                    </li>
                </ul>
                {{-- RSS links --}}
                <div class="mt-3 pt-3 border-t border-gray-50 dark:border-gray-700 flex items-center gap-3 text-xs text-gray-400">
                    <a href="/feed.xml" target="_blank" class="flex items-center gap-1 hover:text-brand-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M6.18 15.64a2.18 2.18 0 012.18 2.18C8.36 19.01 7.38 20 6.18 20C4.98 20 4 19.01 4 17.82a2.18 2.18 0 012.18-2.18M4 4.44A15.56 15.56 0 0119.56 20h-2.83A12.73 12.73 0 004 7.27V4.44m0 5.66a9.9 9.9 0 019.9 9.9h-2.83A7.07 7.07 0 004 12.93V10.1z"/></svg>
                        RSS 2.0
                    </a>
                    <span>|</span>
                    <a href="/sitemap.xml" target="_blank" class="hover:text-brand-600 transition-colors">Sitemap</a>
                </div>
            </div>

            {{-- Followers --}}
            @if($followerUsers->count())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Followers ({{ $followerCount }})</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($followerUsers as $fu)
                    <a href="/profile/{{ $fu->username }}" title="{{ $fu->name }}">
                        <div class="w-10 h-10 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold border-2 border-white dark:border-gray-800 shadow-sm overflow-hidden hover:scale-110 transition-transform">
                            @if($fu->avatar_url)
                            <img src="{{ $fu->avatar_url }}" class="w-full h-full object-cover">
                            @else
                            {{ strtoupper(substr($fu->name, 0, 1)) }}
                            @endif
                        </div>
                    </a>
                    @endforeach
                    @if($followerCount > 6)
                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-xs text-gray-500 font-semibold border-2 border-white dark:border-gray-800">+{{ $followerCount - 6 }}</div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Following --}}
            @if($followingUsers->count())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Following ({{ $followingCount }})</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($followingUsers as $fu)
                    <a href="/profile/{{ $fu->username }}" title="{{ $fu->name }}">
                        <div class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white text-xs font-bold border-2 border-white dark:border-gray-800 shadow-sm overflow-hidden hover:scale-110 transition-transform">
                            @if($fu->avatar_url)
                            <img src="{{ $fu->avatar_url }}" class="w-full h-full object-cover">
                            @else
                            {{ strtoupper(substr($fu->name, 0, 1)) }}
                            @endif
                        </div>
                    </a>
                    @endforeach
                    @if($followingCount > 6)
                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-xs text-gray-500 font-semibold border-2 border-white dark:border-gray-800">+{{ $followingCount - 6 }}</div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Write link (own profile) --}}
            @if($isOwn)
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <a href="/dashboard/posts/create" class="flex items-center gap-1.5 hover:text-brand-600 transition-colors font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Writing
                </a>
                <span>·</span>
                <a href="/dashboard" class="hover:text-brand-600 transition-colors">Management</a>
                <span>·</span>
                <a href="/admin" class="hover:text-brand-600 transition-colors">Statistics</a>
            </div>
            @endif
        </div>

        {{-- ── MAIN AREA ────────────────────────────────────────── --}}
        <div class="lg:col-span-2">

            {{-- Search within profile --}}
            <div class="flex items-center gap-3 mb-4">
                {{-- Tab navigation --}}
                <div class="flex border-b border-gray-200 dark:border-gray-700 flex-1">
                    <button @click="activeTab = 'posts'" :class="activeTab === 'posts' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500 dark:text-gray-400'"
                        class="px-5 py-3 text-sm font-semibold border-b-2 -mb-px transition-colors font-nepali">
                        लेखहरू
                    </button>
                    <button @click="activeTab = 'about'" :class="activeTab === 'about' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500 dark:text-gray-400'"
                        class="px-5 py-3 text-sm font-semibold border-b-2 -mb-px transition-colors font-nepali">
                        जानकारी
                    </button>
                </div>
                {{-- Search --}}
                <div class="relative flex-shrink-0">
                    <input type="text" x-model="searchQ" placeholder="खोज्नुहोस्..."
                        class="w-40 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg pl-8 pr-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <svg class="absolute left-2.5 top-2 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            {{-- Posts tab --}}
            <div x-show="activeTab === 'posts'">
                @if($posts->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @foreach($posts as $post)
                    <a href="/posts/{{ $post->slug }}" class="group block bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden hover:shadow-md transition-shadow"
                        x-show="!searchQ || '{{ strtolower($post->title) }}'.includes(searchQ.toLowerCase())">
                        <div class="relative overflow-hidden bg-gradient-to-br from-brand-100 to-indigo-100 dark:from-gray-700 dark:to-gray-800" style="aspect-ratio:16/9;">
                            @if($post->thumbnail_url)
                            <img src="{{ $post->thumbnail_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-10 h-10 text-brand-200 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            @endif
                            <div class="absolute top-2 left-2">
                                <span class="text-[11px] px-2 py-0.5 rounded-full font-semibold bg-brand-500 text-white">
                                    {{ $post->category?->name_ne ?? $post->category?->name_en ?? '' }}
                                </span>
                            </div>
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-brand-600 transition-colors text-sm line-clamp-2 font-nepali leading-snug">
                                {{ $post->title }}
                            </h3>
                            <div class="mt-1.5 flex items-center gap-3 text-xs text-gray-400">
                                <span>{{ $post->published_at->format('d M Y') }}</span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    {{ number_format($post->view_count) }}
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                <div class="mt-6">{{ $posts->links() }}</div>
                @else
                <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                    <p class="text-2xl mb-3">✍️</p>
                    <p class="font-semibold text-gray-700 dark:text-gray-300 mb-1">There are no posts written yet.</p>
                    <p class="text-sm text-gray-400 mb-5">Fill your own space with various stories, such as fleeting thoughts, moods, and diary entries!</p>
                    @if($isOwn)
                    <a href="/dashboard/posts/create"
                        class="inline-flex items-center gap-2 px-5 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-full hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        글쓰기 (Write a Post)
                    </a>
                    @endif
                </div>
                @endif
            </div>

            {{-- About tab --}}
            <div x-show="activeTab === 'about'" x-cloak>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-brand-500 flex items-center justify-center text-white text-xl font-black overflow-hidden flex-shrink-0">
                            @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" class="w-full h-full object-cover">
                            @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white text-lg">{{ $user->name }}</p>
                            <p class="text-sm text-gray-400">&#64;{{ $user->username }} · {{ ucfirst($user->role) }}</p>
                        </div>
                    </div>

                    @if($user->bio)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $user->bio }}</p>
                    </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Member Since</p>
                                <p class="font-medium text-gray-700 dark:text-gray-300">{{ $user->created_at->format('F d, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Published Articles</p>
                                <p class="font-medium text-gray-700 dark:text-gray-300">{{ $user->posts()->published()->count() }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Total Views</p>
                                <p class="font-medium text-gray-700 dark:text-gray-300">{{ number_format($totalViews) }}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Followers</p>
                                <p class="font-medium text-gray-700 dark:text-gray-300">{{ $followerCount }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Following</p>
                                <p class="font-medium text-gray-700 dark:text-gray-300">{{ $followingCount }}</p>
                            </div>
                            @if($user->website)
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Website</p>
                                <a href="{{ $user->website }}" target="_blank" class="text-brand-600 hover:underline text-sm truncate block">{{ $user->website }}</a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
