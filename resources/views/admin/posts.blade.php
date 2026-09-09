@extends('layouts.admin')
@section('title', 'Posts')

@section('content')
<div x-data="{
    selected: [],
    bulkAction: '',
    bulkTagId: '',
    toggleAll(e) {
        const boxes = document.querySelectorAll('.post-checkbox');
        this.selected = e.target.checked ? [...boxes].map(b => parseInt(b.value)) : [];
    }
}">

{{-- Bulk action bar --}}
<div x-show="selected.length > 0" x-cloak
     class="mb-4 bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex flex-wrap items-center gap-3">
    <span class="text-sm font-semibold text-blue-700" x-text="`${selected.length} post(s) selected`"></span>
    <form method="POST" action="/admin/posts/bulk" class="flex flex-wrap items-center gap-2" id="bulk-form">
        @csrf
        <template x-for="id in selected" :key="id">
            <input type="hidden" name="ids[]" :value="id">
        </template>
        <select name="action" x-model="bulkAction"
                class="text-sm border border-blue-300 rounded-lg px-3 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">— choose action —</option>
            <option value="publish">Publish</option>
            <option value="draft">Set to Draft</option>
            <option value="archived">Archive</option>
            <option value="tag">Assign Tag</option>
            <option value="delete">Delete</option>
        </select>
        <div x-show="bulkAction === 'tag'" class="flex items-center gap-2">
            <select name="tag_id" x-model="bulkTagId"
                    class="text-sm border border-blue-300 rounded-lg px-3 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">— select tag —</option>
                @foreach($allTags as $tag)
                <option value="{{ $tag->id }}">{{ $tag->name_en }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                @click.prevent="
                    if (!bulkAction) return;
                    if (bulkAction === 'delete' && !confirm('Delete ' + selected.length + ' post(s) permanently?')) return;
                    if (bulkAction === 'tag' && !bulkTagId) { alert('Please select a tag.'); return; }
                    $el.closest('form').submit();
                "
                class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
            Apply
        </button>
        <button type="button" @click="selected = []; document.querySelectorAll('.post-checkbox, #check-all').forEach(b => b.checked = false)"
                class="text-sm text-gray-500 hover:text-gray-700 px-2 py-1.5">Clear</button>
    </form>
</div>

{{-- Status tabs --}}
<div class="flex items-center gap-1 mb-5 overflow-x-auto pb-1">
    @foreach([
        ['',          'All',       $counts['all']],
        ['published', 'Published', $counts['published']],
        ['draft',     'Pending',   $counts['draft']],
        ['scheduled', 'Scheduled', $counts['scheduled']],
        ['archived',  'Archived',  $counts['archived']],
    ] as [$val, $label, $count])
    <a href="/admin/posts{{ $val ? '?status='.$val : '' }}"
        class="whitespace-nowrap flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-sm font-medium transition-colors
        {{ request('status', '') === $val ? 'bg-brand-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
        {{ $label }}
        <span class="text-[11px] {{ request('status', '') === $val ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }} px-1.5 py-0.5 rounded-full font-bold">{{ $count }}</span>
    </a>
    @endforeach
</div>

{{-- Search --}}
<form method="GET" class="flex gap-2 mb-4">
    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search posts..."
        class="text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 w-64">
    <button class="px-4 py-2 bg-brand-500 text-white text-sm rounded-lg hover:bg-brand-600 font-medium">Search</button>
    @if(request('q') || request('status'))
    <a href="/admin/posts" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-300 text-sm rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-50">Clear</a>
    @endif
</form>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    {{-- Mobile card list --}}
    <div class="divide-y divide-gray-100 dark:divide-gray-700 md:hidden">
        @forelse($posts as $post)
        <div class="px-4 py-3">
            <label class="flex items-start gap-2">
                <input type="checkbox" class="post-checkbox mt-1 rounded border-gray-300" value="{{ $post->id }}"
                       x-model="selected">
                <div class="flex-1">
                    <a href="/posts/{{ $post->slug }}" class="font-medium text-gray-900 dark:text-white hover:text-brand-600 block mb-0.5" target="_blank">{{ $post->title }}</a>
                    <p class="text-xs text-gray-400 mb-2">{{ $post->created_at->format('d M Y') }} · {{ $post->category?->name_en }}</p>
                </div>
            </label>
            <div class="flex flex-wrap items-center gap-2 pl-6">
                <form method="POST" action="/admin/posts/{{ $post->id }}/status" class="flex items-center gap-1">
                    @csrf
                    <select name="status" class="text-xs border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1 bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @foreach(['draft','published','scheduled','archived'] as $s)
                        <option value="{{ $s }}" {{ $post->status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button class="text-xs px-2 py-1 bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-lg hover:bg-brand-100 font-medium">Set</button>
                </form>
                <a href="/dashboard/posts/{{ $post->id }}/edit" class="text-xs font-medium text-blue-500 hover:text-blue-700 px-2 py-1 bg-blue-50 dark:bg-blue-900/20 rounded-lg">Edit</a>
                <form method="POST" action="/admin/posts/{{ $post->id }}" onsubmit="return confirm('Delete permanently?')">
                    @csrf @method('DELETE')
                    <button class="text-xs font-medium text-red-400 hover:text-red-600 px-2 py-1 bg-red-50 dark:bg-red-900/20 rounded-lg">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-12 text-gray-400 dark:text-gray-500">No posts found.</div>
        @endforelse
    </div>

    {{-- Desktop table --}}
    <table class="w-full text-sm hidden md:table">
        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-100 dark:border-gray-600">
            <tr>
                <th class="px-4 py-3 w-10">
                    <input type="checkbox" id="check-all" @change="toggleAll($event)" class="rounded border-gray-300">
                </th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wide">Post</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wide">Author</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wide hidden lg:table-cell">Format</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wide">Views</th>
                <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wide">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
            @forelse($posts as $post)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" :class="selected.includes({{ $post->id }}) ? 'bg-blue-50/50 dark:bg-blue-900/10' : ''">
                <td class="px-4 py-3">
                    <input type="checkbox" class="post-checkbox rounded border-gray-300" value="{{ $post->id }}"
                           x-model="selected">
                </td>
                <td class="px-5 py-3">
                    <a href="/posts/{{ $post->slug }}" class="font-medium text-gray-900 dark:text-white hover:text-brand-600 line-clamp-1 block max-w-xs" target="_blank">{{ $post->title }}</a>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $post->created_at->format('d M Y') }} · {{ $post->category?->name_en }}</p>
                    @if($post->scheduled_at)
                    <p class="text-xs text-blue-500 mt-0.5">🕐 Scheduled: {{ $post->scheduled_at->format('d M Y H:i') }}</p>
                    @endif
                </td>
                <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $post->author?->name }}</td>
                <td class="px-5 py-3 hidden lg:table-cell">
                    <span class="text-[10px] font-semibold uppercase tracking-wide bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded-full">{{ $post->post_format ?? 'article' }}</span>
                </td>
                <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ number_format($post->view_count) }}</td>
                <td class="px-5 py-3">
                    <form method="POST" action="/admin/posts/{{ $post->id }}/status" class="flex items-center gap-1">
                        @csrf
                        <select name="status" class="text-xs border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                            @foreach(['draft','published','scheduled','archived'] as $s)
                            <option value="{{ $s }}" {{ $post->status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <button class="text-xs px-2 py-1.5 bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 rounded-lg hover:bg-brand-100 font-medium">Set</button>
                    </form>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <a href="/dashboard/posts/{{ $post->id }}/edit" class="text-xs text-blue-500 hover:text-blue-700 font-medium">Edit</a>
                        <form method="POST" action="/admin/posts/{{ $post->id }}/toggle-pro" title="{{ $post->is_pro ? 'Remove Pro lock' : 'Mark as Pro-only' }}">
                            @csrf
                            <button class="text-xs font-semibold {{ $post->is_pro ? 'text-brand-600 bg-brand-50 dark:bg-brand-900/20' : 'text-gray-400 hover:text-brand-600' }} px-1.5 py-0.5 rounded transition-colors">
                                {{ $post->is_pro ? '✨Pro' : 'Pro?' }}
                            </button>
                        </form>
                        <form method="POST" action="/admin/posts/{{ $post->id }}" onsubmit="return confirm('Delete permanently?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-600 font-medium">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-12 text-gray-400 dark:text-gray-500">No posts found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-5 py-3 border-t border-gray-50 dark:border-gray-700">{{ $posts->links() }}</div>
</div>

</div>{{-- end x-data --}}
@endsection
