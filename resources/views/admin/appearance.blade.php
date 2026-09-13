@extends('layouts.admin')
@section('title', 'Appearance')

@section('content')

@php
    $ap = $s['appearance'] ?? [];
    $brandColor = $ap['brand_color'] ?? '#6366f1';
    $customCss  = $ap['custom_css']  ?? '';
@endphp

<div class="max-w-3xl">

@if(session('success'))
<div class="mb-5 px-4 py-2.5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm rounded-xl flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

<form method="POST" action="/admin/appearance" x-data="appearance()" @submit="applyPreview()">
@csrf

{{-- Brand Color ──────────────────────────────────── --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm mb-5 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <h2 class="font-semibold text-gray-900 dark:text-white text-sm">Brand Color</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Primary color used for buttons, links, and highlights across the site.</p>
    </div>
    <div class="px-5 py-5 space-y-5">

        {{-- Color picker + hex input --}}
        <div class="flex items-center gap-4">
            <div class="relative w-14 h-14 rounded-xl overflow-hidden shadow border border-gray-200 dark:border-gray-600 flex-shrink-0 cursor-pointer"
                 @click="$refs.colorPicker.click()">
                <div class="absolute inset-0 rounded-xl" :style="'background:'+color"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white opacity-80 drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                </div>
                <input x-ref="colorPicker" type="color" x-model="color" @input="syncHex()" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
            </div>
            <div class="flex-1">
                <label class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1 block">HEX Value</label>
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 text-sm font-mono">#</span>
                    <input type="text" x-model="hexInput" @input="syncColor()" maxlength="6"
                        class="font-mono text-sm border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-28 focus:ring-2 focus:ring-brand-500 outline-none uppercase"
                        placeholder="6366f1">
                </div>
                <input type="hidden" name="brand_color" :value="color">
            </div>
        </div>

        {{-- Preset swatches --}}
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Presets</p>
            <div class="flex flex-wrap gap-2">
                @foreach([
                    '#6366f1' => 'Indigo',
                    '#8b5cf6' => 'Violet',
                    '#ec4899' => 'Pink',
                    '#ef4444' => 'Red',
                    '#f97316' => 'Orange',
                    '#eab308' => 'Yellow',
                    '#22c55e' => 'Green',
                    '#14b8a6' => 'Teal',
                    '#3b82f6' => 'Blue',
                    '#06b6d4' => 'Cyan',
                    '#64748b' => 'Slate',
                    '#111827' => 'Dark',
                ] as $hex => $name)
                <button type="button" @click="setColor('{{ $hex }}')"
                    class="w-8 h-8 rounded-lg shadow-sm border-2 transition-all hover:scale-110"
                    :class="color === '{{ $hex }}' ? 'border-gray-900 dark:border-white scale-110' : 'border-transparent'"
                    style="background: {{ $hex }}"
                    title="{{ $name }}">
                </button>
                @endforeach
            </div>
        </div>

        {{-- Live preview --}}
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Preview</p>
            <div class="flex flex-wrap gap-3 items-center p-4 bg-gray-50 dark:bg-gray-900 rounded-xl">
                <button type="button" class="px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm transition-colors" :style="'background:'+color">Button</button>
                <button type="button" class="px-4 py-2 rounded-lg text-sm font-semibold border-2 transition-colors" :style="'color:'+color+';border-color:'+color">Outline</button>
                <span class="text-sm font-medium cursor-pointer hover:underline" :style="'color:'+color">Link text</span>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full" :style="'background:'+color+'22;color:'+color">Badge</span>
                <div class="w-full h-1.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                    <div class="h-full rounded-full w-3/5" :style="'background:'+color"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Custom CSS ───────────────────────────────────── --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm mb-5 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <h2 class="font-semibold text-gray-900 dark:text-white text-sm">Custom CSS</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Injected into every frontend page. Use carefully — invalid CSS can break the layout.</p>
    </div>
    <div class="px-5 py-5">
        <textarea name="custom_css" rows="14" spellcheck="false"
            class="w-full font-mono text-xs border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-500 outline-none resize-y leading-relaxed"
            placeholder="/* Add custom CSS here */&#10;&#10;/* Example: change link color */&#10;a { color: inherit; }&#10;&#10;/* Example: custom font size */&#10;body { font-size: 16px; }">{{ $customCss }}</textarea>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">This CSS is added after all other styles. Wrap selectors in <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">body</code> to increase specificity.</p>
    </div>
</div>

{{-- Submit ───────────────────────────────────────── --}}
<div class="flex items-center justify-end">
    <button type="submit"
        class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
        Save Appearance
    </button>
</div>

</form>
</div>

<script>
function appearance() {
    return {
        color: '{{ $brandColor }}',
        hexInput: '{{ ltrim($brandColor, '#') }}',
        setColor(hex) {
            this.color = hex;
            this.hexInput = hex.replace('#','');
        },
        syncHex() {
            this.hexInput = this.color.replace('#','').toUpperCase();
        },
        syncColor() {
            const v = this.hexInput.replace(/[^0-9a-fA-F]/g,'');
            if (v.length === 6) this.color = '#' + v;
        },
        applyPreview() {
            // nothing needed — full save reloads
        }
    }
}
</script>
@endsection
