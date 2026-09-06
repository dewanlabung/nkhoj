@extends('layouts.admin')
@section('title', 'Permissions — ' . $user->name)

@section('content')
@if(session('success'))
<div class="mb-4 text-sm text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl px-4 py-3">{{ session('success') }}</div>
@endif

<div class="flex items-center gap-3 mb-6">
    <nav class="text-xs text-gray-400 flex items-center gap-1.5">
        <a href="/admin" class="hover:text-brand-500">Home</a><span>›</span>
        <a href="/admin/users" class="hover:text-brand-500">Users</a><span>›</span>
        <a href="/admin/users/{{ $user->id }}" class="hover:text-brand-500">{{ $user->name }}</a><span>›</span>
        <span>Permissions</span>
    </nav>
</div>

{{-- User header --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 mb-5 flex items-center gap-4">
    @if($user->avatar_url)
    <img src="{{ $user->avatar_url }}" class="w-10 h-10 rounded-full object-cover border-2 border-gray-100 dark:border-gray-600 flex-shrink-0">
    @else
    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
        style="background: hsl({{ crc32($user->name) % 360 }}, 60%, 55%)">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    @endif
    <div>
        <p class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</p>
        <p class="text-xs text-gray-400">{{ $user->email }}</p>
    </div>
    <a href="/admin/users/{{ $user->id }}" class="ml-auto text-xs text-brand-500 hover:underline">← Back to Profile</a>
</div>

<form method="POST" action="/admin/users/{{ $user->id }}/permissions" x-data="permsManager()">
    @csrf @method('PUT')

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200 dark:border-gray-700 mb-5 gap-0">
        @foreach(['details'=>'Details','permissions'=>'Roles & Permissions','datetime'=>'Date & Time'] as $tab => $label)
        <button type="button" @click="activeTab = '{{ $tab }}'"
            :class="activeTab === '{{ $tab }}' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition-colors -mb-px">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Tab: Details --}}
    <div x-show="activeTab === 'details'" class="space-y-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-400">User ID</dt>
                    <dd class="font-mono text-xs text-gray-700 dark:text-gray-300">{{ $user->id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Username</dt>
                    <dd class="text-gray-700 dark:text-gray-300">@{{ $user->username }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Email</dt>
                    <dd class="text-gray-700 dark:text-gray-300">{{ $user->email }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Current Role</dt>
                    <dd><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $user->roleBadgeClass() }}">{{ $user->roleLabel() }}</span></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Status</dt>
                    <dd>{{ $user->is_banned ? 'Banned' : 'Active' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Balance</dt>
                    <dd class="text-gray-700 dark:text-gray-300">${{ number_format($user->balance, 2) }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Tab: Roles & Permissions --}}
    <div x-show="activeTab === 'permissions'" class="space-y-5">

        {{-- Role selector --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-3">Assign Role</h3>
            <div class="flex flex-wrap gap-2">
                @foreach(['reader'=>['Member','bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'],'reporter'=>['Author','bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'],'editor'=>['Editor','bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'],'admin'=>['Super Admin','bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400']] as $val => [$lbl, $cls])
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="{{ $val }}" {{ $user->role === $val ? 'checked' : '' }} class="sr-only peer">
                    <span class="px-4 py-2 rounded-lg text-sm font-semibold border-2 border-transparent peer-checked:border-brand-500 {{ $cls }} block transition-all hover:ring-2 hover:ring-brand-300">
                        {{ $lbl }}
                    </span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Extra Permissions accordion --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900 dark:text-white text-sm">Extra Permissions</h3>
                <div class="flex gap-2">
                    <button type="button" @click="selectAll()" class="text-xs text-brand-500 hover:underline">Select all</button>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <button type="button" @click="clearAll()" class="text-xs text-red-500 hover:underline">Clear all</button>
                </div>
            </div>

            @php
            $groups = [
                'Posts & Content' => ['add_post','edit_own_post','ai_writer','media'],
                'Administration'  => ['categories','tags','comments','contact','polls'],
                'Community'       => ['newsletter','widgets','ad_spaces'],
                'System'          => ['users','roles','settings','content_settings','email_settings','security','seo_tools','cache_system','analytics','backup'],
            ];
            $userPerms = $user->extra_permissions ?? [];
            @endphp

            <div class="space-y-3">
                @foreach($groups as $groupLabel => $keys)
                <div x-data="{ open: true }" class="border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
                    <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        {{ $groupLabel }}
                        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" class="px-4 pb-4 grid grid-cols-2 gap-2 border-t border-gray-50 dark:border-gray-700 pt-3">
                        @foreach($keys as $perm)
                        @if(isset($allPerms[$perm]))
                        <label class="flex items-center gap-2.5 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="{{ $perm }}"
                                {{ in_array($perm, $userPerms) ? 'checked' : '' }}
                                x-ref="perm_{{ $perm }}"
                                class="rounded border-gray-300 dark:border-gray-600 text-brand-500 focus:ring-brand-400">
                            <span class="text-xs text-gray-600 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">{{ $allPerms[$perm] }}</span>
                        </label>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
                Save Permissions
            </button>
        </div>
    </div>

    {{-- Tab: Date & Time --}}
    <div x-show="activeTab === 'datetime'" class="space-y-5">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-400">Registered</dt>
                    <dd class="text-gray-700 dark:text-gray-300">{{ $user->created_at->format('d M Y, H:i') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Last Updated</dt>
                    <dd class="text-gray-700 dark:text-gray-300">{{ $user->updated_at->format('d M Y, H:i') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Email Verified</dt>
                    <dd class="text-gray-700 dark:text-gray-300">
                        {{ $user->email_verified_at ? $user->email_verified_at->format('d M Y, H:i') : '—' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Last Seen</dt>
                    <dd class="text-gray-700 dark:text-gray-300">{{ $user->lastSeenLabel() }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">AI Credits Used</dt>
                    <dd class="text-gray-700 dark:text-gray-300">{{ number_format($user->ai_credits_used) }}</dd>
                </div>
                @if($user->ai_credits_reset_at)
                <div class="flex justify-between">
                    <dt class="text-gray-400">Credits Reset At</dt>
                    <dd class="text-gray-700 dark:text-gray-300">{{ $user->ai_credits_reset_at->format('d M Y') }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

</form>

<script>
function permsManager() {
    return {
        activeTab: 'permissions',
        allPerms: @json(array_keys($allPerms)),

        selectAll() {
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = true);
        },
        clearAll() {
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = false);
        },
    }
}
</script>
@endsection
