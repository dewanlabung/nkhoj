@php
    $__s      = file_exists(storage_path('app/site_settings.json'))
                  ? (json_decode(file_get_contents(storage_path('app/site_settings.json')), true) ?? [])
                  : [];
    $siteName = $__s['site_name'] ?? config('app.name', 'nkhoj');
@endphp
<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ $siteName }} Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: { 50:'#eef2ff',100:'#e0e7ff',500:'#6366f1',600:'#4f46e5',700:'#4338ca' }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        html, body { height: 100%; overflow: hidden; }
        @media (max-width: 1023px) {
            html, body { overflow: auto; height: auto; min-height: 100%; }
            .flex.h-screen { height: auto; min-height: 100svh; }
        }

        /* ── Sidebar nav links ───────────────────────────────── */
        .sl { display:flex; align-items:center; gap:10px; padding:7px 12px; border-radius:8px; font-size:13.5px; color:#9ca3af; transition:all .15s; cursor:pointer; }
        .sl:hover { color:#fff; background:rgba(255,255,255,.08); }
        .sl.on { color:#fff; background:rgba(255,255,255,.13); font-weight:500; }
        .sl svg { width:16px; height:16px; flex-shrink:0; opacity:.7; }
        .sl.on svg { opacity:1; }

        /* ── Global transitions ──────────────────────────────── */
        body, aside, header, .bg-white, .bg-gray-50, .bg-gray-100, main { transition: background-color .2s, color .2s; }

        /* ══════════════════════════════════════════════════════
           DARK THEME — Varient-style cool navy palette
           ══════════════════════════════════════════════════════ */

        /* Sidebar: deep navy, distinctly darker than content */
        html.dark aside { background: #090d18 !important; }
        html.dark aside .border-white\/10 { border-color: rgba(255,255,255,.05) !important; }

        /* Sidebar section labels — barely-visible navy */
        html.dark nav > .px-2\.5 > p.text-gray-500,
        html.dark nav p.uppercase { color: #1e3356 !important; }

        /* Sidebar links — dark palette */
        html.dark .sl             { color: #3d5572; }
        html.dark .sl:hover       { color: #8db0d8 !important; background: rgba(255,255,255,.05) !important; }
        html.dark .sl.on          { color: #c5daf5 !important; background: rgba(99,102,241,.16) !important; font-weight:600; border-left:2px solid #6366f1; margin-left:-2px; padding-left:14px; }
        html.dark .sl.on svg      { opacity:1; color:#a5c0f0; }

        /* Submenu tree-line in dark */
        html.dark .border-white\/10 { border-color: rgba(255,255,255,.06) !important; }

        /* Header bar in dark */
        html.dark header { background: #0f1825 !important; border-bottom-color: rgba(255,255,255,.06) !important; }

        /* Cards / Panels — cool blue-gray */
        html.dark [class~="dark:bg-gray-800"]       { background: #141f33 !important; }
        html.dark [class~="bg-white"][class~="dark:bg-gray-800"] { background: #141f33 !important; }

        /* Table/form control raised bg */
        html.dark [class~="dark:bg-gray-700"]       { background: #1d2d47 !important; }

        /* Borders — ultra-subtle in dark */
        html.dark [class~="dark:border-gray-700"]   { border-color: rgba(255,255,255,.07) !important; }
        html.dark [class~="dark:border-gray-600"]   { border-color: rgba(255,255,255,.10) !important; }

        /* Dashed dividers — like Varient */
        html.dark [class~="dark:divide-gray-700/40"] > :not([hidden]) ~ :not([hidden]) { border-color: rgba(255,255,255,.06) !important; border-style: dashed !important; }
        html.dark [class~="dark:divide-gray-700"]    > :not([hidden]) ~ :not([hidden]) { border-color: rgba(255,255,255,.07) !important; }

        /* Inline dashed border helper */
        html.dark [class~="dark:border-gray-700/60"] { border-color: rgba(255,255,255,.07) !important; border-style: dashed !important; }

        /* Row hover in dark */
        html.dark [class~="dark:hover:bg-gray-700/30"]:hover { background: rgba(255,255,255,.025) !important; }
        html.dark [class~="dark:hover:bg-gray-700/50"]:hover { background: rgba(255,255,255,.04) !important; }

        /* Chip / badge bg in dark */
        html.dark [class~="dark:bg-gray-700/50"]    { background: rgba(29,45,71,.6) !important; }

        /* Input bg in dark (inherits dark:bg-gray-700 override) */

        /* Text hierarchy in dark — cool blue-white */
        html.dark [class~="dark:text-white"]         { color: #d8e8f8 !important; }
        html.dark [class~="dark:text-gray-200"]      { color: #b8cce4 !important; }
        html.dark [class~="dark:text-gray-300"]      { color: #8aaac8 !important; }
        html.dark [class~="dark:text-gray-400"]      { color: #5a7898 !important; }
        html.dark [class~="dark:text-gray-500"]      { color: #3d5572 !important; }

        /* Accent colors pass-through (brand-600, blue-*, green-*, etc.) — keep as-is */

        /* ══════════════════════════════════════════════════════
           LIGHT THEME — Varient-style clean white
           ══════════════════════════════════════════════════════ */

        /* Slightly cooler, richer page background */
        html:not(.dark) body,
        html:not(.dark) .bg-gray-100 { background: #f2f5f9; }

        /* Cards: add depth with shadow ring instead of flat border */
        html:not(.dark) .shadow-sm.rounded-xl {
            box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 0 0 1px rgba(0,0,0,.04) !important;
            border-color: rgba(0,0,0,.05) !important;
        }
    </style>
    @stack('head')
    <script>
        // Apply dark mode before paint to avoid flash
        (function() {
            if (localStorage.getItem('adminDark') === '1') {
                document.getElementById('html-root').classList.add('dark');
            }
        })();
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 font-sans antialiased" style="font-family:Inter,system-ui,sans-serif"
    x-data="{
        dark: localStorage.getItem('adminDark') === '1',
        sidebarOpen: false,
        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem('adminDark', this.dark ? '1' : '0');
            const root = document.getElementById('html-root');
            root.classList.toggle('dark', this.dark);
            root.dispatchEvent(new Event('classChange'));
        }
    }">
<div class="flex h-screen overflow-hidden">

    {{-- ─── SIDEBAR ─────────────────────────────────────────── --}}
    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
        class="lg:hidden fixed inset-0 bg-black/50 z-40"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    <aside class="w-60 bg-gray-900 flex-shrink-0 flex flex-col overflow-y-auto
        fixed lg:relative inset-y-0 left-0 z-50
        -translate-x-full lg:translate-x-0 transition-transform duration-250
        lg:!transform-none"
        :class="sidebarOpen ? '!translate-x-0' : ''">

        <div class="flex items-center justify-between gap-2.5 px-5 py-4 border-b border-white/10 flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <span class="text-xl font-black text-white tracking-tight">{{ $siteName }}</span>
                <span class="text-[10px] font-semibold text-gray-400 bg-white/10 px-2 py-0.5 rounded-full uppercase tracking-wide">Admin</span>
            </div>
            {{-- Close button (mobile only) --}}
            <button @click="sidebarOpen = false"
                class="lg:hidden w-7 h-7 flex items-center justify-center rounded-full bg-white/10 text-gray-400 hover:bg-white/20 transition-colors text-lg font-light flex-shrink-0">
                ×
            </button>
        </div>

        <nav class="flex-1 px-2.5 py-4 space-y-0.5 text-sm">

            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest px-3 pt-1 pb-1.5">Dashboard</p>
            <a href="/admin" class="sl {{ request()->is('admin') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Home
            </a>
            <a href="/admin/analytics" class="sl {{ request()->is('admin/analytics') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Analytics
            </a>
            <a href="/admin/themes" class="sl {{ request()->is('admin/themes*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                Themes
            </a>
            <a href="/admin/navigation" class="sl {{ request()->is('admin/navigation*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                Navigation
            </a>
            <a href="/admin/pages" class="sl {{ request()->is('admin/pages*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Pages
            </a>

            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest px-3 pt-4 pb-1.5">Content</p>
            <button onclick="document.getElementById('format-chooser').classList.remove('hidden')"
                class="sl w-full text-left">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Post
            </button>

            {{-- Collapsible Posts submenu --}}
            <div x-data="{ postsOpen: {{ request()->is('admin/posts*') ? 'true' : 'false' }} }">
                <button @click="postsOpen = !postsOpen"
                    class="sl w-full justify-between {{ request()->is('admin/posts*') ? 'on' : '' }}">
                    <span class="flex items-center gap-2.5">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Posts
                    </span>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="postsOpen ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="postsOpen" x-cloak class="ml-4 mt-0.5 space-y-0.5 border-l border-white/10 pl-3">
                    <a href="/admin/posts" class="sl text-[12.5px] {{ request()->is('admin/posts') && !request('status') ? 'on' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h7"/></svg>
                        All Posts
                    </a>
                    <a href="/admin/posts?status=draft" class="sl text-[12.5px] {{ request('status') === 'draft' ? 'on' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pending Posts
                    </a>
                    <a href="/admin/posts?status=scheduled" class="sl text-[12.5px] {{ request('status') === 'scheduled' ? 'on' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Scheduled Posts
                    </a>
                    <a href="/admin/posts?post_format=event" class="sl text-[12.5px] {{ request('post_format') === 'event' ? 'on' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Events
                    </a>
                    <a href="/admin/posts?status=archived" class="sl text-[12.5px] {{ request('status') === 'archived' ? 'on' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        Drafts / Archived
                    </a>
                    <a href="/admin/questions" class="sl text-[12.5px] {{ request()->is('admin/questions*') ? 'on' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Questions
                    </a>
                </div>
            </div>

            <a href="/admin/categories" class="sl {{ request()->is('admin/categories*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Categories
            </a>
            <a href="/admin/tags" class="sl {{ request()->is('admin/tags*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                Tags
            </a>
            <a href="/admin/polls" class="sl {{ request()->is('admin/polls*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Polls
            </a>
            <a href="/admin/widgets" class="sl {{ request()->is('admin/widgets*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                Widgets
            </a>

            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest px-3 pt-4 pb-1.5">Media</p>
            <a href="/admin/media" class="sl {{ request()->is('admin/media*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Media Library
            </a>
            <a href="/admin/ads" class="sl {{ request()->is('admin/ads*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Ad Spaces
            </a>

            <a href="/admin/memberships" class="sl {{ request()->is('admin/memberships*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/></svg>
                Memberships
            </a>

            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest px-3 pt-4 pb-1.5">Community</p>

            {{-- Social Pages collapsible submenu --}}
            <div x-data="{ pagesOpen: {{ request()->is('admin/social-pages*') || request()->is('admin/page-categories*') ? 'true' : 'false' }} }">
                <button @click="pagesOpen = !pagesOpen"
                    class="sl w-full justify-between {{ request()->is('admin/social-pages*') || request()->is('admin/page-categories*') ? 'on' : '' }}">
                    <span class="flex items-center gap-2.5">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Social Pages
                    </span>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="pagesOpen ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="pagesOpen" x-cloak class="ml-4 mt-0.5 space-y-0.5 border-l border-white/10 pl-3">
                    <a href="/admin/social-pages" class="sl text-[12.5px] {{ request()->is('admin/social-pages') ? 'on' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        All Pages
                    </a>
                    <a href="/admin/social-pages?status=pending_verification" class="sl text-[12.5px] {{ request()->is('admin/social-pages') && request('status') === 'pending_verification' ? 'on' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        Verifications
                    </a>
                    <a href="/admin/page-categories" class="sl text-[12.5px] {{ request()->is('admin/page-categories*') ? 'on' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Page Categories
                    </a>
                </div>
            </div>

            <a href="/admin/comments" class="sl {{ request()->is('admin/comments*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                Comments
            </a>
            <a href="/admin/contacts" class="sl {{ request()->is('admin/contacts*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Contact Messages
            </a>
            <a href="/admin/support" class="sl {{ request()->is('admin/support*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Support Center
            </a>
            <a href="/admin/newsletter" class="sl {{ request()->is('admin/newsletter*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                Newsletter
            </a>

            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest px-3 pt-4 pb-1.5">Users & Permissions</p>
            <a href="/admin/users" class="sl {{ request()->is('admin/users*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Users
            </a>
            <a href="/admin/roles" class="sl {{ request()->is('admin/roles*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Roles &amp; Permissions
            </a>
            <a href="/admin/badges" class="sl {{ request()->is('admin/badges*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                Badges
            </a>

            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest px-3 pt-4 pb-1.5">System Tools</p>
            <a href="/admin/content-settings" class="sl {{ request()->is('admin/content-settings*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                Content Settings
            </a>
            <a href="/admin/seo" class="sl {{ request()->is('admin/seo*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                SEO Tools
            </a>
            <a href="/admin/storage" class="sl {{ request()->is('admin/storage*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                Storage
            </a>
            <a href="/admin/google-news" class="sl {{ request()->is('admin/google-news*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                Google News
            </a>
            <a href="/admin/rss-feeds" class="sl {{ request()->is('admin/rss-feeds*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/></svg>
                RSS Feeds
            </a>
            <a href="/admin/ai-content" class="sl {{ request()->is('admin/ai-content*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                AI Content
            </a>
            <a href="/admin/email-settings" class="sl {{ request()->is('admin/email-settings*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Email Settings
            </a>
            <a href="/admin/security" class="sl {{ request()->is('admin/security*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Security
            </a>
            <a href="/admin/cache" class="sl {{ request()->is('admin/cache*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                Cache System
            </a>
            <a href="/admin/queue-settings" class="sl {{ request()->is('admin/queue-settings*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Queue Settings
            </a>

            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest px-3 pt-4 pb-1.5">Settings</p>
            <a href="/admin/settings" class="sl {{ request()->is('admin/settings*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Global Settings
            </a>
            <a href="/admin/localized-settings" class="sl {{ request()->is('admin/localized-settings*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                Localized Settings
            </a>
            <a href="/admin/languages" class="sl {{ request()->is('admin/languages*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                Language Settings
            </a>
        </nav>

        <div class="px-2.5 pb-4 pt-2 border-t border-white/10 flex-shrink-0 space-y-1">
            <a href="/admin/backup" class="sl w-full">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Database Backup
            </a>
            <a href="/admin/deploy" class="sl w-full {{ request()->is('admin/deploy*') ? 'on' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Deploy
            </a>
            <a href="/" class="sl w-full">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Site
            </a>
        </div>
    </aside>

    {{-- ─── MAIN ──────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Top bar --}}
        <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 h-14 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                {{-- Mobile hamburger --}}
                <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-base font-bold text-gray-900 dark:text-white">@yield('title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-2">

                {{-- Dark mode toggle --}}
                <button @click="toggleDark()"
                    class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :title="dark ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
                    <svg x-show="!dark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="dark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                {{-- View site --}}
                <a href="/" target="_blank"
                    class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    title="View Site">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>

                {{-- User dropdown --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open"
                        class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="text-left hidden sm:block">
                            <p class="text-xs font-semibold text-gray-900 dark:text-white leading-none">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-gray-400 leading-none mt-0.5">
                                {{ ucfirst(auth()->user()->role) }}
                                @if(auth()->user()->role === 'admin')
                                <span class="text-brand-500 font-bold">· Super Admin</span>
                                @endif
                            </p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Dropdown menu --}}
                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="absolute right-0 top-12 w-52 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden z-50">

                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-xs font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-gray-400 truncate">{{ auth()->user()->email }}</p>
                        </div>

                        <div class="py-1">
                            <a href="/profile/{{ auth()->user()->username }}" target="_blank"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                View Profile
                            </a>
                            <a href="/account/settings"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Account Settings
                            </a>
                            <a href="/account/password"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                Change Password
                            </a>
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-700 py-1">
                            <form method="POST" action="/logout">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-left">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto overflow-x-hidden p-6 pb-24 lg:pb-6 bg-gray-50 dark:bg-gray-900">
            @if(session('success'))
            <div class="mb-5 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-lg text-sm">
                ✅ {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-5 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-lg text-sm">
                ❌ {{ session('error') }}
            </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
{{-- ── Post Format Chooser Modal ────────────────────────── --}}
<div id="format-chooser" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex-shrink-0">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Choose a Post Format</h2>
                <p class="text-sm text-brand-500 mt-0.5">Choose the type of content you want to create</p>
            </div>
            <button onclick="document.getElementById('format-chooser').classList.add('hidden')"
                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-4 overflow-y-auto">
            @php
            // [key, label, icon-path, desc, url, gradient, popular]
            $formats = [
                ['article',          'Article',          'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',         'An article with images and embed videos',        '/dashboard/posts/create',                       'from-blue-400 to-blue-600',     true],
                ['sorted_list',       'Sorted List',      'M4 6h16M4 10h16M4 14h16M4 18h16',                                                                                                'A list-based article',                           '/dashboard/posts/create?format=sorted_list',    'from-orange-400 to-orange-600', false],
                ['table_of_contents', 'Table of Contents','M4 6h16M4 10h16M4 14h7',                                                                                                         'List of links based on headings',                '/dashboard/posts/create?format=table_of_contents','from-teal-400 to-teal-600',    false],
                ['trivia_quiz',       'Trivia Quiz',      'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z','Quizzes with right and wrong answers',          '/dashboard/posts/create?format=trivia_quiz',    'from-yellow-400 to-amber-500',  false],
                ['personality_quiz',  'Personality Quiz', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2','Quizzes with custom results',                   '/dashboard/posts/create?format=personality_quiz','from-purple-400 to-purple-600', false],
                ['poll',              'Poll',             'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z','Get user opinions about something',             '/dashboard/posts/create?format=poll',           'from-indigo-400 to-indigo-600', false],
                ['recipe',            'Recipe',           'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z','A list of ingredients and directions',           '/dashboard/posts/create?format=recipe',         'from-red-400 to-rose-600',     false],
                ['event',             'Event',            'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',                                        'Scheduled events with location and map',         '/dashboard/create-event',                       'from-violet-400 to-violet-600', false],
                ['question',          'Ask Question',     'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z','Post a Q&A question for community',             '/ask-question',                                 'from-emerald-400 to-emerald-600',false],
            ];
            // Filter by content settings toggles
            try {
                $_sp = storage_path('app/site_settings.json');
                $_ss = file_exists($_sp) ? (json_decode(file_get_contents($_sp), true) ?? []) : [];
            } catch (\Exception $_e) { $_ss = []; }
            $_fe = $_ss['formats_enabled'] ?? null;
            $formats = array_values(array_filter($formats, function($f) use ($_fe) {
                if ($f[0] === 'article') return true;
                if ($f[0] === 'question') return true;
                if ($_fe === null) return true;
                return !empty($_fe[$f[0]]);
            }));
            @endphp
            <div class="flex flex-col divide-y divide-gray-50 dark:divide-gray-700/60">
                @foreach($formats as [$fKey, $fLabel, $fIcon, $fDesc, $fUrl, $fGradient, $fPopular])
                <a href="{{ $fUrl }}"
                    class="flex items-center gap-4 px-3 py-3.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer group"
                    onclick="document.getElementById('format-chooser').classList.add('hidden')">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ $fGradient }} shadow flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform duration-150">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $fIcon }}"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $fLabel }}</p>
                            @if($fPopular)
                            <span class="text-[10px] font-bold px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-full leading-none">Popular</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $fDesc }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-gray-400 flex-shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

@stack('scripts')
</body>
</html>
