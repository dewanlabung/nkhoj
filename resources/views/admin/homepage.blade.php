@extends('layouts.admin')
@section('title', 'Homepage Layout')

@section('content')

@if(session('success'))
<div class="mb-5 px-4 py-2.5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm rounded-xl flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Section ordering panel --}}
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <form method="POST" action="/admin/homepage" id="homepage-form">
            @csrf
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-gray-900 dark:text-white text-sm">Section Order & Visibility</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Drag rows to reorder. Toggle to show or hide each section.</p>
                </div>
                <button type="submit" id="save-btn"
                    class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm flex items-center gap-2">
                    <svg id="save-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Save Layout
                </button>
            </div>

            <div id="sections-list" class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($sections as $i => $section)
                <div class="section-row flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors cursor-grab active:cursor-grabbing"
                    data-key="{{ $section['key'] }}"
                    data-label="{{ $section['label'] }}"
                    data-enabled="{{ $section['enabled'] ? '1' : '0' }}">

                    {{-- Drag handle --}}
                    <svg class="w-4 h-4 text-gray-300 dark:text-gray-500 flex-shrink-0 pointer-events-none" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 6a2 2 0 110-4 2 2 0 010 4zm8 0a2 2 0 110-4 2 2 0 010 4zM8 14a2 2 0 110-4 2 2 0 010 4zm8 0a2 2 0 110-4 2 2 0 010 4zM8 22a2 2 0 110-4 2 2 0 010 4zm8 0a2 2 0 110-4 2 2 0 010 4z"/>
                    </svg>

                    {{-- Section icon --}}
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $section['enabled'] ? 'bg-brand-50 dark:bg-brand-900/30' : 'bg-gray-100 dark:bg-gray-700' }}">
                        @if($section['key'] === 'hero_strip')
                        <svg class="w-4 h-4 {{ $section['enabled'] ? 'text-brand-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @elseif($section['key'] === 'stories')
                        <svg class="w-4 h-4 {{ $section['enabled'] ? 'text-brand-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                        @elseif(str_contains($section['key'], 'widgets'))
                        <svg class="w-4 h-4 {{ $section['enabled'] ? 'text-brand-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                        @elseif($section['key'] === 'category_tabs')
                        <svg class="w-4 h-4 {{ $section['enabled'] ? 'text-brand-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        @elseif($section['key'] === 'editors_pick')
                        <svg class="w-4 h-4 {{ $section['enabled'] ? 'text-brand-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        @else
                        <svg class="w-4 h-4 {{ $section['enabled'] ? 'text-brand-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        @endif
                    </div>

                    {{-- Label + key --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $section['label'] }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $section['key'] }}</p>
                    </div>

                    {{-- Toggle --}}
                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                        <input type="checkbox" class="sr-only toggle-checkbox" {{ $section['enabled'] ? 'checked' : '' }}>
                        <div class="toggle-track w-10 h-5 rounded-full transition-colors {{ $section['enabled'] ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-600' }}"></div>
                        <div class="toggle-thumb absolute left-0.5 top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform {{ $section['enabled'] ? 'translate-x-5' : 'translate-x-0' }}"></div>
                    </label>
                </div>
                @endforeach
            </div>

            {{-- Hidden form fields — populated by JS before submit --}}
            <div id="form-fields"></div>
            </form>
        </div>
    </div>

    {{-- Info panel --}}
    <div class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-3">Section Guide</h3>
            <dl class="space-y-3 text-xs">
                <div>
                    <dt class="font-semibold text-gray-700 dark:text-gray-300">Hero Strip</dt>
                    <dd class="text-gray-400 mt-0.5">Top 3 featured/popular posts in an image grid.</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-700 dark:text-gray-300">Stories</dt>
                    <dd class="text-gray-400 mt-0.5">Horizontal scroll of active user stories and highlights.</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-700 dark:text-gray-300">Top / Bottom Widgets</dt>
                    <dd class="text-gray-400 mt-0.5">Widgets assigned to <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">home_top</code> or <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">home_bottom</code> positions in the <a href="/admin/widgets" class="text-brand-500 hover:underline">Widgets</a> admin.</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-700 dark:text-gray-300">Category Tabs</dt>
                    <dd class="text-gray-400 mt-0.5">Category pills for AJAX tab switching of the posts feed.</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-700 dark:text-gray-300">Editor's Pick</dt>
                    <dd class="text-gray-400 mt-0.5">3 high-view posts curated as editor choices.</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-700 dark:text-gray-300">Posts Feed</dt>
                    <dd class="text-gray-400 mt-0.5">Infinite-scroll post grid. Required for the homepage to show content.</dd>
                </div>
            </dl>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <p class="text-xs text-blue-700 dark:text-blue-300 font-medium mb-1">💡 Tip</p>
            <p class="text-xs text-blue-600 dark:text-blue-400">Add widgets to the <strong>home_top</strong> or <strong>home_bottom</strong> positions in <a href="/admin/widgets" class="underline">Widgets</a> to populate those zones here.</p>
        </div>

        <a href="/" target="_blank"
            class="flex items-center gap-2 px-4 py-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm text-sm text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Preview Homepage
        </a>
    </div>

