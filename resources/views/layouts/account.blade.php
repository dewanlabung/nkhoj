<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Account') — Dewanlabung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { brand: { 50:'#eff6ff',100:'#dbeafe',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8' } } } }
        };
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        .nav-item { display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border-radius:0.75rem; font-size:0.875rem; font-weight:500; color:#374151; text-decoration:none; transition:background 0.15s,color 0.15s; }
        .nav-item:hover { background:#f3f4f6; }
        .nav-item.active { background:#eff6ff; color:#1d4ed8; }
        @media (prefers-color-scheme: dark) {
            .nav-item { color:#d1d5db; }
            .nav-item:hover { background:rgba(55,65,81,0.5); }
            .nav-item.active { background:rgba(59,130,246,0.15); color:#93c5fd; }
        }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">

{{-- Top bar --}}
<header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Dewanlabung
            </a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <span class="text-sm font-semibold text-gray-900 dark:text-white">My Account</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500 dark:text-gray-400 hidden sm:block">{{ auth()->user()->email }}</span>
            @if(auth()->user()->avatar_url)
            <img src="{{ auth()->user()->avatar_url }}" class="w-8 h-8 rounded-full object-cover" alt="">
            @else
            <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white text-sm font-bold">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            @endif
        </div>
    </div>
</header>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Sidebar --}}
        <aside class="w-full lg:w-64 flex-shrink-0">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-3">
                {{-- User card --}}
                <div class="text-center px-3 py-5 mb-2">
                    @if(auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" class="w-16 h-16 rounded-full object-cover mx-auto mb-3" alt="">
                    @else
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-2xl font-bold mx-auto mb-3">
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
                    <a href="/account/privacy" class="nav-item {{ request()->is('account/privacy') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Data & Privacy
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
