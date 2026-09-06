@extends('layouts.admin')
@section('title', 'Roles & Permissions')

@section('content')
@php
$badgeColorMap = [
    'red'    => 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-700',
    'rose'   => 'bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-700',
    'purple' => 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-700',
    'green'  => 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-700',
    'blue'   => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-700',
    'indigo' => 'bg-indigo-100 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-700',
    'teal'   => 'bg-teal-100 text-teal-700 border-teal-200 dark:bg-teal-900/30 dark:text-teal-400 dark:border-teal-700',
    'amber'  => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-700',
    'gray'   => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600',
];
$badgeIcons = [
    'shield'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
    'pencil'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>',
    'cog'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
    'user'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
    'star'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
    'newspaper' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>',
    'plus'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',
];
@endphp

@if(session('success'))
<div class="mb-4 text-sm text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl px-4 py-3 flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 text-sm text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3">{{ session('error') }}</div>
@endif

<div x-data="rolesManager()" x-init="init()">

    {{-- Top bar --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <nav class="text-xs text-gray-400 mb-1 flex items-center gap-1.5">
                <a href="/admin" class="hover:text-brand-500">Home</a>
                <span>›</span>
                <span>Roles &amp; Permissions</span>
            </nav>
        </div>
        <button @click="openAdd()"
            class="flex items-center gap-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Role
        </button>
    </div>

    {{-- Roles Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700">
                        <th class="text-left px-6 py-3.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Role Name</th>
                        <th class="text-left px-6 py-3.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Badge</th>
                        <th class="text-left px-6 py-3.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Permissions</th>
                        <th class="text-left px-6 py-3.5 text-xs font-bold text-gray-400 uppercase tracking-wider w-28">AI Credits</th>
                        <th class="text-right px-6 py-3.5 text-xs font-bold text-gray-400 uppercase tracking-wider w-28">Options</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach($roles as $role)
                    @php
                    $perms = $role->permissions ?? [];
                    $isAll = in_array('add_post', $perms) && count($perms) >= count($allPerms) - 1;
                    $clr   = $badgeColorMap[$role->badge_color] ?? $badgeColorMap['gray'];
                    $iconPath = $badgeIcons[$role->badge_icon] ?? $badgeIcons['user'];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        {{-- Role Name --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $role->name }}</span>
                                @if($role->is_default)
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded border border-gray-200 dark:border-gray-600">Default</span>
                                @endif
                            </div>
                            @if($role->name_ne)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $role->name_ne }}</p>
                            @endif
                        </td>

                        {{-- Badge --}}
                        <td class="px-6 py-4">
                            @if($role->badge_label)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-xs font-bold {{ $clr }}">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $iconPath !!}</svg>
                                {{ $role->badge_label }}
                            </span>
                            @else
                            <span class="text-gray-300 dark:text-gray-600 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Permissions --}}
                        <td class="px-6 py-4">
                            @if($role->slug === 'admin' || count($perms) >= count($allPerms))
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-700 rounded-lg text-xs font-bold">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                All Permissions
                            </span>
                            @elseif(count($perms) === 0)
                            <span class="text-gray-300 dark:text-gray-600 text-xs">No permissions</span>
                            @else
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($perms, 0, 5) as $pKey)
                                @if(isset($allPerms[$pKey]))
                                <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 rounded text-[11px] font-medium">{{ $allPerms[$pKey] }}</span>
                                @endif
                                @endforeach
                                @if(count($perms) > 5)
                                <span class="px-2 py-0.5 bg-gray-50 dark:bg-gray-700 text-gray-400 rounded text-[11px]">+{{ count($perms) - 5 }} more</span>
                                @endif
                            </div>
                            @endif
                        </td>

                        {{-- AI Credits --}}
                        <td class="px-6 py-4">
                            @if($role->ai_credits >= 99999)
                            <span class="text-xs font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Unlimited
                            </span>
                            @elseif($role->ai_credits > 0)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-purple-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ number_format($role->ai_credits) }} <span class="font-normal text-gray-400">/mo</span></span>
                            </div>
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>

                        {{-- Options --}}
                        <td class="px-6 py-4 text-right">
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
                                <button @click="toggle($el)"
                                    class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    Select
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-cloak
                                    class="fixed w-40 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-2xl z-[999] overflow-hidden"
                                    :style="`top:${top}px; right:${right}px`">
                                    <button @click="open = false; openEdit({{ $role->id }}, {{ $role->toJson() }})"
                                        class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Edit
                                    </button>
                                    @if(!$role->is_system)
                                    <form method="POST" action="/admin/roles/{{ $role->id }}"
                                        onsubmit="return confirm('Delete role {{ addslashes($role->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors border-t border-gray-50 dark:border-gray-700">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Credit Token Summary --}}
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
            <h3 class="font-bold text-gray-900 dark:text-white">AI Credit Token Allocations</h3>
            <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full ml-1">Per Month</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($roles as $role)
            @php $clr2 = $badgeColorMap[$role->badge_color] ?? $badgeColorMap['gray']; @endphp
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-100 dark:border-gray-600">
                <div class="flex items-center gap-2 mb-2">
                    @if($role->badge_label)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded border text-[10px] font-bold {{ $clr2 }}">{{ $role->badge_label }}</span>
                    @else
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-300">{{ $role->name }}</span>
                    @endif
                </div>
                <p class="text-2xl font-black text-gray-900 dark:text-white">
                    {{ $role->ai_credits >= 99999 ? '∞' : number_format($role->ai_credits) }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">AI credits / month</p>
                <div class="mt-2 h-1 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                    @php $maxC = $roles->where('ai_credits','<',99999)->max('ai_credits') ?: 1; $pct = $role->ai_credits >= 99999 ? 100 : min(100, round(($role->ai_credits / $maxC) * 100)); @endphp
                    <div class="h-full rounded-full" style="width:{{ $pct }}%; background: {{ ['red'=>'#ef4444','rose'=>'#f43f5e','purple'=>'#a855f7','green'=>'#22c55e','blue'=>'#3b82f6','indigo'=>'#6366f1','teal'=>'#14b8a6','amber'=>'#f59e0b','gray'=>'#9ca3af'][$role->badge_color] ?? '#9ca3af' }}"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ══════ ADD ROLE MODAL ══════════════════════════════════════ --}}
    <div x-show="showAdd" x-cloak
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click.self="showAdd = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex-shrink-0">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Add Role</h3>
                <button @click="showAdd = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="overflow-y-auto flex-1">
                <form id="addForm" method="POST" action="/admin/roles" class="p-6 space-y-5">
                    @csrf
                    @include('admin._role_form', ['formData' => null])
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="showAdd = false"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Close
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-lg transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════ EDIT ROLE MODAL ══════════════════════════════════════ --}}
    <div x-show="showEdit" x-cloak
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click.self="showEdit = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex-shrink-0">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Role</h3>
                <button @click="showEdit = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="overflow-y-auto flex-1">
                <form :action="'/admin/roles/' + editId" method="POST" class="p-6 space-y-5">
                    @csrf @method('PUT')

                    {{-- Role Name --}}
                    <div>
                        <label class="text-xs font-bold text-gray-600 dark:text-gray-300 block mb-1.5">Role Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" :value="editData.name" required
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <input type="text" name="name_ne" :value="editData.name_ne"
                            placeholder="Nepali / other language name"
                            class="mt-2 w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    {{-- Badge --}}
                    <div>
                        <label class="text-xs font-bold text-gray-600 dark:text-gray-300 block mb-1.5">User Profile Badge</label>
                        <div class="grid grid-cols-2 gap-3">
                            <select name="badge_label" class="text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="">— No Badge —</option>
                                @foreach($badgeOptions as $b)
                                @if($b['value'])
                                <option :selected="editData.badge_label === '{{ $b['value'] }}'" value="{{ $b['value'] }}">{{ $b['label'] }}</option>
                                @endif
                                @endforeach
                            </select>
                            <select name="badge_color" class="text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                @foreach(['gray'=>'Gray','red'=>'Red','rose'=>'Rose','purple'=>'Purple','green'=>'Green','blue'=>'Blue','indigo'=>'Indigo','teal'=>'Teal','amber'=>'Amber (Gold)'] as $v => $l)
                                <option value="{{ $v }}" :selected="editData.badge_color === '{{ $v }}'">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <select name="badge_icon" class="mt-2 w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            @foreach(['user'=>'User (Default)','shield'=>'Shield','pencil'=>'Pencil / Editor','cog'=>'Cog / Moderator','star'=>'Star / VIP','newspaper'=>'Newspaper / Reporter','plus'=>'Plus / Contributor'] as $v => $l)
                            <option value="{{ $v }}" :selected="editData.badge_icon === '{{ $v }}'">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- AI Credits --}}
                    <div>
                        <label class="text-xs font-bold text-gray-600 dark:text-gray-300 block mb-1">
                            AI Credit Tokens
                            <span class="ml-1 font-normal text-gray-400">(per month, 0 = disabled, 99999 = unlimited)</span>
                        </label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-3.5 w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                            <input type="number" name="ai_credits" :value="editData.ai_credits" min="0" max="99999"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>

                    {{-- Permissions --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-bold text-gray-600 dark:text-gray-300">Permissions</label>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="selectAllPerms()" class="text-xs text-brand-500 hover:underline">All</button>
                                <span class="text-gray-300">·</span>
                                <button type="button" @click="clearAllPerms()" class="text-xs text-gray-400 hover:text-red-500 hover:underline">Clear</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-y-2 gap-x-6 border border-gray-100 dark:border-gray-700 rounded-xl p-4 bg-gray-50 dark:bg-gray-700/30 max-h-56 overflow-y-auto">
                            @foreach($allPerms as $key => $label)
                            <label class="flex items-center gap-2.5 cursor-pointer group">
                                <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                    :checked="editPerms.includes('{{ $key }}')"
                                    @change="togglePerm('{{ $key }}')"
                                    class="w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-400 flex-shrink-0">
                                <span class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-brand-500 transition-colors">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="showEdit = false"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Close
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-lg transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function rolesManager() {
    return {
        showAdd: false,
        showEdit: false,
        editId: null,
        editData: {},
        editPerms: [],
        allPermKeys: @json(array_keys($allPerms)),

        init() {},

        openAdd() { this.showAdd = true; },

        openEdit(id, data) {
            this.editId   = id;
            this.editData = data;
            this.editPerms = data.permissions || [];
            this.showEdit  = true;
        },

        togglePerm(key) {
            if (this.editPerms.includes(key)) {
                this.editPerms = this.editPerms.filter(p => p !== key);
            } else {
                this.editPerms.push(key);
            }
        },

        selectAllPerms() { this.editPerms = [...this.allPermKeys]; },
        clearAllPerms()  { this.editPerms = []; },
    }
}
</script>
@endsection
