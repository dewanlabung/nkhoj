@extends('layouts.admin')
@section('title', 'Categories')

@section('content')
@php
$topLevel   = $categories->whereNull('parent_id')->sortBy('sort_order');
$byParent   = $categories->whereNotNull('parent_id')->groupBy('parent_id');
@endphp

<div x-data="categoryManager()" x-init="init()">

    {{-- ═══ TOP BAR ════════════════════════════════════════════ --}}
    <div class="flex items-center gap-3 mb-5">
        <div class="relative">
            <select class="appearance-none text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg pl-3 pr-8 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                <option>English</option>
                <option>नेपाली</option>
            </select>
            <svg class="absolute right-2.5 top-2.5 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>

        <div class="relative flex-1 max-w-xs">
            <input type="text" x-model="search" placeholder="Search" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg pl-9 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <span x-show="saved" x-cloak class="text-xs text-green-600 font-medium flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Order saved
            </span>
            <button @click="showAdd = true"
                class="flex items-center gap-1.5 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Category
            </button>
        </div>
    </div>

    {{-- ═══ CATEGORY LIST ═══════════════════════════════════════ --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <ul id="category-sortable" class="divide-y divide-gray-50 dark:divide-gray-700/60">
            @foreach($topLevel as $cat)
            @php $children = $byParent[$cat->id] ?? collect(); @endphp
            <li class="category-row"
                data-id="{{ $cat->id }}"
                x-show="!search || '{{ strtolower($cat->name_en) }}'.includes(search.toLowerCase()) || '{{ strtolower($cat->name_ne ?? '') }}'.includes(search.toLowerCase())"
                x-data="{ open: false }">

                {{-- Main row --}}
                <div class="flex items-center gap-3 px-4 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors group">

                    {{-- Drag handle --}}
                    <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-300 dark:text-gray-600 hover:text-gray-400 select-none flex-shrink-0 touch-none">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <circle cx="7" cy="5" r="1.5"/><circle cx="13" cy="5" r="1.5"/>
                            <circle cx="7" cy="10" r="1.5"/><circle cx="13" cy="10" r="1.5"/>
                            <circle cx="7" cy="15" r="1.5"/><circle cx="13" cy="15" r="1.5"/>
                        </svg>
                    </div>

                    {{-- Expand toggle --}}
                    <button @click="open = !open"
                        class="w-6 h-6 flex items-center justify-center rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-transform flex-shrink-0"
                        :class="{ 'rotate-90 text-brand-500': open }"
                        @if($children->isEmpty()) style="visibility:hidden" @endif>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    {{-- Name --}}
                    <div class="flex-1 min-w-0">
                        <span class="font-semibold text-gray-800 dark:text-white text-sm">{{ $cat->name_en }}</span>
                        @if($cat->name_ne)
                        <span class="ml-1.5 text-gray-400 text-xs font-nepali">{{ $cat->name_ne }}</span>
                        @endif
                        <span class="ml-1.5 text-gray-400 text-xs">(ID: {{ $cat->id }})</span>
                    </div>

                    {{-- Badges --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if($cat->is_exclusive)
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-600 text-white">Exclusive</span>
                        @endif
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border {{ $cat->is_active ? 'border-green-300 text-green-600 bg-green-50 dark:bg-green-900/20 dark:border-green-700 dark:text-green-400' : 'border-gray-200 text-gray-400 bg-gray-50 dark:bg-gray-700 dark:border-gray-600' }}">
                            {{ $cat->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="text-xs text-gray-400 hidden sm:block">{{ $cat->posts_count }} posts</span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-1 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="openEdit({{ $cat->id }}, {{ json_encode(['name_en'=>$cat->name_en,'name_ne'=>$cat->name_ne,'slug'=>$cat->slug,'sort_order'=>$cat->sort_order,'is_active'=>$cat->is_active,'is_exclusive'=>$cat->is_exclusive,'color'=>$cat->color,'meta_title'=>$cat->meta_title]) }})"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form method="POST" action="/admin/categories/{{ $cat->id }}" onsubmit="return confirm('Delete \'{{ addslashes($cat->name_en) }}\'?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/20 text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 hover:text-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Subcategories --}}
                @if($children->isNotEmpty())
                <div x-show="open" x-collapse class="bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700/50">
                    <ul id="children-sortable-{{ $cat->id }}" class="divide-y divide-gray-100 dark:divide-gray-700/30">
                        @foreach($children->sortBy('sort_order') as $child)
                        <li class="category-row flex items-center gap-3 px-4 pl-12 py-3 hover:bg-white dark:hover:bg-gray-700/40 transition-colors group" data-id="{{ $child->id }}">
                            <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-300 dark:text-gray-600 hover:text-gray-400 select-none touch-none">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <circle cx="7" cy="5" r="1.5"/><circle cx="13" cy="5" r="1.5"/>
                                    <circle cx="7" cy="10" r="1.5"/><circle cx="13" cy="10" r="1.5"/>
                                    <circle cx="7" cy="15" r="1.5"/><circle cx="13" cy="15" r="1.5"/>
                                </svg>
                            </div>
                            <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7"/></svg>
                            <div class="flex-1 min-w-0">
                                <span class="text-sm text-gray-700 dark:text-gray-200 font-medium">{{ $child->name_en }}</span>
                                @if($child->name_ne)<span class="ml-1.5 text-gray-400 text-xs font-nepali">{{ $child->name_ne }}</span>@endif
                                <span class="ml-1.5 text-gray-400 text-xs">(ID: {{ $child->id }})</span>
                            </div>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full border {{ $child->is_active ? 'border-green-300 text-green-600 bg-green-50' : 'border-gray-200 text-gray-400 bg-gray-50' }}">
                                {{ $child->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="openEdit({{ $child->id }}, {{ json_encode(['name_en'=>$child->name_en,'name_ne'=>$child->name_ne,'slug'=>$child->slug,'sort_order'=>$child->sort_order,'is_active'=>$child->is_active,'is_exclusive'=>$child->is_exclusive,'color'=>$child->color,'meta_title'=>$child->meta_title]) }})"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-500 hover:bg-blue-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="/admin/categories/{{ $child->id }}" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-lg bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </li>
            @endforeach
        </ul>

        @if($topLevel->isEmpty())
        <div class="text-center py-12 text-gray-400">
            <p class="text-sm">No categories yet. Click "+ Add Category" to create one.</p>
        </div>
        @endif
    </div>

    {{-- ═══ ADD MODAL ════════════════════════════════════════════ --}}
    <div x-show="showAdd" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @keydown.escape.window="showAdd = false">
        <div @click.outside="showAdd = false"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Add Category</h3>
                <button @click="showAdd = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="/admin/categories" class="space-y-3.5">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Name (English) *</label>
                        <input type="text" name="name_en" required class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Name (Nepali)</label>
                        <input type="text" name="name_ne" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Slug <span class="font-normal text-gray-400">(auto)</span></label>
                        <input type="text" name="slug" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Parent Category</label>
                        <select name="parent_id" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="">— None (top level) —</option>
                            @foreach($topLevel as $p)
                            <option value="{{ $p->id }}">{{ $p->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded accent-brand-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">Active</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_exclusive" value="0">
                        <input type="checkbox" name="is_exclusive" value="1" class="w-4 h-4 rounded accent-brand-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">Exclusive</span>
                    </label>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="flex-1 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                        Add Category
                    </button>
                    <button type="button" @click="showAdd = false" class="flex-1 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-sm font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ EDIT MODAL ═══════════════════════════════════════════ --}}
    <div x-show="editId" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @keydown.escape.window="editId = null">
        <div @click.outside="editId = null"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Category</h3>
                <button @click="editId = null" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" :action="'/admin/categories/' + editId" class="space-y-3.5">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Name (English) *</label>
                        <input type="text" name="name_en" :value="editData.name_en" required class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Name (Nepali)</label>
                        <input type="text" name="name_ne" :value="editData.name_ne" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Meta Title</label>
                    <input type="text" name="meta_title" :value="editData.meta_title" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" :checked="editData.is_active" class="w-4 h-4 rounded accent-brand-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">Active</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_exclusive" value="0">
                        <input type="checkbox" name="is_exclusive" value="1" :checked="editData.is_exclusive" class="w-4 h-4 rounded accent-brand-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">Exclusive</span>
                    </label>
                    <div class="flex items-center gap-1.5 ml-auto">
                        <label class="text-xs text-gray-500">Color</label>
                        <input type="color" name="color" :value="editData.color || '#3b82f6'" class="w-7 h-7 rounded border border-gray-200 cursor-pointer p-0.5">
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="flex-1 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                        Save Changes
                    </button>
                    <button type="button" @click="editId = null" class="flex-1 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-sm font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SortableJS via CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>

<script>
function categoryManager() {
    return {
        search: '',
        showAdd: false,
        editId: null,
        editData: {},
        saved: false,
        _saveTimer: null,

        init() {
            this.$nextTick(() => this.initSortable());
        },

        initSortable() {
            const el = document.getElementById('category-sortable');
            if (!el) return;

            Sortable.create(el, {
                animation: 180,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: () => this.saveOrder(el, '/admin/categories/reorder'),
            });

            // Init child sortables
            document.querySelectorAll('[id^="children-sortable-"]').forEach(childEl => {
                Sortable.create(childEl, {
                    animation: 180,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    onEnd: () => this.saveOrder(childEl, '/admin/categories/reorder'),
                });
            });
        },

        saveOrder(el, url) {
            const ids = Array.from(el.querySelectorAll(':scope > .category-row, :scope > li.category-row'))
                .map(li => li.dataset.id)
                .filter(Boolean);

            clearTimeout(this._saveTimer);
            this._saveTimer = setTimeout(() => {
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ order: ids }),
                })
                .then(r => r.json())
                .then(() => {
                    this.saved = true;
                    setTimeout(() => this.saved = false, 3000);
                })
                .catch(console.error);
            }, 400);
        },

        openEdit(id, data) {
            this.editId = id;
            this.editData = data;
        },
    };
}
</script>

<style>
.sortable-ghost {
    opacity: 0.4;
    background: #eff6ff !important;
    border: 2px dashed #93c5fd !important;
    border-radius: 8px;
}
.sortable-chosen {
    box-shadow: 0 8px 25px -5px rgba(0,0,0,0.15);
    border-radius: 8px;
}
.sortable-drag {
    opacity: 0;
}
</style>
@endsection
