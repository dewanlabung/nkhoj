@extends('layouts.admin')
@section('title', 'Navigation')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Navigation</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
        <a href="/admin" class="hover:text-brand-500">Home</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">›</span>
        Navigation
    </p>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm rounded-xl border border-green-100 dark:border-green-800/40">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm rounded-xl border border-red-100 dark:border-red-800/40">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ addType: 'custom' }">

    {{-- Left panel --}}
    <div class="lg:col-span-1 space-y-4">

        {{-- Settings --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">Settings</h3>
            <form method="POST" action="/admin/navigation/settings" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Home Page Link</label>
                    <select name="home_page_link" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="show" {{ ($settings['nav_home_page_link'] ?? 'show') === 'show' ? 'selected' : '' }}>Show</option>
                        <option value="hide" {{ ($settings['nav_home_page_link'] ?? 'show') === 'hide' ? 'selected' : '' }}>Hide</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Show or hide the Home link in the navigation menu.</p>
                </div>
                <button class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    Save Changes
                </button>
            </form>
        </div>

        {{-- Add Menu Link --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">Add Menu Link</h3>
            <form method="POST" action="/admin/navigation" class="space-y-3">
                @csrf
                <input type="hidden" name="language" value="{{ $lang }}">

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Label *</label>
                    <input type="text" name="label" required placeholder="e.g. About Us"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Type *</label>
                    <select name="type" x-model="addType"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="custom">Custom URL</option>
                        <option value="home">Home Page</option>
                        <option value="category">Category</option>
                        <option value="page">Page</option>
                        <option value="tag">Tag</option>
                    </select>
                </div>

                <div x-show="addType === 'custom'" x-cloak>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">URL</label>
                    <input type="text" name="url" placeholder="https://... or /path"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div x-show="addType === 'category'" x-cloak>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Category</label>
                    <select name="target_id" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">— select —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name_en }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="addType === 'page'" x-cloak>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Page</label>
                    <select name="target_id" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">— select —</option>
                        @foreach($pages as $page)
                        <option value="{{ $page->id }}">{{ $page->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="addType === 'tag'" x-cloak>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Tag</label>
                    <select name="target_id" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">— select —</option>
                        @foreach($tags as $tag)
                        <option value="{{ $tag->id }}">#{{ $tag->name_en }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Parent Link</label>
                    <select name="parent_id" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">None (top level)</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Open In</label>
                    <select name="open_in" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="_self">Same Tab</option>
                        <option value="_blank">New Tab</option>
                    </select>
                </div>

                <button class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    + Add Menu Link
                </button>
            </form>
        </div>

        {{-- Quick Add: Categories --}}
        @if($categories->count())
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-3">Quick Add — Categories</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($categories as $cat)
                @php $alreadyInNav = $items->where('type','category')->where('target_id',$cat->id)->count() > 0; @endphp
                @if(!$alreadyInNav)
                <form method="POST" action="/admin/navigation/quick-add">
                    @csrf
                    <input type="hidden" name="type" value="category">
                    <input type="hidden" name="target_id" value="{{ $cat->id }}">
                    <input type="hidden" name="language" value="{{ $lang }}">
                    <button class="px-3 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20 dark:hover:text-brand-400 rounded-full border border-gray-200 dark:border-gray-600 transition-colors">
                        + {{ $cat->name_en }}
                    </button>
                </form>
                @else
                <span class="px-3 py-1 text-xs font-medium bg-brand-50 dark:bg-brand-900/20 text-brand-500 rounded-full border border-brand-100 dark:border-brand-800/40">✓ {{ $cat->name_en }}</span>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Quick Add: Pages --}}
        @if($pages->count())
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-3">Quick Add — Pages</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($pages as $page)
                @php $alreadyInNav = $items->where('type','page')->where('target_id',$page->id)->count() > 0; @endphp
                @if(!$alreadyInNav)
                <form method="POST" action="/admin/navigation/quick-add">
                    @csrf
                    <input type="hidden" name="type" value="page">
                    <input type="hidden" name="target_id" value="{{ $page->id }}">
                    <input type="hidden" name="language" value="{{ $lang }}">
                    <button class="px-3 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20 dark:hover:text-brand-400 rounded-full border border-gray-200 dark:border-gray-600 transition-colors">
                        + {{ $page->title }}
                    </button>
                </form>
                @else
                <span class="px-3 py-1 text-xs font-medium bg-brand-50 dark:bg-brand-900/20 text-brand-500 rounded-full border border-brand-100 dark:border-brand-800/40">✓ {{ $page->title }}</span>
                @endif
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- Right: Navigation list --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 dark:border-gray-700/60">
                <h3 class="font-bold text-gray-900 dark:text-white text-sm">Navigation</h3>
                <select onchange="window.location='/admin/navigation?lang='+this.value"
                    class="text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2.5 py-1.5 focus:outline-none">
                    <option value="en" {{ $lang === 'en' ? 'selected' : '' }}>English</option>
                    <option value="ne" {{ $lang === 'ne' ? 'selected' : '' }}>Nepali</option>
                    <option value="ar" {{ $lang === 'ar' ? 'selected' : '' }}>Arabic</option>
                </select>
            </div>

            {{-- Draggable list --}}
            <div id="nav-list" class="divide-y divide-gray-50 dark:divide-gray-700/40 min-h-[80px]">
                @forelse($items as $item)
                <div class="nav-item group px-5 py-3 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors" data-id="{{ $item->id }}">

                    {{-- Drag handle --}}
                    <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-300 dark:text-gray-600 hover:text-gray-400 flex-shrink-0">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="19" r="1.5"/><circle cx="15" cy="19" r="1.5"/></svg>
                    </div>

                    {{-- Label + type badge --}}
                    <div class="flex-1 min-w-0 flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $item->label }}</span>
                        @php
                            $badgeColor = match($item->type) {
                                'category' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
                                'page'     => 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400',
                                'tag'      => 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400',
                                'home'     => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
                                default    => 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400',
                            };
                        @endphp
                        <span class="flex-shrink-0 text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $badgeColor }}">{{ ucfirst($item->type) }}</span>
                        @if($item->type === 'custom' && $item->url)
                        <span class="text-xs text-gray-400 truncate hidden group-hover:block max-w-[120px]">{{ $item->url }}</span>
                        @endif
                    </div>

                    {{-- Children indicator --}}
                    @if($item->children->count())
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $item->children->count() }} sub</span>
                    @endif

                    {{-- Active toggle --}}
                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                        <input type="checkbox" {{ $item->is_active ? 'checked' : '' }}
                            onchange="toggleNav({{ $item->id }}, this.checked)"
                            class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-brand-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                    </label>

                    {{-- Edit --}}
                    <button onclick="openEdit({{ $item->id }}, @js($item->label), @js($item->type), @js($item->url ?? ''), @js($item->target_id), @js($item->open_in ?? '_self'))"
                        class="flex-shrink-0 w-7 h-7 flex items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>

                    {{-- Delete --}}
                    <form method="POST" action="/admin/navigation/{{ $item->id }}" onsubmit="return confirm('Delete this item and its sub-items?')">
                        @csrf @method('DELETE')
                        <button class="flex-shrink-0 w-7 h-7 flex items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/30 text-red-500 dark:text-red-400 hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>

                {{-- Sub-items --}}
                @foreach($item->children as $child)
                <div class="flex items-center gap-3 px-5 py-2.5 bg-gray-50/60 dark:bg-gray-700/20 pl-14">
                    <svg class="w-3 h-3 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-sm text-gray-700 dark:text-gray-300 flex-1 truncate">{{ $child->label }}</span>
                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-500">{{ ucfirst($child->type) }}</span>
                    <form method="POST" action="/admin/navigation/{{ $child->id }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button class="w-6 h-6 flex items-center justify-center rounded bg-red-50 dark:bg-red-900/20 text-red-400 hover:bg-red-100 transition-colors">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </form>
                </div>
                @endforeach

                @empty
                <div class="px-5 py-12 text-center text-gray-400 dark:text-gray-500 text-sm">
                    No navigation items yet. Add one using the form on the left or use Quick Add above.
                </div>
                @endforelse
            </div>
        </div>

        {{-- Info panel --}}
        <div class="flex items-start gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/40 rounded-xl text-sm text-blue-700 dark:text-blue-400">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>You can manage the navigation by <strong>dragging</strong> and <strong>dropping</strong> menu items. Changes are saved automatically.</span>
        </div>

        <div class="flex items-start gap-3 px-4 py-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-100 dark:border-yellow-800/40 rounded-xl text-sm text-yellow-700 dark:text-yellow-400">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Items with <strong>inactive</strong> toggle will not appear on the public website. Sub-items are only shown when the parent is active.</span>
        </div>
    </div>
</div>

{{-- Edit modal --}}
<div id="edit-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden" x-data="{ editType: 'custom' }">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-gray-900 dark:text-white">Edit Menu Item</h3>
            <button onclick="closeEdit()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="edit-form" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Label</label>
                <input type="text" id="edit-label" name="label" required
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Type</label>
                <select id="edit-type" name="type"
                    onchange="document.getElementById('edit-url-wrap').style.display = this.value==='custom' ? '' : 'none'"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="custom">Custom URL</option>
                    <option value="home">Home Page</option>
                    <option value="category">Category</option>
                    <option value="page">Page</option>
                    <option value="tag">Tag</option>
                </select>
            </div>
            <div id="edit-url-wrap">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">URL</label>
                <input type="text" id="edit-url" name="url" placeholder="https://... or /path"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Open In</label>
                <select id="edit-openin" name="open_in"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="_self">Same Tab</option>
                    <option value="_blank">New Tab</option>
                </select>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="closeEdit()" class="flex-1 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEdit(id, label, type, url, targetId, openIn) {
    const form = document.getElementById('edit-form');
    form.action = '/admin/navigation/' + id;
    document.getElementById('edit-label').value = label;
    document.getElementById('edit-url').value = url;
    document.getElementById('edit-openin').value = openIn;
    const typeEl = document.getElementById('edit-type');
    typeEl.value = type;
    document.getElementById('edit-url-wrap').style.display = type === 'custom' ? '' : 'none';
    document.getElementById('edit-modal').classList.remove('hidden');
}
function closeEdit() {
    document.getElementById('edit-modal').classList.add('hidden');
}

async function toggleNav(id, active) {
    try {
        await fetch('/admin/navigation/' + id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ is_active: active ? 1 : 0 })
        });
    } catch(e) { console.error(e); }
}

// Drag-and-drop reorder
const navList = document.getElementById('nav-list');
let dragging = null;
document.querySelectorAll('.drag-handle').forEach(handle => {
    const row = handle.closest('.nav-item');
    row.setAttribute('draggable', 'true');
    row.addEventListener('dragstart', e => {
        dragging = e.currentTarget;
        setTimeout(() => e.currentTarget.style.opacity = '0.4', 0);
    });
    row.addEventListener('dragend', e => {
        e.currentTarget.style.opacity = '';
        const order = [...navList.querySelectorAll('.nav-item')].map(el => el.dataset.id);
        fetch('/admin/navigation/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ order })
        });
    });
});
navList.addEventListener('dragover', e => {
    e.preventDefault();
    const after = getDragAfter(navList, e.clientY);
    if (dragging) {
        if (after === null) navList.appendChild(dragging);
        else navList.insertBefore(dragging, after);
    }
});
function getDragAfter(container, y) {
    const els = [...container.querySelectorAll('.nav-item:not([style*="opacity"])')];
    return els.reduce((closest, el) => {
        const box = el.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) return { offset, element: el };
        return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element ?? null;
}
</script>
@endpush

@endsection
