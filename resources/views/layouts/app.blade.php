@php
    $__s        = file_exists(storage_path('app/site_settings.json'))
                    ? (json_decode(file_get_contents(storage_path('app/site_settings.json')), true) ?? [])
                    : [];
    $siteName   = $__s['site_name']   ?? config('app.name', 'nkhoj');
    $taglineNe  = $__s['tagline_ne']  ?? 'नेपाली समाचार';
    $taglineEn  = $__s['tagline_en']  ?? '';
    $siteDesc   = $__s['site_description'] ?? 'नेपालको अग्रणी समाचार र ब्लग प्लेटफर्म';
    $showHome   = ($__s['nav_home_page_link'] ?? 'show') === 'show';
    try {
        $navItems = \App\Models\NavigationItem::where('is_active', true)
                        ->whereNull('parent_id')
                        ->where('language', 'en')
                        ->orderBy('sort_order')
                        ->with('children')
                        ->get();
    } catch (\Exception $e) {
        $navItems = collect();
    }
    // Fallback: if no nav items defined yet, show categories
    try {
        $useFallback = $navItems->isEmpty();
    } catch (\Exception $e) {
        $useFallback = true;
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteName){{ $taglineEn ? ' — '.$taglineEn : ($taglineNe ? ' — '.$taglineNe : '') }}</title>
    <meta name="description" content="@yield('description', $siteDesc)">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        nepali: ['Noto Sans Devanagari', 'sans-serif'],
                    },
                    colors: {
                        brand: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',500:'#6366f1',600:'#4f46e5',700:'#4338ca',900:'#312e81' }
                    }
                }
            }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .line-clamp-2 { display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }
        .line-clamp-3 { display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden; }
        @keyframes ticker { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        .ticker-track { display:flex; animation: ticker 28s linear infinite; width:max-content; }
        .ticker-track:hover { animation-play-state: paused; }
    </style>
    <script>
        (function() {
            if (localStorage.getItem('siteTheme') === 'dark') {
                document.getElementById('html-root').classList.add('dark');
            }
        })();
    </script>
    @stack('head')
</head>
<body class="bg-gray-50 dark:bg-gray-950 font-sans antialiased transition-colors duration-200"
    x-data="{
        dark: localStorage.getItem('siteTheme') === 'dark',
        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem('siteTheme', this.dark ? 'dark' : 'light');
            document.getElementById('html-root').classList.toggle('dark', this.dark);
        },
        formatModal: false,
        mobileMenu: false,
    }">

