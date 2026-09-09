@extends('layouts.admin')
@section('title', 'Tags')

@section('content')
<div x-data="{
    perPage: 20,
    search: '',
    selected: [],
    showAdd: false,
    editId: null, editNameEn: '', editNameNe: '',
    allTags: {{ $tags->map(fn($t) => ['id'=>$t->id,'name_en'=>$t->name_en,'name_ne'=>$t->name_ne??'','slug'=>$t->slug,'count'=>$t->posts_count,'lang'=>($t->name_ne?'English / Nepali':'English')])->toJson() }},
    get filtered() {
        const q = this.search.toLowerCase();
        return q ? this.allTags.filter(t => t.name_en.toLowerCase().includes(q) || t.slug.includes(q)) : this.allTags;
    },
    get paginated() { return this.filtered.slice(0, this.perPage); },
    toggleAll(e) { this.selected = e.target.checked ? this.paginated.map(t=>t.id) : []; },
    openEdit(tag) { this.editId=tag.id; this.editNameEn=tag.name_en; this.editNameNe=tag.name_ne; }
}" class="space-y-4">

    {{-- Breadcrumb + title --}}
    <div>
        <nav class="text-xs text-gray-400 mb-1"><a href="/admin" class="hover:text-brand-600">Home</a> <span class="mx-1">›</span> Tags</nav>
        <h2 class="text-2xl font-bold text-gray-900">Tags</h2>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Controls bar --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100">
            {{-- Per page --}}
            <select x-model.number="perPage" class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-gray-600 focus:ring-2 focus:ring-brand-300 outline-none">
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>

            {{-- Search --}}
            <div class="relative flex-1 max-w-xs">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="search" placeholder="Filter tags…"
                       class="pl-8 pr-3 py-1.5 border border-gray-200 rounded-lg text-sm w-full focus:ring-2 focus:ring-brand-300 focus:border-brand-400 outline-none">
            </div>

            <div class="flex-1"></div>

            {{-- Add tag button --}}
            <button @click="showAdd=!showAdd"
                    class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add Tag
            </button>
        </div>

        {{-- Add form (collapsible) --}}
        <div x-show="showAdd" x-collapse class="px-4 py-4 bg-blue-50/50 border-b border-blue-100">
            <form method="POST" action="/admin/tags" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Name (English) <span class="text-red-400">*</span></label>
                    <input type="text" name="name_en" required placeholder="e.g. Technology"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-300 outline-none bg-white">
                </div>
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Name (Nepali)</label>
                    <input type="text" name="name_ne" placeholder="e.g. प्रविधि"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-300 outline-none bg-white">
                </div>
                <div class="w-40">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Slug <span class="text-gray-400">(optional)</span></label>
                    <input type="text" name="slug" placeholder="auto-generated"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-300 outline-none bg-white">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">Save</button>
                    <button type="button" @click="showAdd=false" class="px-4 py-2 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" @change="toggleAll($event)" class="rounded border-gray-300">
                    </th>
                    <th class="px-4 py-3 text-left w-16">ID</th>
                    <th class="px-4 py-3 text-left">Tag</th>
                    <th class="px-4 py-3 text-left">Language</th>
                    <th class="px-4 py-3 text-left">Number of Posts</th>
                    <th class="px-4 py-3 text-right">Options</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="tag in paginated" :key="tag.id">
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <input type="checkbox" :value="tag.id" x-model="selected" class="rounded border-gray-300">
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs" x-text="tag.id"></td>
                        <td class="px-4 py-3">
                            <a :href="`/tag/${tag.slug}`" target="_blank"
                               class="font-semibold text-blue-600 hover:text-blue-800 hover:underline" x-text="tag.name_en"></a>
                            <span x-show="tag.name_ne" class="ml-1 text-xs text-gray-400" x-text="tag.name_ne ? '/ '+tag.name_ne : ''"></span>
                        </td>
                        <td class="px-4 py-3 text-gray-500" x-text="tag.lang"></td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                  :class="tag.count > 0 ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-400'"
                                  x-text="tag.count"></span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEdit(tag)"
                                        class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200 hover:text-blue-600 text-gray-600 font-medium transition-colors">
                                    Edit
                                </button>
                                <form :action="`/admin/tags/${tag.id}`" method="POST"
                                      @submit.prevent="if(confirm('Delete tag: '+tag.name_en+'?')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-red-50 hover:border-red-200 hover:text-red-600 text-gray-600 font-medium transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="filtered.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">No tags found.</td>
                </tr>
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-4 py-3 text-xs text-gray-400 border-t border-gray-100">
            <span>Showing <span class="font-semibold text-gray-600" x-text="Math.min(perPage, filtered.length)"></span>
                of <span class="font-semibold text-gray-600" x-text="filtered.length"></span> tags</span>
            <span x-show="selected.length > 0" class="text-red-500 font-medium" x-text="`${selected.length} selected`"></span>
        </div>
    </div>

    {{-- Edit modal (inside x-data so Alpine vars are in scope) --}}
    <div x-show="editId !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
         @keydown.escape.window="editId=null">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4" @click.stop>
            <h3 class="font-bold text-gray-900 mb-4 text-lg">Edit Tag</h3>
            <form method="POST" :action="`/admin/tags/${editId}`">
                @csrf @method('PATCH')
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Name (English)</label>
                        <input type="text" name="name_en" x-model="editNameEn" required
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Name (Nepali)</label>
                        <input type="text" name="name_ne" x-model="editNameNe"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                    </div>
                </div>
                <div class="flex gap-2 justify-end mt-5">
                    <button type="button" @click="editId=null"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                            class="px-5 py-2 text-sm bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>{{-- end x-data --}}
@endsection
