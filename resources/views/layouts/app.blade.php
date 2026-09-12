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


@include('partials.site-header')

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
