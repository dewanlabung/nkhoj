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
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#6366f1">
    @php
        $__pageTitle = trim(View::yieldContent('title', $siteName));
        $__fullTitle = $__pageTitle . ($taglineEn ? ' — '.$taglineEn : ($taglineNe ? ' — '.$taglineNe : ''));
        $__desc      = strip_tags(trim(View::yieldContent('description', $siteDesc)));
        $__ogImage   = trim(View::yieldContent('og_image', $__s['og_default_image'] ?? ''));
        $__canonical = trim(View::yieldContent('canonical', url()->current()));
        $__keywords  = $__s['site_keywords'] ?? '';
        $__siteUrl   = $__s['site_url'] ?? url('/');
        $__twitterHandle = ltrim($__s['social_twitter'] ?? '', 'https://x.com/https://twitter.com/@');
        $__jsonLd    = $__s['enable_jsonld'] ?? true;
    @endphp
    <title>{{ $__fullTitle }}</title>
    <link rel="canonical" href="{{ $__canonical }}">
    <meta name="description" content="{{ $__desc }}">
    @if($__keywords)
    <meta name="keywords" content="{{ $__keywords }}">
    @endif
    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $__pageTitle }}">
    <meta property="og:description" content="{{ $__desc }}">
    <meta property="og:url" content="{{ $__canonical }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    @if($__ogImage)
    <meta property="og:image" content="{{ $__ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    @endif
    <meta property="og:locale" content="ne_NP">
    {{-- Twitter Card --}}
    <meta name="twitter:card" content="{{ $__ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $__pageTitle }}">
    <meta name="twitter:description" content="{{ $__desc }}">
    @if($__ogImage)
    <meta name="twitter:image" content="{{ $__ogImage }}">
    @endif
    @if($__twitterHandle)
    <meta name="twitter:site" content="@{{ $__twitterHandle }}">
    @endif
    {{-- JSON-LD --}}
    @if($__jsonLd)
    @yield('jsonld')
    @endif
    {{-- Webmaster verification --}}
    @if(!empty($__s['verify_google']))
    <meta name="google-site-verification" content="{{ $__s['verify_google'] }}">
    @endif
    @if(!empty($__s['verify_bing']))
    <meta name="msvalidate.01" content="{{ $__s['verify_bing'] }}">
    @endif

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
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
<div class="hidden sm:block bg-gray-900 dark:bg-black text-gray-300 text-xs border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-8">
        {{-- Left links --}}
        <div class="flex items-center gap-4">
            <a href="/contact" class="hover:text-white transition-colors">Contact</a>
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
                    <a href="/account" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        My Account
                    </a>
                    <a href="/dashboard" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                    <a href="/profile/{{ auth()->user()->username }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile
                    </a>
                    <a href="/membership" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/></svg>
                        @if(auth()->user()->hasPro())
                        <span class="flex-1">Pro Membership</span>
                        <span class="text-[10px] bg-brand-500 text-white px-1.5 py-0.5 rounded font-bold">PRO</span>
                        @else
                        Upgrade to Pro
                        @endif
                    </a>
                    <a href="/support" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Support
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
                {{-- Mobile-only: dark mode toggle (top bar hidden on mobile) --}}
                <button @click="toggleDark()" class="sm:hidden w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" :title="dark ? 'Light Mode' : 'Dark Mode'">
                    <svg x-show="!dark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="dark" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z"/></svg>
                </button>
                {{-- Google-style apps grid (desktop only) --}}
                <div class="hidden lg:block relative" x-data="{ appsOpen: false }">
                    <button @click="appsOpen = !appsOpen" @click.outside="appsOpen = false"
                            class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            title="Services">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="3" width="4" height="4" rx="1"/><rect x="10" y="3" width="4" height="4" rx="1"/><rect x="17" y="3" width="4" height="4" rx="1"/>
                            <rect x="3" y="10" width="4" height="4" rx="1"/><rect x="10" y="10" width="4" height="4" rx="1"/><rect x="17" y="10" width="4" height="4" rx="1"/>
                            <rect x="3" y="17" width="4" height="4" rx="1"/><rect x="10" y="17" width="4" height="4" rx="1"/><rect x="17" y="17" width="4" height="4" rx="1"/>
                        </svg>
                    </button>
                    <div x-show="appsOpen" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute right-0 top-11 w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-4 z-50">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Your services</p>
                        <div class="grid grid-cols-3 gap-1">
                            @php
                            $apps = [
                                ['Pages',      '/pages',      'linear-gradient(135deg,#f97316,#f59e0b)', 'M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z'],
                                ['Q&A',         '/questions',  'linear-gradient(135deg,#10b981,#059669)', 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                ['Recipes',    '/recipe',     'linear-gradient(135deg,#ef4444,#ec4899)', 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
                                ['Events',     '/events',     'linear-gradient(135deg,#6366f1,#8b5cf6)', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                                ['Saved',      '/bookmarks',  'linear-gradient(135deg,#7c3aed,#a855f7)', 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z'],
                                ['Membership', '/membership', 'linear-gradient(135deg,#f59e0b,#eab308)', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
                                ['Alerts',     '/notifications','linear-gradient(135deg,#ec4899,#f43f5e)', 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                                ['Support',    '/support',    'linear-gradient(135deg,#14b8a6,#06b6d4)', 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z'],
                                ['Account',    '/account',    'linear-gradient(135deg,#64748b,#475569)', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                            ];
                            @endphp
                            @foreach($apps as [$label, $href, $grad, $path])
                            <a href="{{ $href }}" @click="appsOpen = false"
                               class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors group">
                                <span class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm" style="background:{{ $grad }}">
                                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $path }}"/></svg>
                                </span>
                                <span class="text-xs text-gray-600 dark:text-gray-400 font-medium group-hover:text-gray-900 dark:group-hover:text-white">{{ $label }}</span>
                            </a>
                            @endforeach
                            @auth
                            @if(auth()->user()->isAdmin())
                            <a href="/admin" @click="appsOpen = false"
                               class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors group">
                                <span class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm" style="background:linear-gradient(135deg,#1a73e8,#0d47a1)">
                                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                                </span>
                                <span class="text-xs text-gray-600 dark:text-gray-400 font-medium group-hover:text-gray-900 dark:group-hover:text-white">Admin</span>
                            </a>
                            @endif
                            @endauth
                        </div>
                    </div>
                </div>
                {{-- Mobile menu --}}
                <button @click="mobileMenu = true" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
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
            ['article',          'Article',           'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',       'An article with images and embed videos',       '/dashboard/posts/create?format=article',          'from-blue-400 to-blue-600',    true],
            ['sorted_list',      'Sorted List',       'M4 6h16M4 10h16M4 14h16M4 18h16',                                                                                             'A list-based article',                          '/dashboard/posts/create?format=sorted_list',      'from-orange-400 to-orange-600', false],
            ['table_of_contents','Table of Contents', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1','List of links based on headings',              '/dashboard/posts/create?format=table_of_contents','from-teal-400 to-teal-600',    false],
            ['trivia_quiz',      'Trivia Quiz',       'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z','Quizzes with right and wrong answers',         '/dashboard/posts/create?format=trivia_quiz',      'from-yellow-400 to-amber-500', false],
            ['personality_quiz', 'Personality Quiz',  'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4','Quizzes with custom results',                  '/dashboard/posts/create?format=personality_quiz', 'from-purple-400 to-purple-600', false],
            ['poll',             'Poll',              'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z','Get user opinions about something',            '/dashboard/posts/create?format=poll',             'from-indigo-400 to-indigo-600', false],
            ['recipe',           'Recipe',            'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z','A list of ingredients and directions',          '/dashboard/posts/create?format=recipe',           'from-red-400 to-rose-600',     false],
            ['event',            'Event',             'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',                                     'Scheduled events with location and map',        '/dashboard/create-event',                         'from-violet-400 to-violet-600', false],
            ['question',         'Ask Question',      'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z','Post a Q&A question for community',            '/ask-question',                                   'from-emerald-400 to-emerald-600', false],
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
        <div class="p-4 flex flex-col divide-y divide-gray-50 dark:divide-gray-800">
            @foreach($_filteredFormats as [$format, $label, $icon, $desc, $url, $gradient, $popular])
            <a href="{{ $url }}" @click="formatModal = false"
                class="flex items-center gap-4 px-3 py-3.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors cursor-pointer group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ $gradient }} shadow flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform duration-150">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $label }}</p>
                        @if($popular)
                        <span class="text-[10px] font-bold px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-full leading-none">Popular</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $desc }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-gray-400 flex-shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
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

        {{-- ── PROFILE CARD (expandable with Sign Out, FB style) ── --}}
        @auth
        <div class="bg-gray-50 dark:bg-gray-950 flex-1 overflow-y-auto pb-4">

            {{-- Profile card --}}
            <div class="mx-3 mt-3 mb-1" x-data="{ profileOpen: false }">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
                    <button @click="profileOpen = !profileOpen"
                       class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <div class="flex items-center gap-3">
                            @if(auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0 text-left">
                                <p class="font-bold text-gray-900 dark:text-white text-sm truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">View your profile</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0 transition-transform duration-200" :class="profileOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="profileOpen" class="border-t border-gray-100 dark:border-gray-700">
                        <a href="/profile/{{ auth()->user()->username ?? auth()->user()->name }}" @click="mobileMenu = false"
                           class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                            <svg class="w-5 h-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            View Profile
                        </a>
                        <a href="/account" @click="mobileMenu = false"
                           class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors border-t border-gray-100 dark:border-gray-700">
                            <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Settings
                        </a>
                        <form method="POST" action="/logout" class="border-t border-gray-100 dark:border-gray-700">
                            @csrf
                            <button class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Write post --}}
            <div class="mx-3 mb-1">
                <button @click="mobileMenu = false; formatModal = true"
                   class="w-full flex items-center gap-3 bg-white dark:bg-gray-800 rounded-2xl px-4 py-3 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                    <span class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#f97316,#ef4444)">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </span>
                    <span class="font-semibold text-gray-900 dark:text-white text-sm">Write a post</span>
                </button>
            </div>

            {{-- 2-col feature tile grid (FB Lite style) --}}
            <div class="mx-3 mb-1">
                <div class="grid grid-cols-2 gap-2">
                    {{-- Notifications --}}
                    <a href="/notifications" @click="mobileMenu = false" class="flex flex-col items-start gap-3 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <span class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#8b5cf6,#6366f1)">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">Notifications</span>
                    </a>
                    {{-- Pages --}}
                    <a href="/pages" @click="mobileMenu = false" class="flex flex-col items-start gap-3 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <span class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#f97316,#f59e0b)">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">Pages</span>
                    </a>
                    {{-- Q&A --}}
                    <a href="/questions" @click="mobileMenu = false" class="flex flex-col items-start gap-3 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <span class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#10b981,#059669)">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">Q&amp;A</span>
                    </a>
                    {{-- Saved --}}
                    <a href="/bookmarks" @click="mobileMenu = false" class="flex flex-col items-start gap-3 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <span class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#7c3aed,#a855f7)">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">Saved</span>
                    </a>
                    {{-- Events --}}
                    <a href="/events" @click="mobileMenu = false" class="flex flex-col items-start gap-3 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <span class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">Events</span>
                    </a>
                    {{-- Recipes --}}
                    <a href="/recipe" @click="mobileMenu = false" class="flex flex-col items-start gap-3 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <span class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#f97316,#ef4444)">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">Recipes</span>
                    </a>
                    {{-- Membership --}}
                    <a href="/membership" @click="mobileMenu = false" class="flex flex-col items-start gap-3 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <span class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#f59e0b,#eab308)">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">Membership</span>
                    </a>
                    {{-- Dashboard --}}
                    <a href="/dashboard" @click="mobileMenu = false" class="flex flex-col items-start gap-3 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <span class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#1a73e8,#0d47a1)">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">Dashboard</span>
                    </a>
                    {{-- Support --}}
                    <a href="/support" @click="mobileMenu = false" class="flex flex-col items-start gap-3 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <span class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#14b8a6,#06b6d4)">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">Support</span>
                    </a>
                    {{-- Account --}}
                    <a href="/account" @click="mobileMenu = false" class="flex flex-col items-start gap-3 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <span class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#64748b,#475569)">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">Account</span>
                    </a>
                    @if(auth()->user()->isAdmin())
                    {{-- Admin Panel --}}
                    <a href="/admin" @click="mobileMenu = false" class="flex flex-col items-start gap-3 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <span class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#ef4444,#b91c1c)">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">Admin Panel</span>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Bottom: dark mode + logout --}}
            <div class="mx-3 mb-4 mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
                <button @click="toggleDark()"
                    class="w-full flex items-center gap-3 px-4 py-3.5 text-sm font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                    <svg x-show="!dark" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="dark" class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0z"/></svg>
                    <span x-text="dark ? 'Light Mode' : 'Dark Mode'"></span>
                </button>
                <form method="POST" action="/logout">
                    @csrf
                    <button class="w-full flex items-center gap-3 px-4 py-3.5 text-sm font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Sign Out
                    </button>
                </form>
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

        {{-- Guest: navigation links + dark mode --}}
        @guest
        <nav class="flex-1 py-3 overflow-y-auto">
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
            @else
                @foreach($navItems as $nav)
                @php $navUrl = $nav->resolvedUrl(); @endphp
                <a href="{{ $navUrl }}" @click="mobileMenu = false"
                    class="flex items-center justify-between px-5 py-3 text-sm font-medium {{ request()->is(ltrim($navUrl,'/')) ? 'text-brand-600 bg-brand-50 dark:bg-brand-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' }} transition-colors">
                    {{ $nav->label }}
                </a>
                @endforeach
            @endif
        </nav>
        <div class="border-t border-gray-100 dark:border-gray-800 px-5 py-4 flex-shrink-0">
            <button @click="toggleDark()"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <svg x-show="!dark" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="dark" class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0z"/></svg>
                <span x-text="dark ? 'Light Mode' : 'Dark Mode'"></span>
            </button>
        </div>
        @endguest
    </div>
</div>

{{-- MAIN CONTENT --}}
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-28 lg:pb-8">
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
                    <li><a href="/register" class="hover:text-white transition-colors">Become an Author</a></li>
                    <li><a href="/about" class="hover:text-white transition-colors">About</a></li>
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

{{-- ═══════════════════════════════════════════════════════
     MOBILE BOTTOM TAB BAR
═══════════════════════════════════════════════════════ --}}
@php
$tabHome    = request()->is('/');
$tabSearch  = request()->is('search*');
$tabSaved   = request()->is('notifications*') || request()->is('bookmarks*');
$tabMe      = request()->is('profile*') || request()->is('dashboard*') || request()->is('account*');
@endphp
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-gray-900/95 backdrop-blur border-t border-gray-100 dark:border-gray-800 flex items-end"
    style="padding-bottom: env(safe-area-inset-bottom, 4px)">

    {{-- Home --}}
    <a href="/" class="flex-1 flex flex-col items-center gap-0.5 pt-2 pb-1 min-w-0 group">
        <div class="w-6 h-6 flex items-center justify-center">
            <svg class="w-6 h-6 transition-colors {{ $tabHome ? 'text-brand-600' : 'text-gray-400 dark:text-gray-500 group-active:text-brand-500' }}"
                fill="{{ $tabHome ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </div>
        <span class="text-[10px] font-semibold {{ $tabHome ? 'text-brand-600' : 'text-gray-400 dark:text-gray-500' }}">Home</span>
        @if($tabHome)<span class="w-1 h-1 rounded-full bg-brand-500 mt-0.5 -mb-0.5"></span>@endif
    </a>

    {{-- Explore --}}
    <a href="/search" class="flex-1 flex flex-col items-center gap-0.5 pt-2 pb-1 min-w-0 group">
        <div class="w-6 h-6 flex items-center justify-center">
            <svg class="w-6 h-6 transition-colors {{ $tabSearch ? 'text-brand-600' : 'text-gray-400 dark:text-gray-500 group-active:text-brand-500' }}"
                fill="{{ $tabSearch ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <span class="text-[10px] font-semibold {{ $tabSearch ? 'text-brand-600' : 'text-gray-400 dark:text-gray-500' }}">Explore</span>
        @if($tabSearch)<span class="w-1 h-1 rounded-full bg-brand-500 mt-0.5 -mb-0.5"></span>@endif
    </a>

    {{-- Write (centre — elevated) --}}
    <div class="flex-1 flex flex-col items-center justify-end pb-1 min-w-0 -mt-4">
        @auth
        <button @click="formatModal = true"
            class="w-13 h-13 rounded-full bg-brand-600 hover:bg-brand-700 active:scale-95 flex items-center justify-center shadow-lg shadow-brand-500/30 transition-all"
            style="width:52px;height:52px" title="Write">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
        </button>
        @else
        <a href="/register"
            class="w-13 h-13 rounded-full bg-brand-600 hover:bg-brand-700 active:scale-95 flex items-center justify-center shadow-lg shadow-brand-500/30 transition-all"
            style="width:52px;height:52px" title="Write">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
        </a>
        @endauth
        <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 mt-0.5">Write</span>
    </div>

    {{-- Saved --}}
    @auth
    <a href="/notifications" class="flex-1 flex flex-col items-center gap-0.5 pt-2 pb-1 min-w-0 group"
        x-data="{ cnt: 0 }" x-init="fetch('/notifications/count').then(r=>r.json()).then(d=>cnt=d.count).catch(()=>{})">
        <div class="w-6 h-6 flex items-center justify-center relative">
            <svg class="w-6 h-6 transition-colors {{ $tabSaved ? 'text-brand-600' : 'text-gray-400 dark:text-gray-500 group-active:text-brand-500' }}"
                fill="{{ $tabSaved ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span x-show="cnt > 0" x-text="cnt > 9 ? '9+' : cnt"
                class="absolute -top-1 -right-1.5 bg-red-500 text-white text-[8px] rounded-full min-w-[14px] h-3.5 flex items-center justify-center px-0.5 font-bold leading-none"></span>
        </div>
        <span class="text-[10px] font-semibold {{ $tabSaved ? 'text-brand-600' : 'text-gray-400 dark:text-gray-500' }}">Inbox</span>
        @if($tabSaved)<span class="w-1 h-1 rounded-full bg-brand-500 mt-0.5 -mb-0.5"></span>@endif
    </a>
    @else
    <a href="/login" class="flex-1 flex flex-col items-center gap-0.5 pt-2 pb-1 min-w-0 group">
        <div class="w-6 h-6 flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-400 dark:text-gray-500 group-active:text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-500">Inbox</span>
    </a>
    @endauth

    {{-- Me --}}
    @auth
    <a href="/profile/{{ auth()->user()->username ?? auth()->user()->id }}" class="flex-1 flex flex-col items-center gap-0.5 pt-2 pb-1 min-w-0 group">
        <div class="w-6 h-6 flex items-center justify-center">
            @if(auth()->user()->avatar_url ?? false)
            <img src="{{ auth()->user()->avatar_url }}" class="w-6 h-6 rounded-full object-cover ring-2 {{ $tabMe ? 'ring-brand-500' : 'ring-transparent' }}">
            @else
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black text-white ring-2 {{ $tabMe ? 'ring-brand-500' : 'ring-transparent' }} transition-all"
                style="background:hsl({{ crc32(auth()->user()->name) % 360 }},60%,55%)">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            @endif
        </div>
        <span class="text-[10px] font-semibold {{ $tabMe ? 'text-brand-600' : 'text-gray-400 dark:text-gray-500' }}">Me</span>
        @if($tabMe)<span class="w-1 h-1 rounded-full bg-brand-500 mt-0.5 -mb-0.5"></span>@endif
    </a>
    @else
    <a href="/login" class="flex-1 flex flex-col items-center gap-0.5 pt-2 pb-1 min-w-0 group">
        <div class="w-6 h-6 flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-400 dark:text-gray-500 group-active:text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-500">Sign In</span>
    </a>
    @endauth

</nav>

@stack('scripts')
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
}
</script>
</body>
</html>
