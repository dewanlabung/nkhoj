@extends('layouts.app')

@section('title', $page->name)

@section('content')
<div class="bg-white min-h-screen" x-data="{ activeTab: 'posts' }">

    {{-- Cover photo --}}
    <div class="relative h-36 md:h-56 bg-gray-300 overflow-hidden">
        @if($page->cover_url)
            <img src="{{ $page->cover_url }}" alt="Cover" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full bg-gradient-to-br from-gray-300 to-gray-400"></div>
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
            {{-- Avatar --}}
            <div class="relative flex-shrink-0">
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-full border-4 border-white overflow-hidden bg-gray-200 shadow">
                    <img src="{{ $page->avatar }}" alt="{{ $page->name }}" class="w-full h-full object-cover">
                </div>
                @if($isOwner)
                    <label class="absolute bottom-1 right-1 bg-gray-200 hover:bg-gray-300 rounded-full p-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        </svg>
                    </label>
                @endif
            </div>
            {{-- Name & meta --}}
            <div class="flex-1 pb-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-bold text-gray-900">{{ $page->name }}</h1>
                    @if($page->is_verified)
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </div>
                <p class="text-gray-500 text-sm">
                    {{ number_format($page->followers_count) }} followers
                    @if($page->first_category)
                        &middot; {{ $page->first_category }}
                    @endif
                </p>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex gap-2 mb-5">
            @if($isOwner)
                <a href="/pages/{{ $page->slug }}/dashboard"
                   class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl transition text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Professional dashboard
                </a>
                <a href="/pages/{{ $page->slug }}/settings"
                   class="flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2.5 px-4 rounded-xl transition text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </a>
            @else
                @auth
                    <button
                        id="follow-btn"
                        onclick="toggleFollow()"
                        class="flex-1 flex items-center justify-center gap-2 font-semibold py-2.5 px-4 rounded-xl transition text-sm {{ $isFollowing ? 'bg-gray-100 hover:bg-gray-200 text-gray-800' : 'bg-blue-600 hover:bg-blue-700 text-white' }}"
                        data-following="{{ $isFollowing ? 'true' : 'false' }}"
                        data-slug="{{ $page->slug }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isFollowing ? 'M5 13l4 4L19 7' : 'M12 4v16m8-8H4' }}"/>
                        </svg>
                        <span id="follow-label">{{ $isFollowing ? 'Following' : 'Follow' }}</span>
                    </button>
                    <button id="save-page-btn" onclick="toggleSavePage()"
                            class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl transition text-sm font-semibold
                                   {{ $isSaved ?? false ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                            data-saved="{{ ($isSaved ?? false) ? 'true' : 'false' }}"
                            data-id="{{ $page->id }}">
                        <svg class="w-4 h-4" fill="{{ ($isSaved ?? false) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 20 20">
                            <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                        </svg>
                        <span id="save-page-label">{{ ($isSaved ?? false) ? 'Saved' : 'Save' }}</span>
                    </button>
                @else
                    <a href="/login" class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl transition text-sm">
                        Follow
                    </a>
                @endauth
            @endif
        </div>

        {{-- Bio --}}
        @if($page->bio)
            <p class="text-gray-700 text-sm mb-4">{{ $page->bio }}</p>
        @endif

        {{-- Tabs --}}
        <div class="border-b border-gray-200 flex gap-6 mb-6 overflow-x-auto">
            @foreach(['posts' => 'Posts', 'about' => 'About', 'followers' => 'Followers'] as $tab => $label)
                <button @click="activeTab = '{{ $tab }}'"
                        class="pb-3 text-sm font-semibold whitespace-nowrap transition"
                        :class="activeTab === '{{ $tab }}' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- TAB: Posts --}}
        <div x-show="activeTab === 'posts'">
            @if($posts->count())
                <div class="grid grid-cols-1 gap-4">
                    @foreach($posts as $post)
                        <a href="/posts/{{ $post->slug }}"
                           class="flex gap-3 p-3 rounded-xl hover:bg-gray-50 transition border border-gray-100">
                            @if($post->thumbnail_url)
                                <img src="{{ $post->thumbnail_url }}" alt="" class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-sm line-clamp-2">{{ $post->title }}</p>
                                <p class="text-gray-400 text-xs mt-1">{{ $post->published_at?->diffForHumans() }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-4">{{ $posts->links() }}</div>
            @else
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 font-medium">No posts yet</p>
                    @if($isOwner)
                        <a href="/dashboard/posts/create" class="mt-3 inline-block text-blue-600 text-sm font-semibold hover:underline">Create your first post</a>
                    @endif
                </div>
            @endif
        </div>

        {{-- TAB: About --}}
        <div x-show="activeTab === 'about'">
            <div class="space-y-4">
                <div>
                    <h3 class="font-bold text-gray-900 mb-3">Details</h3>
                    <div class="space-y-3 text-sm">
                        @if($page->first_category)
                            <div class="flex items-center gap-3 text-gray-600">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                {{ collect($page->categories)->implode(', ') }}
                            </div>
                        @endif
                        @if($page->location)
                            <div class="flex items-center gap-3 text-gray-600">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                {{ $page->location }}
                            </div>
                        @endif
                        @if($page->website)
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                <a href="{{ $page->website }}" target="_blank" class="text-blue-600 hover:underline">{{ $page->website }}</a>
                            </div>
                        @endif
                        @if($page->email)
                            <div class="flex items-center gap-3 text-gray-600">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $page->email }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB: Followers --}}
        <div x-show="activeTab === 'followers'">
            <p class="text-gray-500 text-sm text-center py-12">
                {{ number_format($page->followers_count) }} {{ Str::plural('follower', $page->followers_count) }}
            </p>
        </div>
    </div>
</div>

<script>
function toggleFollow() {
    const btn = document.getElementById('follow-btn');
    const label = document.getElementById('follow-label');
    const slug = btn.dataset.slug;
    const isFollowing = btn.dataset.following === 'true';

    fetch(`/pages/${slug}/follow`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        btn.dataset.following = data.following ? 'true' : 'false';
        label.textContent = data.following ? 'Following' : 'Follow';
        if (data.following) {
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'text-white');
            btn.classList.add('bg-gray-100', 'hover:bg-gray-200', 'text-gray-800');
        } else {
            btn.classList.remove('bg-gray-100', 'hover:bg-gray-200', 'text-gray-800');
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'text-white');
        }
    });
}

function toggleSavePage() {
    const btn = document.getElementById('save-page-btn');
    const label = document.getElementById('save-page-label');
    const isSaved = btn.dataset.saved === 'true';
    const pageId = btn.dataset.id;

    fetch('/bookmarks/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        },
        body: JSON.stringify({ type: 'page', id: pageId })
    })
    .then(r => r.json())
    .then(data => {
        btn.dataset.saved = data.saved ? 'true' : 'false';
        label.textContent = data.saved ? 'Saved' : 'Save';
        const icon = btn.querySelector('svg');
        if (data.saved) {
            icon.setAttribute('fill', 'currentColor');
            btn.classList.replace('bg-gray-100', 'bg-yellow-50');
            btn.classList.replace('text-gray-700', 'text-yellow-700');
        } else {
            icon.setAttribute('fill', 'none');
            btn.classList.replace('bg-yellow-50', 'bg-gray-100');
            btn.classList.replace('text-yellow-700', 'text-gray-700');
        }
    });
}
</script>
@endsection