{{-- ═══════════════════════════════════════════════════════
     TOP BAR (dark strip)
═══════════════════════════════════════════════════════ --}}
<div class="bg-gray-900 dark:bg-black text-gray-300 text-xs border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-8">
        {{-- Left links --}}
        <div class="flex items-center gap-4">
            <a href="/contact" class="hover:text-white transition-colors">Contact</a>
            <span class="text-gray-700">|</span>
            <a href="/about" class="hover:text-white transition-colors">About</a>
            <span class="text-gray-700">|</span>
            <a href="/register" class="hover:text-white transition-colors">Become an Author</a>
        </div>
        {{-- Right actions --}}
        <div class="flex items-center gap-3">
            {{-- Add Post button --}}
            @auth
            <button @click="formatModal = true"
                class="flex items-center gap-1.5 text-xs font-semibold text-white hover:text-brand-400 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                पोस्ट थप्नुहोस्
            </button>
            <span class="text-gray-700">|</span>
            {{-- User dropdown --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button @click="open = !open" class="flex items-center gap-1.5 hover:text-white transition-colors">
                    <div class="w-5 h-5 rounded-full bg-brand-500 flex items-center justify-center text-white text-[9px] font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span>{{ auth()->user()->name }}</span>
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak
                    class="absolute right-0 top-7 w-44 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden z-50 text-gray-700 dark:text-gray-200">
                    <a href="/dashboard" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                    <a href="/profile/{{ auth()->user()->username }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile
                    </a>
                    @if(auth()->user()->isAdmin())
                    <a href="/admin" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-brand-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Admin Panel
                    </a>
                    @endif
                    <div class="border-t border-gray-100 dark:border-gray-700">
                        <form method="POST" action="/logout">
                            @csrf
                            <button class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 text-left">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @else
            <a href="/login" class="hover:text-white transition-colors">Sign in</a>
            <span class="text-gray-700">|</span>
            <a href="/register" class="hover:text-white transition-colors font-semibold">Register</a>
            @endauth
            <span class="text-gray-700">|</span>
            {{-- Language stub --}}
            <button class="flex items-center gap-1 hover:text-white transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                NP
            </button>
            {{-- Dark mode toggle --}}
            <button @click="toggleDark()" class="hover:text-white transition-colors" :title="dark ? 'Light Mode' : 'Dark Mode'">
                <svg x-show="!dark" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="dark" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z"/></svg>
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MAIN HEADER
═══════════════════════════════════════════════════════ --}}
<header class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 sticky top-0 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2 flex-shrink-0">
                <span class="text-2xl font-black text-brand-600 tracking-tight">{{ $siteName }}</span>
                @if($taglineNe || $taglineEn)
                <span class="hidden sm:block text-[10px] text-gray-400 font-nepali leading-tight mt-0.5">{{ $taglineNe ?: $taglineEn }}</span>
                @endif
            </a>

            {{-- Nav (desktop) --}}
            <nav class="hidden lg:flex items-center gap-0 overflow-x-auto flex-1 mx-6">
                @if($showHome)
                <a href="/" class="whitespace-nowrap px-3 py-4 text-sm font-medium {{ request()->is('/') ? 'text-brand-600 border-b-2 border-brand-500' : 'text-gray-600 dark:text-gray-300 hover:text-brand-600 border-b-2 border-transparent' }} transition-colors">Home</a>
                @endif

                @if($useFallback)
                    @foreach((\App\Models\Category::orderBy('sort_order')->limit(7)->get() ?? collect()) as $cat)
                    <a href="/category/{{ $cat->slug }}"
                        class="whitespace-nowrap px-3 py-4 text-sm font-medium font-nepali {{ request()->is('category/'.$cat->slug) ? 'text-brand-600 border-b-2 border-brand-500' : 'text-gray-600 dark:text-gray-300 hover:text-brand-600 border-b-2 border-transparent' }} transition-colors">
                        {{ $cat->name_ne ?? $cat->name_en }}
                    </a>
                    @endforeach
                    <a href="/feed.xml" class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-brand-600 border-b-2 border-transparent transition-colors">RSS News</a>
                @else
                    @foreach($navItems as $nav)
                    @php $navUrl = $nav->resolvedUrl(); @endphp
                    @if($nav->children->count())
                    <div class="relative group">
                        <button class="whitespace-nowrap px-3 py-4 text-sm font-medium flex items-center gap-1 text-gray-600 dark:text-gray-300 hover:text-brand-600 border-b-2 border-transparent transition-colors">
                            {{ $nav->label }}
                            <svg class="w-3 h-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="absolute left-0 top-full hidden group-hover:block bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-lg py-2 min-w-[180px] z-50">
                            @foreach($nav->children as $child)
                            <a href="{{ $child->resolvedUrl() }}" target="{{ $child->open_in ?? '_self' }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-brand-600">{{ $child->label }}</a>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <a href="{{ $navUrl }}" target="{{ $nav->open_in ?? '_self' }}"
                        class="whitespace-nowrap px-3 py-4 text-sm font-medium {{ request()->is(ltrim($navUrl,'/')) || request()->fullUrlIs(url($navUrl)) ? 'text-brand-600 border-b-2 border-brand-500' : 'text-gray-600 dark:text-gray-300 hover:text-brand-600 border-b-2 border-transparent' }} transition-colors">
                        {{ $nav->label }}
                    </a>
                    @endif
                    @endforeach
                @endif
            </nav>

            {{-- Right: search + notification --}}
            <div class="flex items-center gap-2">
                {{-- Search toggle --}}
                <div x-data="{ searchOpen: false }" class="relative">
                    <button @click="searchOpen = !searchOpen"
                        class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    <div x-show="searchOpen" x-cloak x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                        @click.outside="searchOpen = false"
                        class="absolute right-0 top-11 w-72 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg p-3 z-50">
                        <form action="/search" method="GET">
                            <input type="text" name="q" autofocus placeholder="समाचार खोज्नुहोस्..."
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </form>
                    </div>
                </div>
                @auth
                {{-- Notification --}}
                <div class="relative" x-data="{ count: 0 }" x-init="fetch('/notifications/count').then(r=>r.json()).then(d=>count=d.count).catch(()=>{})">
                    <a href="/notifications"
                        class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors relative">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span x-show="count > 0" x-text="count > 9 ? '9+' : count"
                            class="absolute -top-0.5 -right-0.5 bg-brand-500 text-white text-[9px] rounded-full min-w-[15px] h-3.5 flex items-center justify-center px-1 font-bold"></span>
                    </a>
                </div>
                @endauth
                {{-- Mobile menu --}}
                <button @click="mobileMenu = true" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>
