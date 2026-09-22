@php
    $__s        = file_exists(storage_path('app/site_settings.json'))
                    ? (json_decode(file_get_contents(storage_path('app/site_settings.json')), true) ?? [])
                    : [];
    $siteName   = $__s['site_name']   ?? config('app.name', 'nkhoj');
    $taglineNe  = $__s['tagline_ne']  ?? 'नेपाली समाचार';
    $taglineEn  = $__s['tagline_en']  ?? '';
    $showHome   = ($__s['nav_home_page_link'] ?? 'show') === 'show';
    try {
        $navItems = \App\Models\Core\NavigationItem::where('is_active', true)
                        ->whereNull('parent_id')
                        ->where('language', 'en')
                        ->orderBy('sort_order')
                        ->with('children')
                        ->get();
    } catch (\Exception $e) {
        $navItems = collect();
    }
    try {
        $useFallback = $navItems->isEmpty();
    } catch (\Exception $e) {
        $useFallback = true;
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'My Account') — {{ $siteName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            if (localStorage.getItem('siteTheme') === 'dark') {
                document.getElementById('html-root').classList.add('dark');
            }
        })();
    </script>
    <style>
        .nav-item { display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border-radius:0.75rem; font-size:0.875rem; font-weight:500; color:#374151; text-decoration:none; transition:background 0.15s,color 0.15s; }
        .nav-item:hover { background:#f3f4f6; }
        .nav-item.active { background:#eef2ff; color:#4338ca; }
        .dark .nav-item { color:#d1d5db; }
        .dark .nav-item:hover { background:rgba(55,65,81,0.5); }
        .dark .nav-item.active { background:rgba(99,102,241,0.15); color:#a5b4fc; }
    </style>
    @stack('head')
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 font-sans antialiased transition-colors duration-200"
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

{{-- Impersonation banner --}}
@if(session('impersonating_admin_id'))
<div class="bg-amber-500 text-white px-4 py-2 flex items-center justify-center gap-4 text-sm font-medium z-50 sticky top-0">
    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
    <span>You are logged in as <strong>{{ auth()->user()->name }}</strong></span>
    <a href="/admin/users/stop-impersonating"
       class="bg-white/20 hover:bg-white/30 transition-colors px-3 py-1 rounded-full text-xs font-bold">
        Return to Admin
    </a>
</div>
@endif

@php
$__navTitle = match(true) {
    request()->is('account/personal-info')  => 'Personal Info',
    request()->is('account/security')       => 'Security & Sign-in',
    request()->is('account/subscriptions')  => 'Subscriptions',
    request()->is('account/sessions')       => 'Sessions',
    request()->is('account/tokens*')        => 'API Tokens',
    request()->is('account/notifications')  => 'Notifications',
    request()->is('account/data-privacy')   => 'Data & Privacy',
    request()->is('account/recovery*')      => 'Account Recovery',
    default                                 => 'Home',
};
@endphp
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    {{-- Mobile hamburger nav (hidden on lg+) --}}
    <div class="lg:hidden mb-4" x-data="{ open: false }">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                @if(auth()->user()->avatar_url)
                <img src="{{ auth()->user()->avatar_url }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0 border border-gray-100 dark:border-gray-600" alt="">
                @else
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                @endif
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 truncate">{{ auth()->user()->displayName() }}</p>
                    <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ $__navTitle }}</p>
                </div>
            </div>
            <button @click="open = !open"
                class="w-9 h-9 flex flex-col items-center justify-center gap-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex-shrink-0 ml-2"
                :class="{ 'bg-gray-100 dark:bg-gray-700': open }">
                <span class="block w-5 h-0.5 bg-gray-600 dark:bg-gray-300 transition-all duration-200" :class="{ 'rotate-45 translate-y-2': open }"></span>
                <span class="block w-5 h-0.5 bg-gray-600 dark:bg-gray-300 transition-all duration-200" :class="{ 'opacity-0 scale-x-0': open }"></span>
                <span class="block w-5 h-0.5 bg-gray-600 dark:bg-gray-300 transition-all duration-200" :class="{ '-rotate-45 -translate-y-2': open }"></span>
            </button>
        </div>
        <div x-show="open" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="mt-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xl p-3">
            <div class="text-center px-3 py-4 mb-1">
                @if(auth()->user()->avatar_url)
                <img src="{{ auth()->user()->avatar_url }}" class="w-14 h-14 rounded-full object-cover mx-auto mb-2" alt="">
                @else
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white text-xl font-bold mx-auto mb-2">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                @endif
                <p class="font-bold text-gray-900 dark:text-white text-sm">{{ auth()->user()->displayName() }}</p>
                <p class="text-xs text-gray-400 mt-0.5 break-all">{{ auth()->user()->email }}</p>
                <span class="inline-block mt-1.5 text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-medium">{{ auth()->user()->roleLabel() }}</span>
            </div>
            <nav class="space-y-0.5" @click="open = false">
                <a href="/account" class="nav-item {{ request()->is('account') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Home
                </a>
                <a href="/account/personal-info" class="nav-item {{ request()->is('account/personal-info') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Personal Info
                </a>
                <a href="/account/security" class="nav-item {{ request()->is('account/security') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Security & Sign-in
                </a>
                <a href="/account/subscriptions" class="nav-item {{ request()->is('account/subscriptions') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Subscriptions
                </a>
                <a href="/account/sessions" class="nav-item {{ request()->is('account/sessions') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    Sessions
                </a>
                <a href="/account/tokens" class="nav-item {{ request()->is('account/tokens*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    API Tokens
                </a>
                <a href="/account/notifications" class="nav-item {{ request()->is('account/notifications') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notifications
                </a>
                <a href="/account/data-privacy" class="nav-item {{ request()->is('account/data-privacy') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Data & Privacy
                </a>
                <a href="/account/linked-apps" class="nav-item {{ request()->is('account/linked-apps') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Linked Accounts
                </a>
                <a href="/account/recovery" class="nav-item {{ request()->is('account/recovery*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Account Recovery
                </a>
                <div class="border-t border-gray-100 dark:border-gray-700 my-2"></div>
                <form method="POST" action="/logout">
                    @csrf
                    <button class="nav-item w-full text-left text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Sign Out
                    </button>
                </form>
            </nav>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Sidebar (desktop only) --}}
        <aside class="hidden lg:block w-64 flex-shrink-0">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-3">
                {{-- User card --}}
                <div class="text-center px-3 py-5 mb-2">
                    @if(auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" class="w-16 h-16 rounded-full object-cover mx-auto mb-3" alt="">
                    @else
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white text-2xl font-bold mx-auto mb-3">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    @endif
                    <p class="font-bold text-gray-900 dark:text-white text-sm">{{ auth()->user()->displayName() }}</p>
                    <p class="text-xs text-gray-400 mt-0.5 break-all">{{ auth()->user()->email }}</p>
                    <span class="inline-block mt-2 text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-medium">
                        {{ auth()->user()->roleLabel() }}
                    </span>
                </div>

                <nav class="space-y-0.5">
                    <a href="/account" class="nav-item {{ request()->is('account') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Home
                    </a>
                    <a href="/account/personal-info" class="nav-item {{ request()->is('account/personal-info') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Personal Info
                    </a>
                    <a href="/account/security" class="nav-item {{ request()->is('account/security') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Security & Sign-in
                    </a>
                    <a href="/account/subscriptions" class="nav-item {{ request()->is('account/subscriptions') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Subscriptions
                    </a>
                    <a href="/account/sessions" class="nav-item {{ request()->is('account/sessions') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                        Sessions
                    </a>
                    <a href="/account/tokens" class="nav-item {{ request()->is('account/tokens*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        API Tokens
                    </a>
                    <a href="/account/notifications" class="nav-item {{ request()->is('account/notifications') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        Notifications
                    </a>
                    <a href="/account/data-privacy" class="nav-item {{ request()->is('account/data-privacy') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Data & Privacy
                    </a>
                    <a href="/account/linked-apps" class="nav-item {{ request()->is('account/linked-apps') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        Linked Accounts
                    </a>
                    <a href="/account/recovery" class="nav-item {{ request()->is('account/recovery*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Account Recovery
                    </a>

                    <div class="border-t border-gray-100 dark:border-gray-700 my-2"></div>

                    <form method="POST" action="/logout">
                        @csrf
                        <button class="nav-item w-full text-left text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Sign Out
                        </button>
                    </form>
                </nav>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 min-w-0">
            @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-xl text-sm">
                @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
            @endif
            @yield('main')
        </main>
    </div>
</div>

</body>
</html>
