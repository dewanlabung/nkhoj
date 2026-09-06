@extends('layouts.admin')
@section('title', 'Users')

@section('content')
@php
$badgeColorMap = ['admin'=>'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400','editor'=>'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400','reporter'=>'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400','reader'=>'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'];
@endphp

@if(session('success'))
<div class="mb-4 text-sm text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl px-4 py-3 flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {!! session('success') !!}
</div>
@endif
@if(session('error'))
<div class="mb-4 text-sm text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3">{{ session('error') }}</div>
@endif

{{-- Impersonate banner --}}
@if(session('impersonating_admin_id'))
<div class="mb-4 flex items-center justify-between bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl px-4 py-3">
    <p class="text-sm text-amber-700 dark:text-amber-400 font-medium">⚠ You are impersonating <strong>{{ auth()->user()->name }}</strong></p>
    <a href="/admin/users/stop-impersonating" class="text-xs font-bold text-white bg-amber-500 hover:bg-amber-600 px-3 py-1.5 rounded-lg transition-colors">Return to Admin</a>
</div>
@endif

<div x-data="usersManager()">

    {{-- Top bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div>
            <nav class="text-xs text-gray-400 mb-1 flex items-center gap-1.5">
                <a href="/admin" class="hover:text-brand-500">Home</a>
                <span>›</span><span>Users</span>
            </nav>
        </div>
        <div class="flex items-center gap-2">
            {{-- Filter dropdown --}}
            <div x-data="{
                    open: false, top: 0, right: 0,
                    toggle(btn) {
                        const r = btn.getBoundingClientRect();
                        this.top = r.bottom + 4;
                        this.right = window.innerWidth - r.right;
                        this.open = !this.open;
                    }
                }"
                class="inline-block" @click.outside="open = false">
                <button @click="toggle($el)" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                </button>
                <div x-show="open" x-cloak
                    class="fixed w-56 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-xl z-[999] p-3 space-y-2"
                    :style="`top:${top}px; right:${right}px`">
                    <form method="GET" action="/admin/users" class="space-y-2">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name / email…" class="w-full text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <select name="role" class="w-full text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="">All Roles</option>
                            @foreach(['reader'=>'Member','reporter'=>'Author','editor'=>'Editor','admin'=>'Super Admin'] as $v => $l)
                            <option value="{{ $v }}" {{ request('role')===$v?'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="w-full text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option>
                            <option value="banned" {{ request('status')==='banned'?'selected':'' }}>Banned</option>
                        </select>
                        <button class="w-full py-2 bg-brand-500 text-white text-xs font-bold rounded-lg hover:bg-brand-600 transition-colors">Apply</button>
                    </form>
                </div>
            </div>
            <a href="/admin/users/create"
                class="flex items-center gap-2 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add User
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
            <p class="text-xs text-gray-400">{{ $users->total() }} total users</p>
            <form method="GET" class="flex items-center gap-2">
                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="role" value="{{ request('role') }}">
                <select name="per_page" onchange="this.form.submit()" class="text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2 py-1.5 focus:outline-none">
                    @foreach([10,20,50,100] as $pp)
                    <option value="{{ $pp }}" {{ request('per_page',20)==$pp?'selected':'' }}>{{ $pp }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-50 dark:border-gray-700">
                        <th class="px-5 py-3 w-8"><input type="checkbox" @change="toggleAll($event)" class="rounded border-gray-300 text-brand-500 focus:ring-brand-400"></th>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider w-12">ID</th>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">User</th>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Activity</th>
                        <th class="text-right px-5 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Options</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        {{-- Checkbox --}}
                        <td class="px-5 py-4 w-8">
                            <input type="checkbox" :value="{{ $user->id }}" x-model="selected" class="rounded border-gray-300 text-brand-500 focus:ring-brand-400">
                        </td>
                        {{-- ID --}}
                        <td class="px-4 py-4 text-xs text-gray-400">{{ $user->id }}</td>
                        {{-- User --}}
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0 border border-gray-100 dark:border-gray-600">
                                @else
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0 shadow-sm"
                                    style="background: hsl({{ crc32($user->name) % 360 }}, 60%, 55%)">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <a href="/admin/users/{{ $user->id }}" class="font-semibold text-gray-900 dark:text-white hover:text-brand-500 transition-colors block">{{ $user->name }}</a>
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs text-gray-400">{{ $user->email }}</span>
                                        @if($user->email_verified_at)
                                        <svg class="w-3 h-3 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        {{-- Role --}}
                        <td class="px-4 py-4">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $badgeColorMap[$user->role] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ $user->roleLabel() }}
                            </span>
                        </td>
                        {{-- Status --}}
                        <td class="px-4 py-4">
                            @if($user->is_banned)
                            <span class="px-2.5 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-700 rounded-lg text-xs font-semibold">Banned</span>
                            @else
                            <span class="px-2.5 py-1 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-700 rounded-lg text-xs font-semibold">Active</span>
                            @endif
                        </td>
                        {{-- Activity --}}
                        <td class="px-4 py-4 text-xs text-gray-500 dark:text-gray-400 space-y-0.5">
                            <p><span class="text-gray-400">Registration Date:</span> <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $user->created_at->format('Y-m-d H:i') }}</span></p>
                            <p><span class="text-gray-400">Last seen:</span> <span class="text-brand-500">{{ $user->lastSeenLabel() }}</span></p>
                            <p><span class="text-gray-400">Posts:</span> {{ $user->posts_count ?? 0 }} · Comments: {{ $user->comments_count ?? 0 }}</p>
                        </td>
                        {{-- Options --}}
                        <td class="px-5 py-4 text-right">
                            <div x-data="{
                                    open: false,
                                    top: 0, right: 0,
                                    toggle(btn) {
                                        const r = btn.getBoundingClientRect();
                                        this.top = r.bottom + 4;
                                        this.right = window.innerWidth - r.right;
                                        this.open = !this.open;
                                    }
                                }"
                                class="inline-block" @click.outside="open = false">
                                <button @click="toggle($el)"
                                    class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    Select
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-cloak
                                    class="fixed w-52 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-2xl z-[999] overflow-hidden"
                                    :style="`top:${top}px; right:${right}px`">
                                    <a href="/admin/users/{{ $user->id }}"
                                        class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        User Details
                                    </a>
                                    <a href="/admin/users/{{ $user->id }}/permissions"
                                        class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        Roles &amp; Permissions
                                    </a>
                                    <form method="POST" action="/admin/users/{{ $user->id }}/reward-system">
                                        @csrf
                                        <button class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            {{ $user->reward_system ? 'Disable' : 'Enable' }} Rewards
                                        </button>
                                    </form>
                                    @if(!$user->email_verified_at)
                                    <form method="POST" action="/admin/users/{{ $user->id }}/verify-email">
                                        @csrf
                                        <button class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Verify Email
                                        </button>
                                    </form>
                                    @endif
                                    <form method="POST" action="/admin/users/{{ $user->id }}/ban">
                                        @csrf
                                        <button class="flex items-center gap-2 w-full px-4 py-2.5 text-sm {{ $user->is_banned ? 'text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20' : 'text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20' }} border-t border-gray-50 dark:border-gray-700">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            {{ $user->is_banned ? 'Unban User' : 'Ban User' }}
                                        </button>
                                    </form>
                                    <a href="/admin/users/{{ $user->id }}/edit"
                                        class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Edit
                                    </a>
                                    {{-- Impersonate --}}
                                    @if($user->id !== auth()->id())
                                    <button @click="open = false; confirmImpersonate({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        Login as User
                                    </button>
                                    @endif
                                    @if($user->id !== auth()->id())
                                    <form method="POST" action="/admin/users/{{ $user->id }}"
                                        onsubmit="return confirm('Permanently delete {{ addslashes($user->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 border-t border-gray-50 dark:border-gray-700">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-50 dark:border-gray-700">{{ $users->links() }}</div>
    </div>

    {{-- Impersonate Confirmation Modal --}}
    <div x-show="showImpersonate" x-cloak
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click.self="showImpersonate = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="1.5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white">Impersonate user</h3>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Are you sure you want to login as <strong x-text="impersonateName" class="text-gray-900 dark:text-white"></strong>?</p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mb-6">This will log you out of your current account and log you in as the user.</p>
            <div class="flex gap-3">
                <button @click="showImpersonate = false" class="flex-1 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                <form :action="'/admin/users/' + impersonateId + '/impersonate'" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2.5 text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-xl transition-colors">Login</button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function usersManager() {
    return {
        selected: [],
        showImpersonate: false,
        impersonateId: null,
        impersonateName: '',

        toggleAll(e) {
            this.selected = e.target.checked
                ? @json($users->pluck('id'))
                : [];
        },

        confirmImpersonate(id, name) {
            this.impersonateId = id;
            this.impersonateName = name;
            this.showImpersonate = true;
        },
    }
}
</script>
@endsection
