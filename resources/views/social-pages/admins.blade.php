@extends('layouts.app')

@section('title', 'Manage Admins — ' . $page->name)

@section('content')
<div class="min-h-screen bg-gray-50">

    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
        <a href="/pages/{{ $page->slug }}/dashboard" class="p-2 -ml-2 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1">
            <h1 class="font-bold text-gray-900 text-lg">Page Team</h1>
            <p class="text-xs text-gray-400">Invite admins, moderators, and editors</p>
        </div>
    </div>

    <div class="max-w-xl mx-auto px-4 py-6 space-y-4">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif

        {{-- Invite form --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="font-bold text-gray-900">Invite a team member</p>
                <p class="text-xs text-gray-400 mt-0.5">Enter their username (@handle) to invite</p>
            </div>
            <form method="POST" action="/pages/{{ $page->slug }}/admins/invite" class="p-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Username</label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">@</span>
                            <input type="text" name="username" value="{{ old('username') }}" placeholder="username"
                                   class="w-full border border-gray-300 rounded-xl pl-7 pr-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        <select name="role" class="border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                            <option value="admin">Admin</option>
                            <option value="moderator">Moderator</option>
                            <option value="editor">Editor</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl transition">
                    Send Invite
                </button>
            </form>
        </div>

        {{-- Role explanation --}}
        <div class="bg-blue-50 rounded-2xl px-4 py-3 space-y-1.5">
            <p class="text-xs font-bold text-blue-800 uppercase tracking-wide">Role permissions</p>
            <div class="grid grid-cols-1 gap-1 text-xs text-blue-700">
                <div class="flex items-center gap-2"><span class="w-20 font-semibold">Admin</span><span>Full control — post, moderate, manage team</span></div>
                <div class="flex items-center gap-2"><span class="w-20 font-semibold">Moderator</span><span>Review and action reports on this page</span></div>
                <div class="flex items-center gap-2"><span class="w-20 font-semibold">Editor</span><span>Create and delete posts only</span></div>
            </div>
        </div>

        {{-- Current team --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="font-bold text-gray-900">Current team</p>
            </div>

            {{-- Owner row --}}
            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50">
                <img src="{{ $page->owner->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($page->owner->name).'&background=e5e7eb&color=6b7280&size=64' }}"
                     class="w-10 h-10 rounded-full object-cover border border-gray-200 flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $page->owner->name }}</p>
                    <p class="text-xs text-gray-400">@{{ $page->owner->username ?? 'owner' }}</p>
                </div>
                <span class="px-2.5 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">Owner</span>
            </div>

            @forelse($admins as $admin)
            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 last:border-0">
                <img src="{{ $admin->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($admin->user->name ?? 'U').'&background=e5e7eb&color=6b7280&size=64' }}"
                     class="w-10 h-10 rounded-full object-cover border border-gray-200 flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $admin->user->name }}</p>
                    <p class="text-xs text-gray-400">@{{ $admin->user->username ?? '' }}</p>
                    @if($admin->isPending())
                    <p class="text-xs text-amber-500 font-medium">Invite pending</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @php $roleColors = ['admin'=>'blue','moderator'=>'green','editor'=>'orange']; $rc = $roleColors[$admin->role] ?? 'gray'; @endphp
                    <span class="px-2.5 py-1 bg-{{ $rc }}-100 text-{{ $rc }}-700 text-xs font-semibold rounded-full capitalize">{{ $admin->role }}</span>
                    <form method="POST" action="/pages/{{ $page->slug }}/admins/{{ $admin->user_id }}" onsubmit="return confirm('Remove this admin?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600 p-1 rounded transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-gray-400 text-sm">No admins yet. Invite someone above.</div>
            @endforelse
        </div>

    </div>
</div>
@endsection
