@extends('layouts.admin')
@section('title', 'Language Settings')

@push('head')
<style>
.opt-menu { position:fixed; min-width:128px; background:#1f2937; border:1px solid rgba(255,255,255,.1); border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.4); z-index:9999; overflow:hidden; }
html:not(.dark) .opt-menu { background:#fff; border-color:#e5e7eb; box-shadow:0 4px 16px rgba(0,0,0,.12); }
.opt-menu a,.opt-menu button { display:flex; align-items:center; gap:8px; width:100%; padding:9px 14px; font-size:13px; color:#d1d5db; text-decoration:none; background:none; border:none; cursor:pointer; text-align:left; transition:background .12s; }
html:not(.dark) .opt-menu a, html:not(.dark) .opt-menu button { color:#374151; }
.opt-menu button:hover,.opt-menu a:hover { background:rgba(255,255,255,.07); }
html:not(.dark) .opt-menu button:hover { background:#f3f4f6; }
.opt-menu .del { color:#f87171; }
</style>
@endpush

@section('content')
@php
$presets = \App\Http\Controllers\LanguageController::presets();
$existing = \App\Models\Language::pluck('short_form')->toArray();
@endphp

<div x-data="langApp()" @click="closeAll()">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Language Settings</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
            <a href="/admin" class="hover:text-brand-500">Home</a>
            <span class="mx-1.5 text-gray-300 dark:text-gray-600">›</span>
            Language Settings
        </p>
    </div>

    @if(session('success'))
    <div class="mb-5 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm rounded-xl border border-green-100 dark:border-green-800/40">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-5 px-4 py-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm rounded-xl border border-red-100 dark:border-red-800/40">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        {{-- Settings panel --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">Settings</h3>
                <form method="POST" action="/admin/languages/default" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Default Language <span class="text-red-400">*</span></label>
                        <select name="default_language"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            @foreach($languages as $lang)
                            <option value="{{ $lang->short_form }}" {{ ($settings['default_language'] ?? 'en') === $lang->short_form ? 'selected' : '' }}>
                                {{ $lang->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>

        {{-- Languages table --}}
        <div class="lg:col-span-3">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $languages->total() }} Languages</span>
                    <div class="flex items-center gap-2">
                        <button @click.stop="showImport = true"
                            class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-purple-700 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/30 hover:bg-purple-100 dark:hover:bg-purple-900/50 rounded-lg transition-colors border border-purple-200 dark:border-purple-800/40">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Import Language
                        </button>
                        <button @click.stop="openAdd()"
                            class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-brand-500 hover:bg-brand-600 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            Add Language
                        </button>
                    </div>
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 w-10">ID</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-5 py-3">Language Name</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 w-24">Short Form</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 w-24">Language Code</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 w-20">Status</th>
                            <th class="text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 w-24">Options</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                        @forelse($languages as $lang)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="px-5 py-3.5 text-sm text-gray-400">{{ $lang->id }}</td>
                            <td class="px-5 py-3.5">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $lang->name }}</span>
                                <span class="ml-1.5 text-xs text-gray-400">{{ $lang->direction === 'rtl' ? '← RTL' : 'LTR →' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-gray-600 dark:text-gray-300 font-mono">{{ $lang->short_form }}</td>
                            <td class="px-5 py-3.5 text-sm text-gray-600 dark:text-gray-300 font-mono">{{ $lang->code }}</td>
                            <td class="px-5 py-3.5">
                                @if($lang->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Active</span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right" @click.stop>
                                <button @click.stop="toggleMenu($event, {{ $lang->id }})"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                    Select
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="opt-menu" x-show="openId === {{ $lang->id }}" x-cloak :style="menuPos" @click.stop>
                                    <button @click="openEdit({{ $lang->id }}, '{{ addslashes($lang->name) }}', '{{ $lang->short_form }}', '{{ $lang->code }}', '{{ $lang->editor_language }}', '{{ $lang->direction }}', {{ $lang->sort_order }}, {{ $lang->is_active ? 'true' : 'false' }})">
                                        <svg class="w-3.5 h-3.5 fill-blue-400" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 000-1.41l-2.34-2.34a1 1 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                        Edit
                                    </button>
                                    <form method="POST" action="/admin/languages/{{ $lang->id }}" onsubmit="return confirm('Delete this language?')">
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
                            <td colspan="6" class="px-6 py-16 text-center text-sm text-gray-400 dark:text-gray-500">
                                No languages yet. Import or add one.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══ IMPORT MODAL ═══ --}}
    <template x-teleport="body">
    <div x-show="showImport" x-cloak
        class="fixed inset-0 z-[999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        @click="showImport = false"
        x-transition:enter="transition duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md max-h-[80vh] overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800">
                <h3 class="font-bold text-gray-900 dark:text-white">Import Language</h3>
                <button @click="showImport = false" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Select a language preset to import:</p>
                <div class="space-y-2">
                    @foreach($presets as $preset)
                    @php $alreadyExists = in_array($preset['short_form'], $existing); @endphp
                    <div class="flex items-center justify-between p-3 rounded-xl {{ $alreadyExists ? 'bg-gray-50 dark:bg-gray-700/20 opacity-60' : 'bg-gray-50 dark:bg-gray-700/40 hover:bg-brand-50 dark:hover:bg-brand-900/20' }} transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 flex items-center justify-center bg-white dark:bg-gray-700 rounded-lg text-xs font-bold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 uppercase">{{ $preset['short_form'] }}</span>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $preset['name'] }}</p>
                                <p class="text-xs text-gray-400">{{ $preset['code'] }} · {{ strtoupper($preset['direction']) }}</p>
                            </div>
                        </div>
                        @if($alreadyExists)
                        <span class="text-xs text-green-500 font-medium">Installed</span>
                        @else
                        <form method="POST" action="/admin/languages/import">
                            @csrf
                            <input type="hidden" name="preset" value="{{ $preset['short_form'] }}">
                            <button type="submit"
                                class="text-xs font-semibold text-brand-500 hover:text-brand-600 border border-brand-300 dark:border-brand-700 hover:bg-brand-50 dark:hover:bg-brand-900/30 px-3 py-1.5 rounded-lg transition-colors">
                                Import
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- ═══ ADD MODAL ═══ --}}
    <template x-teleport="body">
    <div x-show="showAdd" x-cloak
        class="fixed inset-0 z-[999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        @click="showAdd = false"
        x-transition:enter="transition duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-bold text-gray-900 dark:text-white" x-text="editId ? 'Edit Language' : 'Add Language'"></h3>
                <button @click="showAdd = false" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form :action="editId ? '/admin/languages/'+editId : '/admin/languages'" method="POST" class="px-6 py-5 space-y-4">
                @csrf
                <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>

                {{-- Status --}}
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</span>
                    <label class="relative inline-flex items-center cursor-pointer gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" x-model="fActive">
                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        <span class="text-sm text-gray-400">Active</span>
                    </label>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Language Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" x-model="fName" required placeholder="Language Name"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Short Form <span class="text-red-400">*</span></label>
                    <input type="text" name="short_form" x-model="fShort" required placeholder="E.g en" maxlength="10"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Language Code <span class="text-red-400">*</span></label>
                    <input type="text" name="code" x-model="fCode" required placeholder="E.g en-US" maxlength="20"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Order <span class="text-red-400">*</span></label>
                    <input type="number" name="sort_order" x-model="fOrder" required min="1" value="1"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Text Editor Language <span class="text-red-400">*</span></label>
                    <select name="editor_language"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Select an option</option>
                        @foreach($presets as $p)
                        <option value="{{ $p['short_form'] }}" :selected="fEditorLang === '{{ $p['short_form'] }}'">{{ $p['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-2">Text Direction <span class="text-red-400">*</span></label>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="direction" value="ltr" x-model="fDir"
                                class="w-4 h-4 text-brand-500 border-gray-300 focus:ring-brand-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Left to Right</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="direction" value="rtl" x-model="fDir"
                                class="w-4 h-4 text-brand-500 border-gray-300 focus:ring-brand-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Right to Left</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showAdd = false"
                        class="flex-1 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Close
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors"
                        x-text="editId ? 'Save Changes' : 'Add Language'">
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

</div>

@push('scripts')
<script>
function langApp() {
    return {
        showAdd: false, showImport: false,
        openId: null, menuPos: '',
        editId: null,
        fName: '', fShort: '', fCode: '', fEditorLang: '', fDir: 'ltr', fOrder: 1, fActive: true,

        openAdd() { this.editId=null; this.fName=''; this.fShort=''; this.fCode=''; this.fEditorLang=''; this.fDir='ltr'; this.fOrder=1; this.fActive=true; this.showAdd=true; },
        openEdit(id, name, short, code, editor, dir, order, active) {
            this.editId=id; this.fName=name; this.fShort=short; this.fCode=code; this.fEditorLang=editor; this.fDir=dir; this.fOrder=order; this.fActive=active; this.openId=null; this.showAdd=true;
        },
        toggleMenu(e, id) {
            if (this.openId === id) { this.openId=null; return; }
            const r = e.currentTarget.getBoundingClientRect();
            this.menuPos = `top:${r.bottom+4}px;right:${window.innerWidth-r.right}px`;
            this.openId = id;
        },
        closeAll() { this.openId=null; }
    };
}
</script>
@endpush

@endsection
