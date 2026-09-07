{{--
  Content Locker Blade Component
  Usage: <x-content-locker :post="$post">{{ full content here }}</x-content-locker>
  Shows a blur overlay with subscribe CTA for non-pro users on pro posts.
--}}
@props(['post'])

@php
    $locked = $post->is_pro && !(auth()->check() && auth()->user()->hasPro());
@endphp

@if($locked)
<div class="relative">
    {{-- Show first ~300 chars blurred --}}
    <div class="relative overflow-hidden" style="max-height: 320px;">
        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed font-nepali select-none" style="filter: blur(4px); pointer-events: none; user-select: none;">
            {{ $slot }}
        </div>
        {{-- Gradient fade --}}
        <div class="absolute bottom-0 left-0 right-0 h-48 bg-gradient-to-t from-white dark:from-gray-900 to-transparent"></div>
    </div>

    {{-- Lock overlay --}}
    <div class="relative mt-0 pt-8 pb-10 text-center px-6 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700">
        <div class="w-14 h-14 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <span class="inline-flex items-center gap-1.5 bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 text-xs font-bold px-3 py-1 rounded-full mb-3">
            ✨ Pro Content
        </span>

        <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2">This is a Pro-only article</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">
            Subscribe to unlock full access to this article and all other premium content.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="/membership"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl transition-colors text-sm shadow-md shadow-brand-200/50">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/></svg>
                View Plans & Subscribe
            </a>
            @guest
            <a href="/login?next={{ urlencode(url()->current()) }}"
                class="inline-flex items-center justify-center px-6 py-3 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm">
                Already a member? Sign in
            </a>
            @endguest
        </div>
    </div>
</div>
@else
{{-- Not locked — show content normally --}}
<div class="prose prose-lg max-w-none text-gray-700 leading-relaxed font-nepali">
    {{ $slot }}
</div>
@endif
