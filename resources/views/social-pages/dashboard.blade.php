@extends('layouts.app')

@section('title', 'Professional Dashboard — ' . $page->name)

@push('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" defer></script>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50" x-data="{ activeTab: 'home' }">

    {{-- Mobile header --}}
    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="/pages/{{ $page->slug }}" class="p-2 -ml-2 text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="font-bold text-gray-900">Professional dashboard</h1>
        </div>
        <a href="/pages/{{ $page->slug }}/settings" class="text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </a>
    </div>

    {{-- Tabs --}}
    <div class="bg-white border-b border-gray-200 overflow-x-auto">
        <div class="flex gap-1 px-3 py-1 max-w-2xl mx-auto">
            @foreach(['home' => 'Home', 'insights' => 'Insights', 'content' => 'Content', 'engagement' => 'Engagement', 'tools' => 'Tools'] as $tab => $label)
            <button @click="activeTab = '{{ $tab }}'"
                class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition"
                :class="activeTab === '{{ $tab }}' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-6">

        {{-- TAB: Home --}}
        <div x-show="activeTab === 'home'" class="space-y-4">
            <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm">
                <img src="{{ $page->avatar }}" alt="{{ $page->name }}" class="w-14 h-14 rounded-full object-cover">
                <div>
                    <div class="flex items-center gap-1">
                        <p class="font-bold text-gray-900">{{ $page->name }}</p>
                        @if($page->is_verified)
                        <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        @endif
                    </div>
                    <p class="text-gray-500 text-sm">{{ number_format($page->followers_count) }} followers</p>
                </div>
            </div>

            @php
                $postCount  = \App\Models\PagePost::where('social_page_id', $page->id)->count();
                $totalViews = $page->views_count;
            @endphp
            <div class="grid grid-cols-4 gap-3">
                <div class="bg-white rounded-2xl p-3 shadow-sm text-center">
                    <p class="text-xl font-bold text-gray-900">{{ number_format($page->followers_count) }}</p>
                    <p class="text-gray-500 text-xs mt-0.5">Followers</p>
                </div>
                <div class="bg-white rounded-2xl p-3 shadow-sm text-center">
                    <p class="text-xl font-bold text-gray-900">{{ $postCount }}</p>
                    <p class="text-gray-500 text-xs mt-0.5">Posts</p>
                </div>
                <div class="bg-white rounded-2xl p-3 shadow-sm text-center">
                    <p class="text-xl font-bold text-gray-900">{{ number_format($totalViews) }}</p>
                    <p class="text-gray-500 text-xs mt-0.5">Views</p>
                </div>
                <div class="bg-white rounded-2xl p-3 shadow-sm text-center">
                    <p class="text-xl font-bold text-gray-900">{{ $page->reviews_count > 0 ? number_format($page->rating_avg, 1) : '—' }}</p>
                    <p class="text-gray-500 text-xs mt-0.5">Rating</p>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-3">Quick actions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="/pages/{{ $page->slug }}" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span class="text-sm font-semibold text-gray-700">Create post</span>
                    </a>
                    <a href="/pages/{{ $page->slug }}/settings" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span class="text-sm font-semibold text-gray-700">Edit page</span>
                    </a>
                    <a href="/pages/{{ $page->slug }}/moderation" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 transition relative">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="text-sm font-semibold text-gray-700">Moderation</span>
                        @if($pendingReports > 0)
                        <span class="absolute top-1.5 right-1.5 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ $pendingReports }}</span>
                        @endif
                    </a>
                    @if($isOwner)
                    <a href="/pages/{{ $page->slug }}/admins" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="text-sm font-semibold text-gray-700">Team ({{ $pendingInvites }})</span>
                    </a>
                    @else
                    <a href="/pages/{{ $page->slug }}" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span class="text-sm font-semibold text-gray-700">View page</span>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Page status banner (disabled) --}}
            @if($page->status === 'disabled')
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="flex-1">
                    <p class="font-semibold text-amber-800 text-sm">This page is currently disabled</p>
                    @if($page->disabled_reason)<p class="text-amber-700 text-xs mt-0.5">{{ $page->disabled_reason }}</p>@endif
                </div>
                @if($isOwner)
                <form method="POST" action="/pages/{{ $page->slug }}/enable">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-amber-700 hover:text-amber-900 underline">Enable</button>
                </form>
                @endif
            </div>
            @endif

            @if(!$page->bio || !$page->avatar_url)
            <div class="bg-white rounded-2xl p-4 shadow-sm flex items-start gap-3">
                <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-gray-900 text-sm">Get more out of your Page</p>
                    <p class="text-gray-500 text-xs mt-0.5">Adding bio, photo, and location helps people discover you.</p>
                    <a href="/pages/{{ $page->slug }}/settings" class="text-blue-600 text-sm font-semibold mt-2 inline-block hover:underline">Update your Page</a>
                </div>
            </div>
            @endif
        </div>

        {{-- TAB: Insights --}}
        <div x-show="activeTab === 'insights'" class="space-y-4">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-1">Page views (last 30 days)</h3>
                <p class="text-3xl font-bold text-blue-600 mb-4">{{ number_format(array_sum($chartValues)) }}</p>
                <canvas id="views-chart" height="80"></canvas>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white rounded-2xl p-4 shadow-sm text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($page->followers_count) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Followers</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $page->reviews_count > 0 ? number_format($page->rating_avg, 1) : '—' }}</p>
                    <p class="text-xs text-gray-500 mt-1">Avg Rating</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($page->views_count) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Views</p>
                </div>
            </div>
        </div>

        {{-- TAB: Content --}}
        <div x-show="activeTab === 'content'">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900">Your posts</h3>
                <a href="/pages/{{ $page->slug }}" class="text-blue-600 text-sm font-semibold hover:underline">+ Create</a>
            </div>
            @php $recentPosts = \App\Models\PagePost::where('social_page_id', $page->id)->latest()->limit(10)->get(); @endphp
            @if($recentPosts->count())
            <div class="space-y-3">
                @foreach($recentPosts as $post)
                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold uppercase {{ match($post->type) { 'event' => 'bg-red-100 text-red-600', 'photo' => 'bg-green-100 text-green-600', 'video' => 'bg-purple-100 text-purple-600', default => 'bg-gray-100 text-gray-600' } }}">{{ $post->type }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800 line-clamp-2">{{ $post->event_title ?: ($post->body ?: '(media post)') }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $post->created_at->diffForHumans() }} · {{ $post->likes_count }} likes</p>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            {{-- Pin / Unpin --}}
                            @if($isOwner)
                            @if($page->pinned_post_id === $post->id)
                            <form method="POST" action="/pages/{{ $page->slug }}/posts/{{ $post->id }}/pin">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-yellow-500 hover:text-yellow-600 p-1" title="Unpin">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M16 2v2h-1v5.586l1.707 1.707A1 1 0 0117 12v1a1 1 0 01-1 1h-4v6l-1 2-1-2v-6H6a1 1 0 01-1-1v-1a1 1 0 01.293-.707L7 9.586V4H6V2h10z"/></svg>
                                </button>
                            </form>
                            @else
                            <form method="POST" action="/pages/{{ $page->slug }}/posts/{{ $post->id }}/pin">
                                @csrf
                                <button type="submit" class="text-gray-300 hover:text-yellow-500 p-1" title="Pin post">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                </button>
                            </form>
                            @endif
                            @endif
                            <form method="POST" action="/pages/{{ $page->slug }}/posts/{{ $post->id }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-300 hover:text-red-500 p-1" onclick="return confirm('Delete?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="bg-white rounded-2xl p-8 shadow-sm text-center">
                <p class="text-gray-500">No posts yet. <a href="/pages/{{ $page->slug }}" class="text-blue-600 font-semibold hover:underline">Create your first</a></p>
            </div>
            @endif
        </div>

        {{-- TAB: Engagement --}}
        <div x-show="activeTab === 'engagement'">
            @php $recentReviews = \App\Models\PageReview::where('social_page_id', $page->id)->with('user')->latest()->limit(10)->get(); @endphp
            @if($recentReviews->count())
            <div class="space-y-3">
                @foreach($recentReviews as $review)
                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">{{ strtoupper(substr($review->user->name,0,1)) }}</div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $review->user->name }}</p>
                            <p class="text-yellow-400 text-xs">{{ str_repeat('★',$review->rating) }}{{ str_repeat('☆',5-$review->rating) }}</p>
                        </div>
                        <span class="ml-auto text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    @if($review->body) <p class="text-sm text-gray-600 mt-1">{{ $review->body }}</p> @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                <p class="text-gray-500">No reviews or interactions yet.</p>
            </div>
            @endif
        </div>

        {{-- TAB: Tools --}}
        <div x-show="activeTab === 'tools'" class="space-y-4">

            {{-- Team management --}}
            @if($isOwner)
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100"><p class="font-bold text-gray-700 text-sm">Team & Admins</p></div>
                <div class="divide-y divide-gray-100">
                    <a href="/pages/{{ $page->slug }}/admins" class="flex items-center justify-between px-4 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">Manage Team</p>
                                <p class="text-gray-500 text-xs">Invite admins, moderators &amp; editors · {{ $pendingInvites }} active</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            @endif

            {{-- Moderation --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100"><p class="font-bold text-gray-700 text-sm">Moderation</p></div>
                <div class="divide-y divide-gray-100">
                    <a href="/pages/{{ $page->slug }}/moderation" class="flex items-center justify-between px-4 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center relative">
                                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                @if($pendingReports > 0)
                                <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $pendingReports }}</span>
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">Report Queue</p>
                                <p class="text-gray-500 text-xs">{{ $pendingReports }} pending report{{ $pendingReports !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            {{-- Profile, Verification, Map --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100"><p class="font-bold text-gray-700 text-sm">Profile & Discovery</p></div>
                <div class="divide-y divide-gray-100">
                    <a href="/pages/{{ $page->slug }}/settings" class="flex items-center justify-between px-4 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                            </div>
                            <div><p class="font-semibold text-gray-900 text-sm">Page Settings</p><p class="text-gray-500 text-xs">Update details, hours, location</p></div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="/pages/map" class="flex items-center justify-between px-4 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            </div>
                            <div><p class="font-semibold text-gray-900 text-sm">Map Directory</p><p class="text-gray-500 text-xs">Your pin on the discovery map</p></div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>

                    {{-- Verification --}}
                    @if($isOwner)
                    <div class="px-4 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 text-sm">Verified Badge</p>
                                @if($page->is_verified)
                                    <p class="text-green-600 text-xs font-semibold">✓ Page is verified</p>
                                @elseif($hasPendingVerification)
                                    <p class="text-amber-600 text-xs">Request under review</p>
                                @else
                                    <p class="text-gray-500 text-xs">Request a blue checkmark</p>
                                @endif
                            </div>
                        </div>
                        @if(!$page->is_verified && !$hasPendingVerification)
                        <div x-data="{ open: false }" class="mt-3">
                            <button @click="open = !open" class="text-blue-600 text-sm font-semibold hover:underline">Request verification →</button>
                            <div x-show="open" x-cloak class="mt-3">
                                <form method="POST" action="/pages/{{ $page->slug }}/request-verification">
                                    @csrf
                                    <textarea name="reason" rows="2" placeholder="Why should this page be verified? (optional)"
                                        class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 resize-none mb-2"></textarea>
                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl transition text-sm">
                                        Submit Request
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Danger zone (owner only) --}}
            @if($isOwner)
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-red-100">
                <div class="px-4 py-3 border-b border-red-100"><p class="font-bold text-red-600 text-sm">Danger zone</p></div>
                <div class="divide-y divide-gray-100 p-4 space-y-3">

                    {{-- Disable / Enable --}}
                    @if($page->status === 'active')
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 text-sm font-semibold text-amber-600 hover:text-amber-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            Disable this page
                        </button>
                        <div x-show="open" x-cloak class="mt-3">
                            <p class="text-xs text-gray-500 mb-2">Disabled pages are hidden from discovery. You can re-enable anytime.</p>
                            <form method="POST" action="/pages/{{ $page->slug }}/disable">
                                @csrf
                                <input type="text" name="reason" placeholder="Reason (optional)" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-500 mb-2">
                                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2.5 rounded-xl transition text-sm">
                                    Disable Page
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <form method="POST" action="/pages/{{ $page->slug }}/enable">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 text-sm font-semibold text-green-600 hover:text-green-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Re-enable this page
                        </button>
                    </form>
                    @endif

                    {{-- Delete --}}
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 text-sm font-semibold text-red-600 hover:text-red-700 mt-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Delete this page permanently
                        </button>
                        <div x-show="open" x-cloak class="mt-3">
                            <p class="text-xs text-red-600 font-semibold mb-2">⚠ This will delete all posts, reviews and followers. It cannot be undone.</p>
                            <form method="POST" action="/pages/{{ $page->slug }}" onsubmit="return confirm('Delete {{ addslashes($page->name) }}? This CANNOT be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition text-sm">
                                    Yes, delete permanently
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('views-chart');
    if (!canvas || typeof Chart === 'undefined') return;
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Views',
                data: @json($chartValues),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.08)',
                borderWidth: 2,
                pointRadius: 2,
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 8, font: { size: 10 } } },
                y: { beginAtZero: true, ticks: { font: { size: 10 }, precision: 0 } }
            }
        }
    });
});
</script>
@endsection