</header>

{{-- ═══════════════════════════════════════════════════════
     BREAKING NEWS TICKER
═══════════════════════════════════════════════════════ --}}
@php
    try {
        $breakingPosts = \App\Models\Post::where('status', 'published')
            ->latest('published_at')->limit(8)->pluck('title')->toArray();
    } catch (\Exception $e) {
        $breakingPosts = [];
    }
@endphp
@if(count($breakingPosts))
<div class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 overflow-hidden" style="height:38px;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center">
        {{-- Badge --}}
        <div class="flex-shrink-0 flex items-center gap-1.5 bg-brand-500 text-white text-xs font-bold px-3 py-1 rounded-sm mr-4 whitespace-nowrap">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
            Breaking News
        </div>
        {{-- Scrolling text --}}
        <div class="flex-1 overflow-hidden relative">
            <div class="ticker-track">
                @foreach(array_merge($breakingPosts, $breakingPosts) as $title)
                <span class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap mr-12">{{ $title }}</span>
                @endforeach
            </div>
        </div>
        {{-- Controls --}}
        <div class="flex-shrink-0 flex items-center gap-1 ml-4">
            <button onclick="this.closest('.ticker-track') && null"
                class="w-7 h-7 flex items-center justify-center border border-gray-200 dark:border-gray-600 rounded text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 text-xs">‹</button>
            <button
                class="w-7 h-7 flex items-center justify-center border border-gray-200 dark:border-gray-600 rounded text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 text-xs">›</button>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     POST FORMAT MODAL
