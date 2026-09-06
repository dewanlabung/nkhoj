@extends('layouts.admin')
@section('title', 'Badges')

@push('head')
<style>
.badge-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 11px 3px 8px;
    border-radius: 20px;
    font-size: 12px; font-weight: 700; color: #fff;
    letter-spacing: .01em;
}
.badge-pill svg { width: 13px; height: 13px; flex-shrink: 0; fill: #fff; }

.icon-btn {
    width: 52px; height: 52px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 8px;
    border: 1.5px dashed #374151;
    cursor: pointer;
    transition: all .15s;
    background: transparent;
}
.icon-btn:hover { border-color: #6366f1; background: rgba(99,102,241,.1); }
.icon-btn.selected { border-color: #6366f1; border-style: solid; background: rgba(99,102,241,.18); }
.icon-btn svg { width: 20px; height: 20px; fill: #9ca3af; }
.icon-btn.selected svg { fill: #818cf8; }
html:not(.dark) .icon-btn { border-color: #d1d5db; }
html:not(.dark) .icon-btn:hover { border-color: #6366f1; }

.opt-menu {
    position: fixed;
    min-width: 128px;
    background: #1f2937;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,.4);
    z-index: 9999;
    overflow: hidden;
}
html:not(.dark) .opt-menu { background:#fff; border-color:#e5e7eb; box-shadow:0 4px 16px rgba(0,0,0,.12); }
.opt-menu a, .opt-menu button {
    display: flex; align-items: center; gap: 8px;
    width: 100%; padding: 9px 14px;
    font-size: 13px; color: #d1d5db; text-decoration: none;
    background: none; border: none; cursor: pointer; text-align: left;
    transition: background .12s;
}
html:not(.dark) .opt-menu a, html:not(.dark) .opt-menu button { color: #374151; }
.opt-menu button:hover, .opt-menu a:hover { background: rgba(255,255,255,.07); }
html:not(.dark) .opt-menu button:hover, html:not(.dark) .opt-menu a:hover { background: #f3f4f6; }
.opt-menu .del { color: #f87171; }
html:not(.dark) .opt-menu .del { color: #ef4444; }
</style>
@endpush

@section('content')
@php
    $iconKeys = array_keys($icons);
@endphp

<div x-data="badgesApp()" @click="closeMenus()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Badges</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Manage user badges displayed on profiles and posts.</p>
        </div>
        <button @click.stop="openAddModal()"
            class="flex items-center gap-1.5 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Add Badge
        </button>
    </div>

    @if(session('success'))
    <div class="mb-5 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm rounded-xl border border-green-100 dark:border-green-800/40">
        {{ session('success') }}
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700">
                    <th class="text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-6 py-3.5 w-2/5">Name</th>
                    <th class="text-left text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-6 py-3.5">Preview</th>
                    <th class="text-right text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-6 py-3.5 w-32">Options</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                @forelse($badges as $badge)
                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/20 transition-colors">
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $badge->name }}</span>
                        @if($badge->name_ne)
                        <span class="ml-2 text-xs text-gray-400 font-normal">({{ $badge->name_ne }})</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="badge-pill" style="background: {{ $badge->color }}">
                            @if($badge->icon && $badge->icon !== 'none' && isset($icons[$badge->icon]))
                            {!! $icons[$badge->icon] !!}
                            @endif
                            {{ $badge->name }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right" @click.stop>
                        <button
                            @click.stop="toggleMenu($event, {{ $badge->id }})"
                            class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 transition-colors">
                            Select
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div class="opt-menu"
                            x-show="openId === {{ $badge->id }}"
                            x-cloak
                            :style="menuPos"
                            @click.stop>
                            <button @click="openEditModal({{ $badge->id }}, {{ json_encode($badge->name) }}, {{ json_encode($badge->name_ne ?? '') }}, {{ json_encode($badge->color) }}, {{ json_encode($badge->icon ?? 'none') }})">
                                <svg class="w-3.5 h-3.5 text-blue-400 fill-blue-400" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 000-1.41l-2.34-2.34a1 1 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                Edit
                            </button>
                            <form method="POST" action="/admin/badges/{{ $badge->id }}" onsubmit="return confirm('Delete this badge?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="del">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="w-10 h-10 text-gray-200 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            <p class="text-sm text-gray-400 dark:text-gray-500">No badges yet.</p>
                            <button @click="openAddModal()" class="text-sm text-brand-500 hover:text-brand-600 font-medium">+ Create your first badge</button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ═══ ADD MODAL ═══ --}}
    <template x-teleport="body">
    <div x-show="showAdd" x-cloak
        class="fixed inset-0 z-[999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        x-transition:enter="transition duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        @click="showAdd = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-[440px] max-h-[90vh] overflow-y-auto"
            @click.stop
            x-transition:enter="transition duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800">
                <h3 class="font-bold text-gray-900 dark:text-white">Add Badge</h3>
                <button @click="showAdd = false" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="/admin/badges" class="px-6 py-5 space-y-5">
                @csrf

                {{-- Name tabs --}}
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-2">Name <span class="text-red-400">*</span></label>
                    <div class="flex border-b border-gray-200 dark:border-gray-600 mb-3">
                        <button type="button" @click="aLang='en'"
                            :class="aLang==='en' ? 'text-brand-500 border-b-2 border-brand-500' : 'text-gray-500 dark:text-gray-400 border-b-2 border-transparent'"
                            class="px-4 pb-2 text-sm font-medium transition-colors -mb-px">English</button>
                        <button type="button" @click="aLang='ne'"
                            :class="aLang==='ne' ? 'text-brand-500 border-b-2 border-brand-500' : 'text-gray-500 dark:text-gray-400 border-b-2 border-transparent'"
                            class="px-4 pb-2 text-sm font-medium transition-colors -mb-px">Nepali</button>
                    </div>
                    <div x-show="aLang==='en'">
                        <input type="text" name="name" x-model="aName" required placeholder="Badge name"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div x-show="aLang==='ne'" x-cloak>
                        <input type="text" name="name_ne" placeholder="नाम (ऐच्छिक)"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                {{-- Color --}}
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-2">Color <span class="text-red-400">*</span></label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="color" x-model="aColor" maxlength="20"
                            class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <label class="relative w-10 h-9 cursor-pointer">
                            <input type="color" x-model="aColor" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
                            <span class="block w-10 h-9 rounded-lg border border-gray-200 dark:border-gray-600" :style="'background:'+aColor"></span>
                        </label>
                    </div>
                </div>

                {{-- Icon grid --}}
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-2">Icon</label>
                    <input type="hidden" name="icon" x-model="aIcon">
                    <div class="grid grid-cols-5 gap-2">
                        @foreach($icons as $key => $svg)
                        <button type="button"
                            :class="aIcon === '{{ $key }}' ? 'selected' : ''"
                            @click="aIcon = '{{ $key }}'"
                            class="icon-btn"
                            title="{{ $key === 'none' ? 'No icon' : ucfirst($key) }}">
                            @if($key === 'none')
                            <span class="text-[10px] font-bold text-gray-400">NONE</span>
                            @else
                            {!! $svg !!}
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Preview --}}
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-2">Preview</label>
                    <span class="badge-pill" :style="'background:'+aColor">
                        <svg x-show="aIcon !== 'none'" class="w-3.5 h-3.5 fill-white flex-shrink-0" viewBox="0 0 24 24" x-html="iconPath(aIcon)"></svg>
                        <span x-text="aName || 'Badge'"></span>
                    </span>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="showAdd = false"
                        class="flex-1 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Close
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                        Add Badge
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- ═══ EDIT MODAL ═══ --}}
    <template x-teleport="body">
    <div x-show="showEdit" x-cloak
        class="fixed inset-0 z-[999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        x-transition:enter="transition duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        @click="showEdit = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-[440px] max-h-[90vh] overflow-y-auto"
            @click.stop
            x-transition:enter="transition duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800">
                <h3 class="font-bold text-gray-900 dark:text-white">Edit Badge</h3>
                <button @click="showEdit = false" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'/admin/badges/'+eId" method="POST" class="px-6 py-5 space-y-5">
                @csrf @method('PUT')

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-2">Name <span class="text-red-400">*</span></label>
                    <div class="flex border-b border-gray-200 dark:border-gray-600 mb-3">
                        <button type="button" @click="eLang='en'"
                            :class="eLang==='en' ? 'text-brand-500 border-b-2 border-brand-500' : 'text-gray-500 dark:text-gray-400 border-b-2 border-transparent'"
                            class="px-4 pb-2 text-sm font-medium transition-colors -mb-px">English</button>
                        <button type="button" @click="eLang='ne'"
                            :class="eLang==='ne' ? 'text-brand-500 border-b-2 border-brand-500' : 'text-gray-500 dark:text-gray-400 border-b-2 border-transparent'"
                            class="px-4 pb-2 text-sm font-medium transition-colors -mb-px">Nepali</button>
                    </div>
                    <div x-show="eLang==='en'">
                        <input type="text" name="name" x-model="eName" required
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div x-show="eLang==='ne'" x-cloak>
                        <input type="text" name="name_ne" x-model="eNameNe" placeholder="नाम (ऐच्छिक)"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-2">Color <span class="text-red-400">*</span></label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="color" x-model="eColor" maxlength="20"
                            class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <label class="relative w-10 h-9 cursor-pointer">
                            <input type="color" x-model="eColor" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
                            <span class="block w-10 h-9 rounded-lg border border-gray-200 dark:border-gray-600" :style="'background:'+eColor"></span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-2">Icon</label>
                    <input type="hidden" name="icon" x-model="eIcon">
                    <div class="grid grid-cols-5 gap-2">
                        @foreach($icons as $key => $svg)
                        <button type="button"
                            :class="eIcon === '{{ $key }}' ? 'selected' : ''"
                            @click="eIcon = '{{ $key }}'"
                            class="icon-btn"
                            title="{{ $key === 'none' ? 'No icon' : ucfirst($key) }}">
                            @if($key === 'none')
                            <span class="text-[10px] font-bold text-gray-400">NONE</span>
                            @else
                            {!! $svg !!}
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-2">Preview</label>
                    <span class="badge-pill" :style="'background:'+eColor">
                        <svg x-show="eIcon !== 'none'" class="w-3.5 h-3.5 fill-white flex-shrink-0" viewBox="0 0 24 24" x-html="iconPath(eIcon)"></svg>
                        <span x-text="eName || 'Badge'"></span>
                    </span>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="showEdit = false"
                        class="flex-1 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

</div>

@push('scripts')
<script>
const ICON_PATHS = @json(collect($icons)->map(function($svg) {
    // Extract the inner path/shape content from the SVG string
    preg_match('/<svg[^>]*>(.*?)<\/svg>/s', $svg, $m);
    return $m[1] ?? '';
})->toArray());

function badgesApp() {
    return {
        showAdd: false,
        showEdit: false,
        openId: null,
        menuPos: '',
        // add form
        aLang: 'en', aName: '', aColor: '#6366f1', aIcon: 'none',
        // edit form
        eLang: 'en', eId: null, eName: '', eNameNe: '', eColor: '#6366f1', eIcon: 'none',

        openAddModal() {
            this.aLang = 'en'; this.aName = ''; this.aColor = '#6366f1'; this.aIcon = 'none';
            this.showAdd = true;
        },
        openEditModal(id, name, nameNe, color, icon) {
            this.eId = id; this.eName = name; this.eNameNe = nameNe;
            this.eColor = color; this.eIcon = icon || 'none';
            this.eLang = 'en'; this.openId = null; this.showEdit = true;
        },
        toggleMenu(event, id) {
            if (this.openId === id) { this.openId = null; return; }
            const r = event.currentTarget.getBoundingClientRect();
            this.menuPos = `top:${r.bottom + 4}px;right:${window.innerWidth - r.right}px`;
            this.openId = id;
        },
        closeMenus() { this.openId = null; },
        iconPath(key) { return ICON_PATHS[key] || ''; },
    };
}
</script>
@endpush

@endsection