</div>

<script>
// Toggle switches
document.querySelectorAll('.toggle-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        const row = this.closest('.section-row');
        const track = this.parentElement.querySelector('.toggle-track');
        const thumb = this.parentElement.querySelector('.toggle-thumb');
        const icon = row.querySelector('.w-8 svg');
        row.dataset.enabled = this.checked ? '1' : '0';
        track.classList.toggle('bg-brand-500', this.checked);
        track.classList.toggle('bg-gray-200', !this.checked);
        track.classList.toggle('dark:bg-gray-600', !this.checked);
        thumb.classList.toggle('translate-x-5', this.checked);
        thumb.classList.toggle('translate-x-0', !this.checked);
        if (icon) {
            icon.classList.toggle('text-brand-500', this.checked);
            icon.classList.toggle('text-gray-400', !this.checked);
        }
        const bg = row.querySelector('.w-8');
        if (bg) {
            bg.classList.toggle('bg-brand-50', this.checked);
            bg.classList.toggle('dark:bg-brand-900/30', this.checked);
            bg.classList.toggle('bg-gray-100', !this.checked);
            bg.classList.toggle('dark:bg-gray-700', !this.checked);
        }
    });
});

// Drag to reorder (native HTML5 drag, no library needed)
const list = document.getElementById('sections-list');
let dragging = null;

list.querySelectorAll('.section-row').forEach(row => {
    row.setAttribute('draggable', true);
    row.addEventListener('dragstart', e => {
        dragging = row;
        row.style.opacity = '0.4';
        e.dataTransfer.effectAllowed = 'move';
    });
    row.addEventListener('dragend', () => {
        dragging = null;
        row.style.opacity = '';
        list.querySelectorAll('.section-row').forEach(r => r.classList.remove('border-t-2', 'border-brand-400'));
    });
    row.addEventListener('dragover', e => {
        e.preventDefault();
        if (row === dragging) return;
        e.dataTransfer.dropEffect = 'move';
        list.querySelectorAll('.section-row').forEach(r => r.classList.remove('border-t-2', 'border-brand-400'));
        row.classList.add('border-t-2', 'border-brand-400');
    });
    row.addEventListener('drop', e => {
        e.preventDefault();
        if (row !== dragging) {
            list.insertBefore(dragging, row);
        }
        list.querySelectorAll('.section-row').forEach(r => r.classList.remove('border-t-2', 'border-brand-400'));
    });
});

// Build hidden fields on form submit
document.getElementById('homepage-form').addEventListener('submit', function() {
    // Show spinner
    const btn = document.getElementById('save-btn');
    const spinner = document.getElementById('save-spinner');
    if (btn) btn.disabled = true;
    if (spinner) spinner.classList.remove('hidden');

    const container = document.getElementById('form-fields');
    container.innerHTML = '';
    list.querySelectorAll('.section-row').forEach((row, i) => {
        const key     = row.dataset.key;
        const label   = row.dataset.label;
        const enabled = row.dataset.enabled === '1' ? '1' : '0';
        const mk = (n, v) => {
            const f = document.createElement('input');
            f.type = 'hidden'; f.name = n; f.value = v;
            container.appendChild(f);
        };
        mk(`sections[${i}][key]`,     key);
        mk(`sections[${i}][label]`,   label);
        mk(`sections[${i}][enabled]`, enabled);
    });
});
</script>

@endsection
