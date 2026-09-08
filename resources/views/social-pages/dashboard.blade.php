@extends('layouts.app')

@section('title', 'Professional Dashboard — ' . $page->name)

@section('content')
<div class="min-h-screen bg-gray-50" x-data="{ activeTab: 'home' }">

    {{-- Mobile header --}}
    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="/pages/{{ $page->slug }}" class="p-2 -ml-2 text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="font-bold text-gray-900">Professional dashboard</h1>
        </div>
        <a href="/pages/{{ $page->slug }}/settings" class="text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
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

            {{-- Page mini header --}}
            <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm">
                <img src="{{ $page->avatar }}" alt="{{ $page->name }}" class="w-14 h-14 rounded-full object-cover">
                <div>
                    <div class="flex items-center gap-1">
                        <p class="font-bold text-gray-900">{{ $page->name }}</p>
                        @if($page->is_verified)
                            <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </div>
                    <p class="text-gray-500 text-sm">{{ number_format($page->followers_count) }} followers</p>
                </div>
            </div>

            {{-- Stat cards --}}
            @php
                $postCount = \App\Models\Post::where('author_id', $page->user_id)->where('status', 'published')->count();
                $totalViews = \App\Models\Post::where('author_id', $page->user_id)->sum('view_count');
            @endphp
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white rounded-2xl p-4 shadow-sm text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($page->followers_count) }}</p>
                    <p class="text-gray-500 text-xs mt-1">Followers</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $postCount }}</p>
                    <p class="text-gray-500 text-xs mt-1">Posts</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalViews) }}</p>
                    <p class="text-gray-500 text-xs mt-1">Views</p>
                </div>
            </div>

            {{-- Recent comments placeholder --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <p class="font-bold text-gray-900 text-lg">You don't have any recent comments</p>
                <p class="text-gray-400 text-sm mt-1">Engage with your audience by posting content</p>
            </div>

            {{-- Quick actions --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-3">Quick actions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="/dashboard/posts/create" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="text-sm font-semibold text-gray-700">Create post</span>
                    </a>
                    <a href="/pages/{{ $page->slug }}/settings" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        <span class="text-sm font-semibold text-gray-700">Edit page</span>
                    </a>
                </div>
            </div>

            {{-- Get more out of your Page --}}
            @if(!$page->bio || !$page->avatar_url)
                <div class="bg-white rounded-2xl p-4 shadow-sm flex items-start gap-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-gray-900 text-sm">Get more out of your Page</p>
                        <p class="text-gray-500 text-xs mt-0.5">Adding more details gives people a better sense of what you're about.</p>
                        <a href="/pages/{{ $page->slug }}/settings" class="text-blue-600 text-sm font-semibold mt-2 inline-block hover:underline">Update your Page</a>
                    </div>
                </div>
            @endif
        </div>

        {{-- TAB: Insights --}}
        <div x-show="activeTab === 'insights'" class="space-y-4">
            <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                <p class="text-gray-500">Insights coming soon. Publish more posts to see your performance.</p>
            </div>
        </div>

        {{-- TAB: Content --}}
        <div x-show="activeTab === 'content'">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900">Your posts</h3>
                <a href="/dashboard/posts/create" class="text-blue-600 text-sm font-semibold hover:underline">+ Create</a>
            </div>
            @php $recentPosts = \App\Models\Post::where('author_id', $page->user_id)->latest()->limit(10)->get(); @endphp
            @if($recentPosts->count())
                <div class="space-y-3">
                    @foreach($recentPosts as $post)
                        <div class="bg-white rounded-2xl p-4 shadow-sm flex gap-3 items-start">
                            @if($post->thumbnail_url)
                                <img src="{{ $post->thumbnail_url }}" class="w-16 h-16 object-cover rounded-xl flex-shrink-0">
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-sm line-clamp-2">{{ $post->title }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $post->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                    <span class="text-gray-400 text-xs">{{ $post->view_count ?? 0 }} views</span>
                                </div>
                            </div>
                            <a href="/dashboard/posts/{{ $post->id }}/edit" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-2xl p-8 shadow-sm text-center">
                    <p class="text-gray-500">No posts yet. <a href="/dashboard/posts/create" class="text-blue-600 font-semibold hover:underline">Create your first</a></p>
                </div>
            @endif
        </div>

        {{-- TAB: Engagement --}}
        <div x-show="activeTab === 'engagement'">
            <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                <p class="text-gray-500">No recent comments or interactions.</p>
            </div>
        </div>

        {{-- TAB: Tools --}}
        <div x-show="activeTab === 'tools'" class="space-y-4">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="font-bold text-gray-700 text-sm">Engagement</p>
                </div>
                <div class="divide-y divide-gray-100">
                    <a href="/dashboard/posts/create?format=event" class="flex items-center justify-between px-4 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">Events</p>
                                <p class="text-gray-500 text-xs">Organize an event online or nearby</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="font-bold text-gray-700 text-sm">Profile</p>
                </div>
                <div class="divide-y divide-gray-100">
                    <a href="/pages/{{ $page->slug }}/settings" class="flex items-center justify-between px-4 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">Page Settings</p>
                                <p class="text-gray-500 text-xs">Update your page details and visibility</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
