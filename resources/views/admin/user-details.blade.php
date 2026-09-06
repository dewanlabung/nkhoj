@extends('layouts.admin')
@section('title', $user->name . ' — User Details')

@section('content')
@if(session('success'))
<div class="mb-4 text-sm text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl px-4 py-3">{{ session('success') }}</div>
@endif

<div class="flex items-center gap-3 mb-6">
    <nav class="text-xs text-gray-400 flex items-center gap-1.5">
        <a href="/admin" class="hover:text-brand-500">Home</a><span>›</span>
        <a href="/admin/users" class="hover:text-brand-500">Users</a><span>›</span>
        <span>{{ $user->name }}</span>
    </nav>
    <div class="ml-auto flex items-center gap-2">
        <a href="/admin/users/{{ $user->id }}/edit"
            class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-brand-500 hover:bg-brand-600 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            Edit Profile
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── LEFT SIDEBAR ── --}}
    <div class="space-y-4">

        {{-- Avatar + role badge --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 text-center">
            @if($user->avatar_url)
            <img src="{{ $user->avatar_url }}" class="w-24 h-24 rounded-full object-cover mx-auto mb-3 border-4 border-white dark:border-gray-700 shadow-md">
            @else
            <div class="w-24 h-24 rounded-full mx-auto mb-3 flex items-center justify-center text-3xl font-black text-white shadow-md"
                style="background: hsl({{ crc32($user->name) % 360 }}, 60%, 55%)">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            @endif
            <h2 class="font-bold text-gray-900 dark:text-white text-lg">{{ $user->displayName() }}</h2>
            <p class="text-sm text-gray-400">@{{ $user->username }}</p>
            <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-bold {{ $user->roleBadgeClass() }}">
                {{ $user->roleLabel() }}
            </span>
            @if($user->is_banned)
            <span class="ml-1 inline-block px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full text-xs font-bold">Banned</span>
            @endif
        </div>

        {{-- Stats chips --}}
        <div class="grid grid-cols-3 gap-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3 text-center shadow-sm">
                <p class="text-xl font-black text-brand-500">{{ number_format($user->posts_count) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Posts</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3 text-center shadow-sm">
                <p class="text-xl font-black text-indigo-500">{{ number_format($user->comments_count) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Comments</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3 text-center shadow-sm">
                <p class="text-xl font-black text-green-500">${{ number_format($user->balance, 2) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Balance</p>
            </div>
        </div>

        {{-- Details --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Details</h3>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400 flex-shrink-0">User ID</dt>
                    <dd class="font-mono text-xs text-gray-700 dark:text-gray-300 text-right">{{ $user->id }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400 flex-shrink-0">Status</dt>
                    <dd>
                        @if($user->is_banned)
                        <span class="px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full text-xs font-semibold">Banned</span>
                        @else
                        <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full text-xs font-semibold">Active</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400 flex-shrink-0">Email</dt>
                    <dd class="text-right text-gray-700 dark:text-gray-300 text-xs truncate">
                        {{ $user->email }}
                        @if($user->email_verified_at)
                        <span class="text-green-500 ml-1">✓</span>
                        @endif
                    </dd>
                </div>
                @if($user->first_name)
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400 flex-shrink-0">First Name</dt>
                    <dd class="text-gray-700 dark:text-gray-300">{{ $user->first_name }}</dd>
                </div>
                @endif
                @if($user->last_name)
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400 flex-shrink-0">Last Name</dt>
                    <dd class="text-gray-700 dark:text-gray-300">{{ $user->last_name }}</dd>
                </div>
                @endif
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400 flex-shrink-0">Profile URL</dt>
                    <dd class="text-right">
                        <a href="/profile/{{ $user->username }}" target="_blank" class="text-brand-500 hover:underline text-xs">{{ $user->username }}</a>
                    </dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400 flex-shrink-0">Page Views</dt>
                    <dd class="text-gray-700 dark:text-gray-300">{{ number_format($user->profile_view_count) }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400 flex-shrink-0">Reward System</dt>
                    <dd>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $user->reward_system ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500' }}">
                            {{ $user->reward_system ? 'Enabled' : 'Disabled' }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400 flex-shrink-0">Last Seen</dt>
                    <dd class="text-gray-700 dark:text-gray-300 text-xs">{{ $user->lastSeenLabel() }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400 flex-shrink-0">Joined</dt>
                    <dd class="text-gray-700 dark:text-gray-300 text-xs">{{ $user->created_at->format('d M Y') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Social Accounts --}}
        @if(!empty($user->social_links))
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Social Accounts</h3>
            <div class="space-y-2">
                @foreach(User::socialPlatforms() as $key => $label)
                @if(!empty($user->social_links[$key]))
                <a href="{{ $user->social_links[$key] }}" target="_blank" rel="noopener"
                    class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 hover:text-brand-500 transition-colors">
                    <span class="w-6 h-6 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($key,0,1)) }}</span>
                    {{ $label }}
                    <svg class="w-3 h-3 text-gray-300 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-2">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Quick Actions</h3>
            <a href="/admin/users/{{ $user->id }}/permissions"
                class="flex items-center gap-2 w-full px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Roles &amp; Permissions
            </a>
            @if(!$user->email_verified_at)
            <form method="POST" action="/admin/users/{{ $user->id }}/verify-email">
                @csrf
                <button class="flex items-center gap-2 w-full px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Verify Email
                </button>
            </form>
            @endif
            <form method="POST" action="/admin/users/{{ $user->id }}/ban">
                @csrf
                <button class="flex items-center gap-2 w-full px-3 py-2.5 text-sm {{ $user->is_banned ? 'text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20' : 'text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20' }} rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    {{ $user->is_banned ? 'Unban User' : 'Ban User' }}
                </button>
            </form>
            @if($user->id !== auth()->id())
            <form method="POST" action="/admin/users/{{ $user->id }}/impersonate">
                @csrf
                <button class="flex items-center gap-2 w-full px-3 py-2.5 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors"
                    onclick="return confirm('Login as {{ addslashes($user->name) }}?')">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Login as User
                </button>
            </form>
            @endif
            @if($user->id !== auth()->id())
            <form method="POST" action="/admin/users/{{ $user->id }}" onsubmit="return confirm('Delete {{ addslashes($user->name) }} permanently?')">
                @csrf @method('DELETE')
                <button class="flex items-center gap-2 w-full px-3 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete User
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- ── RIGHT MAIN ── --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Bio --}}
        @if($user->bio)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">About</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $user->bio }}</p>
        </div>
        @endif

        {{-- Premium / Subscription Placeholder --}}
        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl border border-indigo-100 dark:border-indigo-800 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </div>
            <div>
                <p class="font-bold text-indigo-700 dark:text-indigo-300 text-sm">Premium Membership</p>
                <p class="text-xs text-indigo-500 dark:text-indigo-400 mt-0.5">No Active Subscription</p>
            </div>
        </div>

        {{-- Recent Posts --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-bold text-gray-900 dark:text-white text-sm">Recent Posts</h3>
                <span class="text-xs text-gray-400">{{ $user->posts_count }} total</span>
            </div>
            @php $recentPosts = $user->posts()->with('category')->latest('published_at')->limit(5)->get(); @endphp
            @if($recentPosts->isEmpty())
            <div class="px-5 py-8 text-center text-sm text-gray-400">No posts yet.</div>
            @else
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-50 dark:border-gray-700">
                    <th class="px-5 py-2.5 text-left text-xs font-bold text-gray-400 uppercase">Title</th>
                    <th class="px-5 py-2.5 text-left text-xs font-bold text-gray-400 uppercase">Status</th>
                    <th class="px-5 py-2.5 text-right text-xs font-bold text-gray-400 uppercase">Views</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach($recentPosts as $post)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-5 py-3">
                            <a href="/posts/{{ $post->slug }}" target="_blank" class="font-medium text-gray-800 dark:text-gray-200 hover:text-brand-500 line-clamp-1">{{ $post->title }}</a>
                            <p class="text-xs text-gray-400">{{ $post->published_at?->format('d M Y') ?? 'Draft' }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $post->status === 'published' ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400' }}">
                                {{ ucfirst($post->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right text-gray-600 dark:text-gray-300 font-medium">{{ number_format($post->view_count) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

    </div>
</div>
@endsection