═══════════════════════════════════════════════════════ --}}
<div x-show="formatModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="formatModal = false"></div>
    <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100">
        <div class="p-6 text-center border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Choose a Post Format</h2>
            <p class="text-sm text-brand-500 mt-1">Choose the type of content you want to create</p>
        </div>
        <button @click="formatModal = false"
            class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 hover:bg-gray-200 text-lg font-light">×</button>

        @php
        $_allFormats = [
            ['article',          'Article',           'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',       'An article with images and embed videos',                '/dashboard/posts/create?format=article'],
            ['sorted_list',      'Sorted List',       'M4 6h16M4 10h16M4 14h10',                                                                                                    'A list based article',                                   '/dashboard/posts/create?format=sorted_list'],
            ['table_of_contents','Table of Contents', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1','List of links based on headings',                       '/dashboard/posts/create?format=table_of_contents'],
            ['trivia_quiz',      'Trivia Quiz',       'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z','Quizzes with right and wrong answers',                  '/dashboard/posts/create?format=trivia_quiz'],
            ['personality_quiz', 'Personality Quiz',  'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4','Quizzes with custom results',                           '/dashboard/posts/create?format=personality_quiz'],
            ['poll',             'Poll',              'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z','Get user opinions about something',                     '/dashboard/posts/create?format=poll'],
            ['recipe',           'Recipe',            'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z','A list of ingredients and directions',                   '/dashboard/posts/create?format=recipe'],
            ['event',            'Event',             'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',                                     'Scheduled events with location and map details',         '/dashboard/create-event'],
            ['question',         'Ask Question',      'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z','Post a Q&A question for community answers',             '/ask-question'],
        ];
        try {
            $_sp = storage_path('app/site_settings.json');
            $_ss = file_exists($_sp) ? (json_decode(file_get_contents($_sp), true) ?? []) : [];
        } catch (\Exception $_e) { $_ss = []; }
        $_fe = $_ss['formats_enabled'] ?? null;
        $_filteredFormats = array_values(array_filter($_allFormats, function($f) use ($_fe) {
            if ($f[0] === 'article') return true;
            if ($f[0] === 'question') return true;
            if ($_fe === null) return true;
            return !empty($_fe[$f[0]]);
        }));
        @endphp
        <div class="p-6 grid grid-cols-3 gap-4">
            @foreach($_filteredFormats as [$format, $label, $icon, $desc, $url])
            <a href="{{ $url }}" @click="formatModal = false"
                class="flex flex-col items-center gap-2 p-4 border-2 border-gray-100 dark:border-gray-700 rounded-xl hover:border-brand-500 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-all cursor-pointer group">
                <div class="w-14 h-14 rounded-full bg-teal-50 dark:bg-teal-900/30 flex items-center justify-center group-hover:bg-brand-100 dark:group-hover:bg-brand-900/40 transition-colors">
                    <svg class="w-6 h-6 text-teal-600 dark:text-teal-400 group-hover:text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/>
                    </svg>
                </div>
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $label }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5 leading-tight">{{ $desc }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MOBILE DRAWER
═══════════════════════════════════════════════════════ --}}
<div x-show="mobileMenu" x-cloak class="lg:hidden fixed inset-0 z-50 flex">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="mobileMenu = false"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    {{-- Drawer panel --}}
    <div class="relative w-72 max-w-[85vw] bg-white dark:bg-gray-900 h-full flex flex-col shadow-2xl overflow-y-auto"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-shrink-0">
            <a href="/" @click="mobileMenu = false" class="text-xl font-black text-brand-600 tracking-tight">
                {{ $siteName }}<span class="text-brand-400">.</span>
            </a>
            <button @click="mobileMenu = false"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-lg font-light">
                ×
            </button>
        </div>

        {{-- User section (auth) --}}
        @auth
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                    @if(auth()->user()->isAdmin())
                    <span class="inline-block mt-0.5 text-[10px] font-semibold px-1.5 py-0.5 bg-brand-100 dark:bg-brand-900/40 text-brand-600 rounded-full">Super Admin</span>
                    @endif
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="grid grid-cols-3 gap-2">
                <button @click="mobileMenu = false; formatModal = true"
                    class="flex flex-col items-center gap-1 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 hover:bg-brand-50 dark:hover:bg-brand-900/30 transition-colors group">
                    <div class="w-8 h-8 rounded-full bg-white dark:bg-gray-700 flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <span class="text-[10px] font-medium text-gray-600 dark:text-gray-400">Add Post</span>
                </button>
                <a href="/profile/{{ auth()->user()->username ?? auth()->user()->name }}" @click="mobileMenu = false"
                    class="flex flex-col items-center gap-1 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 hover:bg-brand-50 dark:hover:bg-brand-900/30 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-white dark:bg-gray-700 flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <span class="text-[10px] font-medium text-gray-600 dark:text-gray-400">Profile</span>
                </a>
                <a href="/dashboard" @click="mobileMenu = false"
                    class="flex flex-col items-center gap-1 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 hover:bg-brand-50 dark:hover:bg-brand-900/30 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-white dark:bg-gray-700 flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <span class="text-[10px] font-medium text-gray-600 dark:text-gray-400">Dashboard</span>
                </a>
                <a href="/notifications" @click="mobileMenu = false"
                    class="flex flex-col items-center gap-1 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 hover:bg-brand-50 dark:hover:bg-brand-900/30 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-white dark:bg-gray-700 flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <span class="text-[10px] font-medium text-gray-600 dark:text-gray-400">Reading List</span>
                </a>
                <a href="/account/settings" @click="mobileMenu = false"
                    class="flex flex-col items-center gap-1 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 hover:bg-brand-50 dark:hover:bg-brand-900/30 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-white dark:bg-gray-700 flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="text-[10px] font-medium text-gray-600 dark:text-gray-400">Account</span>
                </a>
                @if(auth()->user()->isAdmin())
                <a href="/admin" @click="mobileMenu = false"
                    class="flex flex-col items-center gap-1 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 hover:bg-brand-50 dark:hover:bg-brand-900/30 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-brand-50 dark:bg-brand-900/40 flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                    </div>
                    <span class="text-[10px] font-medium text-brand-600">Admin Panel</span>
                </a>
                @endif
            </div>
        </div>
        @else
        {{-- Guest CTA --}}
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex gap-2">
            <a href="/login" @click="mobileMenu = false"
                class="flex-1 text-center py-2 border border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Sign In
            </a>
            <a href="/register" @click="mobileMenu = false"
                class="flex-1 text-center py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                Register
            </a>
        </div>
        @endauth

        {{-- Navigation --}}
        <nav class="flex-1 py-3">
            @if($showHome)
            <a href="/" @click="mobileMenu = false"
                class="flex items-center justify-between px-5 py-3 text-sm font-medium {{ request()->is('/') ? 'text-brand-600 bg-brand-50 dark:bg-brand-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' }} transition-colors">
                Home
            </a>
            @endif

            @if($useFallback)
                @foreach((\App\Models\Category::orderBy('sort_order')->limit(10)->get() ?? collect()) as $cat)
                <a href="/category/{{ $cat->slug }}" @click="mobileMenu = false"
                    class="flex items-center justify-between px-5 py-3 text-sm font-medium font-nepali {{ request()->is('category/'.$cat->slug) ? 'text-brand-600 bg-brand-50 dark:bg-brand-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' }} transition-colors">
                    {{ $cat->name_ne ?? $cat->name_en }}
                </a>
                @endforeach
                <a href="/feed.xml" @click="mobileMenu = false"
                    class="flex items-center justify-between px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    RSS News
                </a>
            @else
                @foreach($navItems as $nav)
                @php $navUrl = $nav->resolvedUrl(); @endphp
                @if($nav->children->count())
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                        class="w-full flex items-center justify-between px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        {{ $nav->label }}
                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <div x-show="open" class="bg-gray-50 dark:bg-gray-800/50">
                        @foreach($nav->children as $child)
                        <a href="{{ $child->resolvedUrl() }}" @click="mobileMenu = false"
                            class="block pl-9 pr-5 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:text-brand-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            {{ $child->label }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @else
                <a href="{{ $navUrl }}" @click="mobileMenu = false"
                    class="flex items-center justify-between px-5 py-3 text-sm font-medium {{ request()->is(ltrim($navUrl,'/')) ? 'text-brand-600 bg-brand-50 dark:bg-brand-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' }} transition-colors">
                    {{ $nav->label }}
                </a>
                @endif
                @endforeach
            @endif
        </nav>

        {{-- Bottom: dark mode + logout --}}
        <div class="border-t border-gray-100 dark:border-gray-800 px-5 py-4 space-y-1 flex-shrink-0">
            <button @click="toggleDark()"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <svg x-show="!dark" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="dark" class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0z"/></svg>
                <span x-text="dark ? 'Light Mode' : 'Dark Mode'"></span>
            </button>
            @auth
            <form method="POST" action="/logout">
                @csrf
                <button class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
            @endauth
        </div>
    </div>
