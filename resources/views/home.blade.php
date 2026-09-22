@extends('layouts.app')
@section('title', 'nkhoj — नेपाली समाचार')

@section('content')

@php
// Default section order — admin can reorder/toggle via /admin/homepage
$defaultSections = [
    ['key' => 'hero_strip',          'label' => 'Hero Strip',      'enabled' => true],
    ['key' => 'stories',             'label' => 'Stories',         'enabled' => true],
    ['key' => 'home_top_widgets',    'label' => 'Top Widgets',     'enabled' => true],
    ['key' => 'category_tabs',       'label' => 'Category Tabs',   'enabled' => true],
    ['key' => 'editors_pick',        'label' => 'Editor\'s Pick',  'enabled' => true],
    ['key' => 'posts_feed',          'label' => 'Posts Feed',      'enabled' => true],
    ['key' => 'home_bottom_widgets', 'label' => 'Bottom Widgets',  'enabled' => true],
];
$sections = $homepageSections ?? $defaultSections;
@endphp

{{-- ══ TWO-PANEL WRAPPER — sidebar is always present ══ --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    {{-- ══ MAIN COLUMN (sections rendered in configured order) ══ --}}
    <div class="lg:col-span-3 space-y-0">

        @foreach($sections as $section)
        @if($section['enabled'] ?? true)
            @if($section['key'] === 'hero_strip')
                @include('homepage._hero_strip')
            @elseif($section['key'] === 'stories')
                @include('homepage._stories')
            @elseif($section['key'] === 'home_top_widgets')
                @include('homepage._widgets_zone', ['zoneWidgets' => $homeTopWidgets])
            @elseif($section['key'] === 'category_tabs')
                @include('homepage._category_tabs')
            @elseif($section['key'] === 'editors_pick')
                @include('homepage._editors_pick')
            @elseif($section['key'] === 'posts_feed')
                @include('homepage._posts_feed')
            @elseif($section['key'] === 'home_bottom_widgets')
                @include('homepage._widgets_zone', ['zoneWidgets' => $homeBottomWidgets])
            @endif
        @endif
        @endforeach

    </div>

    {{-- ══ RIGHT SIDEBAR ══ --}}
    <aside class="space-y-4">

        @auth
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-brand-500 to-indigo-600 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                    </div>
                    <div>
                        <p class="text-white font-semibold text-sm">{{ auth()->user()->name }}</p>
                        <p class="text-white/60 text-xs">{{ auth()->user()->username }}</p>
                    </div>
                </div>
                <form method="POST" action="/logout" class="inline">
                    @csrf
                    <button class="text-xs text-white/60 hover:text-white">log out</button>
                </form>
            </div>
            <div class="p-4 space-y-3">
                <div class="grid grid-cols-2 gap-2">
                    <a href="/profile/{{ auth()->user()->username }}"
                        class="text-center py-2 text-sm font-semibold text-brand-600 bg-brand-50 hover:bg-brand-100 rounded-lg transition-colors border border-brand-200">
                        मेरो ब्लग
                    </a>
                    <a href="/dashboard/posts/create"
                        class="text-center py-2 text-sm font-semibold text-white bg-brand-500 hover:bg-brand-600 rounded-lg transition-colors">
                        ✍ लेख्नुहोस्
                    </a>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-700 pt-3">
                    <div class="flex border-b border-gray-100 dark:border-gray-700 mb-2 text-xs">
                        <span class="pb-1.5 px-2 font-semibold text-brand-600 border-b-2 border-brand-500">मेरो समाचार</span>
                        <a href="/dashboard" class="pb-1.5 px-2 text-gray-400 hover:text-gray-600">गतिविधि</a>
                        <a href="/following/feed" class="pb-1.5 px-2 text-gray-400 hover:text-gray-600">फलोइङ</a>
                    </div>
                    <p class="text-xs text-gray-400 text-center py-3 font-nepali">नयाँ समाचार छैन।</p>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 text-center">
            <div class="text-4xl mb-3">✍️</div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-1 font-nepali">नखोजमा सामेल हुनुहोस्</h3>
            <p class="text-xs text-gray-400 mb-4">हजारौं नेपाली पाठकसँग आफ्नो कथा साझा गर्नुहोस्।</p>
            <a href="/register" class="block text-center bg-brand-500 text-white font-semibold text-sm py-2.5 rounded-lg hover:bg-brand-600 transition-colors mb-2">
                Free मा सुरु गर्नुहोस्
            </a>
            <a href="/login" class="block text-center text-sm text-gray-400 hover:text-gray-600">साइन इन</a>
        </div>
        @endauth

        <x-newsletter-widget class="mb-6" />

        @if($sidebarWidgets->isNotEmpty())
            @foreach($sidebarWidgets as $widget)
                @include('partials._widget', ['widget' => $widget, 'data' => $widgetData])
            @endforeach
        @else
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
                <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm flex items-center gap-1.5">
                    <span class="text-red-500">🔥</span> ट्रेन्डिङ
                </h3>
                <ol class="space-y-2.5">
                    @foreach($trending as $i => $post)
                    <li class="flex gap-2.5 items-start">
                        <span class="text-lg font-black leading-none mt-0.5 min-w-[18px] {{ $i < 3 ? 'text-brand-500' : 'text-gray-200 dark:text-gray-600' }}">{{ $i+1 }}</span>
                        <div class="flex-1 min-w-0">
                            <a href="/posts/{{ $post->slug }}" class="text-xs font-medium text-gray-800 dark:text-gray-200 hover:text-brand-600 dark:hover:text-brand-400 transition-colors line-clamp-2 font-nepali leading-snug">{{ $post->title }}</a>
                            <p class="text-xs text-gray-400 mt-0.5">{{ number_format($post->view_count) }} views</p>
                        </div>
                    </li>
                    @endforeach
                </ol>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
                <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm">📂 विषयहरू</h3>
                <div class="space-y-1">
                    @foreach($categories as $cat)
                    <a href="/category/{{ $cat->slug }}"
                        class="flex items-center justify-between py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 transition-colors group">
                        <span class="font-nepali text-sm">{{ $cat->name_ne ?? $cat->name_en }}</span>
                        <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full group-hover:bg-brand-50 dark:group-hover:bg-brand-900/30 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ $cat->posts_count }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        @endif

    </aside>
</div>

@push('scripts')
<script>
async function toggleFollow(btn, userId) {
    btn.disabled = true;
    try {
        const r = await fetch('/follow/' + userId, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        });
        const d = await r.json();
        if (d.action === 'followed') {
            btn.textContent = '✓ Following';
            btn.classList.add('border-brand-400', 'text-brand-600');
            btn.dataset.following = 'true';
        } else {
            btn.textContent = '+ Add';
            btn.classList.remove('border-brand-400', 'text-brand-600');
            btn.dataset.following = 'false';
        }
    } catch(e) {}
    btn.disabled = false;
}
</script>
@endpush

@endsection
