@php
    $__s        = file_exists(storage_path('app/site_settings.json'))
                    ? (json_decode(file_get_contents(storage_path('app/site_settings.json')), true) ?? [])
                    : [];
    $siteName      = $__s['site_name']   ?? config('app.name', 'nkhoj');
    $taglineNe     = $__s['tagline_ne']  ?? 'नेपाली समाचार';
    $taglineEn     = $__s['tagline_en']  ?? '';
    $siteDesc      = $__s['site_description'] ?? 'नेपालको अग्रणी समाचार र ब्लग प्लेटफर्म';
    $__activeTheme = $__s['active_theme'] ?? 'magazine';
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
    @php
        $__brandHex = $__s['appearance']['brand_color'] ?? '#6366f1';
        // Generate shade palette from base hex
        function __hexToHsl(string $hex): array {
            $hex = ltrim($hex, '#');
            $r = hexdec(substr($hex,0,2))/255; $g = hexdec(substr($hex,2,2))/255; $b = hexdec(substr($hex,4,2))/255;
            $max = max($r,$g,$b); $min = min($r,$g,$b); $l = ($max+$min)/2;
            if ($max === $min) { $h = $s = 0; } else {
                $d = $max-$min; $s = $l > 0.5 ? $d/(2-$max-$min) : $d/($max+$min);
                $h = match(true) { $max===$r => ($g-$b)/$d + ($g<$b?6:0), $max===$g => ($b-$r)/$d+2, default => ($r-$g)/$d+4 };
                $h /= 6;
            }
            return [round($h*360), round($s*100), round($l*100)];
        }
        function __hslToHex(int $h, int $s, int $l): string {
            $s/=100; $l/=100; $c=(1-abs(2*$l-1))*$s; $x=$c*(1-abs(fmod($h/60,2)-1)); $m=$l-$c/2;
            if ($h<60){$r=$c;$g=$x;$b=0;}elseif($h<120){$r=$x;$g=$c;$b=0;}elseif($h<180){$r=0;$g=$c;$b=$x;}
            elseif($h<240){$r=0;$g=$x;$b=$c;}elseif($h<300){$r=$x;$g=0;$b=$c;}else{$r=$c;$g=0;$b=$x;}
            return sprintf('#%02x%02x%02x',round(($r+$m)*255),round(($g+$m)*255),round(($b+$m)*255));
        }
        [$__h, $__s, $__l] = __hexToHsl($__brandHex);
        $__brand = [
            50  => __hslToHex($__h, max(0,$__s-20), min(98,$__l+44)),
            100 => __hslToHex($__h, max(0,$__s-10), min(96,$__l+38)),
            200 => __hslToHex($__h, $__s,            min(92,$__l+28)),
            500 => $__brandHex,
            600 => __hslToHex($__h, min(100,$__s+5), max(5,$__l-8)),
            700 => __hslToHex($__h, min(100,$__s+8), max(5,$__l-18)),
            900 => __hslToHex($__h, min(100,$__s+10),max(5,$__l-32)),
        ];
        $__customCss    = $__s['appearance']['custom_css'] ?? '';
        $__beRadius     = $__s['appearance']['border_radius'] ?? '0.75rem';
        $__beFontScale  = $__s['appearance']['font_scale']    ?? '1';
    @endphp
    <style>
        :root {
            --be-radius: {{ $__beRadius }};
            --be-font-scale: {{ $__beFontScale }};
            --be-primary: {{ $__brandHex }};
            --be-primary-50:  {{ $__brand[50] }};
            --be-primary-100: {{ $__brand[100] }};
            --be-primary-500: {{ $__brand[500] }};
            --be-primary-600: {{ $__brand[600] }};
            --be-primary-700: {{ $__brand[700] }};
        }
        /* apply --be-radius to all Tailwind rounded-* classes */
        .rounded-xl { border-radius: var(--be-radius) !important; }
        .rounded-lg { border-radius: calc(var(--be-radius) * 0.8) !important; }
        .rounded-md { border-radius: calc(var(--be-radius) * 0.6) !important; }
        .rounded-2xl { border-radius: calc(var(--be-radius) * 1.5) !important; }
        .rounded-full { border-radius: 9999px !important; }
        /* apply --be-font-scale to body text */
        body { font-size: calc(14px * var(--be-font-scale)); }
    </style>
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
                        brand: {
                            50:'{{ $__brand[50] }}',100:'{{ $__brand[100] }}',200:'{{ $__brand[200] }}',
                            500:'{{ $__brand[500] }}',600:'{{ $__brand[600] }}',700:'{{ $__brand[700] }}',900:'{{ $__brand[900] }}'
                        }
                    }
                }
            }
        }
    </script>
    @if($__customCss)
    <style id="site-custom-css">{!! $__customCss !!}</style>
    @endif
    @if(!empty($__s['ga_id']))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $__s['ga_id'] }}"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $__s['ga_id'] }}');</script>
    @endif
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .line-clamp-2 { display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }
        .line-clamp-3 { display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden; }
        @keyframes ticker { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        .ticker-track { display:flex; animation: ticker 28s linear infinite; width:max-content; }
        .ticker-track:hover { animation-play-state: paused; }

        /* ── Layout theme overrides ── */
        /* minimal, news, grid: full-width content, hide sidebar */
        [data-layout="minimal"] #home-sidebar,
        [data-layout="news"]    #home-sidebar,
        [data-layout="grid"]    #home-sidebar    { display: none !important; }
        @media (min-width: 1024px) {
            [data-layout="minimal"] #home-main,
            [data-layout="news"]    #home-main,
            [data-layout="grid"]    #home-main    { grid-column: span 4 / span 4; }
        }
        /* minimal: generous whitespace, readable type */
        [data-layout="minimal"] #home-main { max-width: 760px; margin-inline: auto; }
        /* news: compact no-gap cards */
        [data-layout="news"] .rounded-xl { border-radius: 0 !important; }
        [data-layout="news"] #home-main  { gap: 0; }
        /* grid: 2-col post cards on wider screens */
        @media (min-width: 768px) {
            [data-layout="grid"] #posts-feed-list { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        }
        /* classic: sidebar on the left */
        @media (min-width: 1024px) {
            [data-layout="classic"] #home-sidebar { order: -1; }
        }
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
<body class="bg-gray-50 dark:bg-gray-950 font-sans antialiased transition-colors duration-200" data-layout="{{ $__activeTheme }}"
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
                    @foreach((\App\Models\Blog\Category::limit(5)->get() ?? collect()) as $cat)
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
    $settingsFile  = storage_path('app/site_settings.json');
    $rawSettings   = file_exists($settingsFile) ? (json_decode(file_get_contents($settingsFile), true) ?? []) : [];
    $mobileNavCfg  = $rawSettings['mobile_nav'] ?? [
        ['slot'=>0,'icon'=>'home',  'label'=>'Home',   'url'=>'/'],
        ['slot'=>1,'icon'=>'search','label'=>'Explore','url'=>'/search'],
        ['slot'=>2,'icon'=>'write', 'label'=>'Write',  'url'=>'/write',         'type'=>'center'],
        ['slot'=>3,'icon'=>'bell',  'label'=>'Inbox',  'url'=>'/notifications'],
        ['slot'=>4,'icon'=>'user',  'label'=>'Me',     'url'=>'/profile'],
    ];
    $mobileIconSvg = \App\Domains\Pages\Http\Controllers\NavigationController::$mobileNavIcons;
@endphp
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-gray-900/95 backdrop-blur border-t border-gray-100 dark:border-gray-800 flex items-end"
    style="padding-bottom: env(safe-area-inset-bottom, 4px)">

    @foreach($mobileNavCfg as $slotIdx => $navSlot)
    @php
        $slotUrl      = $navSlot['url']   ?? '/';
        $slotLabel    = $navSlot['label'] ?? '';
        $slotIcon     = $navSlot['icon']  ?? 'home';
        $isCenter     = $slotIdx === 2;
        $iconSvgPath  = $mobileIconSvg[$slotIcon] ?? ($mobileIconSvg['home'] ?? '');
        $cleanPath    = ltrim(parse_url($slotUrl, PHP_URL_PATH) ?? '/', '/');
        $isActive     = $cleanPath === ''
                            ? request()->is('/')
                            : request()->is($cleanPath) || request()->is($cleanPath . '/*');
        $isProfileSlot = str_starts_with($slotUrl, '/profile');
        $isNotifSlot   = $slotUrl === '/notifications';
    @endphp

    @if($isCenter)
    {{-- Center: elevated circular button --}}
    <div class="flex-1 flex flex-col items-center justify-end pb-1 min-w-0 -mt-4">
        @auth
        <button @click="formatModal = true"
            class="rounded-full bg-brand-600 hover:bg-brand-700 active:scale-95 flex items-center justify-center shadow-lg shadow-brand-500/30 transition-all"
            style="width:52px;height:52px" title="{{ $slotLabel }}">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                {!! $iconSvgPath !!}
            </svg>
        </button>
        @else
        <a href="/register"
            class="rounded-full bg-brand-600 hover:bg-brand-700 active:scale-95 flex items-center justify-center shadow-lg shadow-brand-500/30 transition-all"
            style="width:52px;height:52px" title="{{ $slotLabel }}">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                {!! $iconSvgPath !!}
            </svg>
        </a>
        @endauth
        <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 mt-0.5">{{ $slotLabel }}</span>
    </div>

    @elseif($isNotifSlot && auth()->check())
    {{-- Notifications slot with unread badge --}}
    <a href="{{ $slotUrl }}" class="flex-1 flex flex-col items-center gap-0.5 pt-2 pb-1 min-w-0 group"
        x-data="{ cnt: 0 }" x-init="fetch('/notifications/count').then(r=>r.json()).then(d=>cnt=d.count).catch(()=>{})">
        <div class="w-6 h-6 flex items-center justify-center relative">
            <svg class="w-6 h-6 transition-colors {{ $isActive ? 'text-brand-600' : 'text-gray-400 dark:text-gray-500 group-active:text-brand-500' }}"
                fill="{{ $isActive ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                {!! $iconSvgPath !!}
            </svg>
            <span x-show="cnt > 0" x-text="cnt > 9 ? '9+' : cnt"
                class="absolute -top-1 -right-1.5 bg-red-500 text-white text-[8px] rounded-full min-w-[14px] h-3.5 flex items-center justify-center px-0.5 font-bold leading-none"></span>
        </div>
        <span class="text-[10px] font-semibold {{ $isActive ? 'text-brand-600' : 'text-gray-400 dark:text-gray-500' }}">{{ $slotLabel }}</span>
        @if($isActive)<span class="w-1 h-1 rounded-full bg-brand-500 mt-0.5 -mb-0.5"></span>@endif
    </a>

    @elseif($isProfileSlot && auth()->check())
    {{-- Profile slot: show user avatar --}}
    <a href="/profile/{{ auth()->user()->username ?? auth()->user()->id }}"
        class="flex-1 flex flex-col items-center gap-0.5 pt-2 pb-1 min-w-0 group">
        <div class="w-6 h-6 flex items-center justify-center">
            @if(auth()->user()->avatar_url ?? false)
            <img src="{{ auth()->user()->avatar_url }}" class="w-6 h-6 rounded-full object-cover ring-2 {{ $isActive ? 'ring-brand-500' : 'ring-transparent' }}">
            @else
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black text-white ring-2 {{ $isActive ? 'ring-brand-500' : 'ring-transparent' }} transition-all"
                style="background:hsl({{ crc32(auth()->user()->name) % 360 }},60%,55%)">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            @endif
        </div>
        <span class="text-[10px] font-semibold {{ $isActive ? 'text-brand-600' : 'text-gray-400 dark:text-gray-500' }}">{{ $slotLabel }}</span>
        @if($isActive)<span class="w-1 h-1 rounded-full bg-brand-500 mt-0.5 -mb-0.5"></span>@endif
    </a>

    @else
    {{-- Generic slot --}}
    <a href="{{ $slotUrl }}" class="flex-1 flex flex-col items-center gap-0.5 pt-2 pb-1 min-w-0 group">
        <div class="w-6 h-6 flex items-center justify-center">
            <svg class="w-6 h-6 transition-colors {{ $isActive ? 'text-brand-600' : 'text-gray-400 dark:text-gray-500 group-active:text-brand-500' }}"
                fill="{{ $isActive ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                {!! $iconSvgPath !!}
            </svg>
        </div>
        <span class="text-[10px] font-semibold {{ $isActive ? 'text-brand-600' : 'text-gray-400 dark:text-gray-500' }}">{{ $slotLabel }}</span>
        @if($isActive)<span class="w-1 h-1 rounded-full bg-brand-500 mt-0.5 -mb-0.5"></span>@endif
    </a>
    @endif

    @endforeach

</nav>

@include('components.session-warning')

@stack('scripts')
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
}
</script>
</body>
</html>