</div>

{{-- MAIN CONTENT --}}
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @yield('content')
</main>

{{-- FOOTER --}}
<footer class="bg-gray-900 text-gray-400 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-1 md:col-span-2">
                <span class="text-2xl font-black text-white">{{ $siteName }}</span>
                @if($siteDesc)
                <p class="mt-2 text-sm font-nepali text-gray-400">{{ $siteDesc }}</p>
                @endif
            </div>
            <div>
                <h4 class="text-sm font-semibold text-white mb-3">Platform</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/" class="hover:text-white transition-colors">Home</a></li>
                    <li><a href="/search" class="hover:text-white transition-colors">Search</a></li>
                    <li><a href="/register" class="hover:text-white transition-colors">Start Writing</a></li>
                    <li><a href="/contact" class="hover:text-white transition-colors">Contact</a></li>
                    <li><a href="/feed.xml" class="hover:text-white transition-colors">RSS Feed</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-white mb-3">विभागहरू</h4>
                <ul class="space-y-2 text-sm">
                    @foreach((\App\Models\Category::limit(5)->get() ?? collect()) as $cat)
                    <li><a href="/category/{{ $cat->slug }}" class="hover:text-white transition-colors font-nepali">{{ $cat->name_ne ?? $cat->name_en }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="mt-8 pt-8 border-t border-gray-800">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-white">Newsletter सब्स्क्राइब गर्नुहोस्</p>
                    <p class="text-xs text-gray-500">ताजा समाचार र लेखहरू सिधै इमेलमा पाउनुहोस्।</p>
                </div>
                <form action="/newsletter/subscribe" method="POST" class="flex gap-2">
                    @csrf
                    <input type="email" name="email" placeholder="your@email.com" required
                        class="px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 w-56">
                    <button class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors whitespace-nowrap">Subscribe</button>
                </form>
            </div>
            <div class="mt-6 text-xs text-center text-gray-600">
                © {{ date('Y') }} {{ $siteName }}. Built with Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }}
            </div>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
