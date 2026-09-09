{{--
  Render a single widget block.
  $widget   - Widget model instance
  $data     - $widgetData array (pre-fetched in HomeController)
--}}
@php $title = $widget->title; $type = $widget->type; @endphp

@if($type === 'popular_posts' && !empty($data['popular_posts']))
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm flex items-center gap-1.5">
        <span class="text-red-500">🔥</span> {{ $title }}
    </h3>
    <ol class="space-y-2.5">
        @foreach($data['popular_posts'] as $i => $post)
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

@elseif($type === 'popular_tags' && !empty($data['popular_tags']))
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm">🏷 {{ $title }}</h3>
    <div class="flex flex-wrap gap-1.5">
        @foreach($data['popular_tags'] as $tag)
        <a href="/tag/{{ $tag->slug }}"
            class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-brand-100 dark:hover:bg-brand-900/30 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
            {{ $tag->name }}
            <span class="text-gray-400 dark:text-gray-500 text-xs">({{ $tag->posts_count }})</span>
        </a>
        @endforeach
    </div>
</div>

@elseif($type === 'recommended_posts' && !empty($data['recommended_posts']))
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm">✨ {{ $title }}</h3>
    <div class="space-y-3">
        @foreach($data['recommended_posts'] as $post)
        <div class="flex gap-3">
            @if($post->featured_image)
            <img src="{{ $post->featured_image }}" class="w-14 h-14 rounded-lg object-cover flex-shrink-0">
            @else
            <div class="w-14 h-14 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            @endif
            <div class="flex-1 min-w-0">
                <a href="/posts/{{ $post->slug }}" class="text-xs font-semibold text-gray-800 dark:text-gray-200 hover:text-brand-600 dark:hover:text-brand-400 line-clamp-2 font-nepali leading-snug">{{ $post->title }}</a>
                <p class="text-xs text-gray-400 mt-0.5">{{ $post->published_at?->diffForHumans() }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

@elseif($type === 'voting_poll' && !empty($data['voting_poll']))
@php $poll = $data['voting_poll']; $total = $poll->options->sum('votes_count'); @endphp
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
    <h3 class="font-bold text-gray-900 dark:text-white mb-1 text-sm">📊 {{ $title }}</h3>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 font-nepali">{{ $poll->question }}</p>
    <div class="space-y-2">
        @foreach($poll->options as $opt)
        @php $pct = $total > 0 ? round($opt->votes_count / $total * 100) : 0; @endphp
        <form method="POST" action="/polls/{{ $poll->id }}/vote">
            @csrf
            <button name="option_id" value="{{ $opt->id }}" class="w-full text-left group">
                <div class="flex justify-between text-xs text-gray-700 dark:text-gray-300 mb-0.5">
                    <span class="font-nepali">{{ $opt->text }}</span>
                    <span class="font-bold text-brand-500">{{ $pct }}%</span>
                </div>
                <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-500 group-hover:bg-brand-600 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                </div>
            </button>
        </form>
        @endforeach
    </div>
    <p class="text-xs text-gray-400 mt-2 text-right">{{ number_format($total) }} votes</p>
</div>

@elseif($type === 'follow_us')
@php $settings = $data['settings'] ?? []; @endphp
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm">👥 {{ $title }}</h3>
    @php
    $socials = [
        'facebook'  => ['icon'=>'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z', 'color'=>'bg-blue-600'],
        'twitter'   => ['icon'=>'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z', 'color'=>'bg-gray-900 dark:bg-gray-700'],
        'instagram' => ['icon'=>'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01M6.5 19.5h11a3 3 0 003-3v-11a3 3 0 00-3-3h-11a3 3 0 00-3 3v11a3 3 0 003 3z', 'color'=>'bg-pink-600'],
        'youtube'   => ['icon'=>'M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.41 19.1C5.12 19.56 12 19.56 12 19.56s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.95 29 29 0 00.46-5.25 29 29 0 00-.46-5.33zm-12.29 8.5V8.25l5.74 3.34-5.74 3.33z', 'color'=>'bg-red-600'],
        'tiktok'    => ['icon'=>'M9 12a4 4 0 104 4V4a5 5 0 005 5', 'color'=>'bg-gray-900 dark:bg-gray-700'],
    ];
    @endphp
    <div class="space-y-2">
        @foreach($socials as $platform => $info)
        @if(!empty($settings['social_'.$platform]))
        <a href="{{ $settings['social_'.$platform] }}" target="_blank" rel="noopener"
            class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <span class="w-8 h-8 rounded-lg {{ $info['color'] }} flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $info['icon'] }}"/></svg>
            </span>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">{{ $platform }}</span>
            <svg class="w-3.5 h-3.5 text-gray-300 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        @endif
        @endforeach
    </div>
</div>

@elseif($type === 'newsletter')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
    <h3 class="font-bold text-gray-900 dark:text-white mb-1 text-sm">📧 {{ $title }}</h3>
    <p class="text-xs text-gray-400 dark:text-gray-500 mb-3 font-nepali">नयाँ समाचार सिधै इमेलमा पाउनुहोस्।</p>
    <form method="POST" action="/newsletter/subscribe" class="space-y-2">
        @csrf
        <input type="email" name="email" placeholder="your@email.com" required
            class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all">
        <button class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg transition-colors">
            Subscribe
        </button>
    </form>
</div>

@elseif($type === 'about_us')
@php $settings = $data['settings'] ?? []; @endphp
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
    <h3 class="font-bold text-gray-900 dark:text-white mb-2 text-sm">ℹ {{ $title }}</h3>
    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-nepali">
        {{ $settings['site_description'] ?? 'नेपालको अग्रणी बहु-ब्लग र समाचार प्लेटफर्म। हजारौं नेपाली पाठकसँग आफ्नो कथा साझा गर्नुहोस्।' }}
    </p>
</div>

@elseif($type === 'category_grid')
@php
$cfg = $widget->config ?? [];
$showSearch = $cfg['show_search'] ?? true;
$searchPlaceholder = $cfg['search_placeholder'] ?? 'Search topics…';
$cats = $cfg['categories'] ?? [
    ['slug' => 'science',    'label' => 'Science',     'icon' => '🔬', 'color' => 'bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40',    'text' => 'text-blue-700 dark:text-blue-300'],
    ['slug' => 'it',         'label' => 'IT',           'icon' => '💻', 'color' => 'bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/40', 'text' => 'text-purple-700 dark:text-purple-300'],
    ['slug' => 'technology', 'label' => 'Technology',  'icon' => '⚙️', 'color' => 'bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700',       'text' => 'text-gray-700 dark:text-gray-300'],
    ['slug' => 'health',     'label' => 'Health',       'icon' => '❤️', 'color' => 'bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40',          'text' => 'text-red-700 dark:text-red-300'],
    ['slug' => 'finance',    'label' => 'Finance',      'icon' => '💰', 'color' => 'bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/40',   'text' => 'text-green-700 dark:text-green-300'],
    ['slug' => 'sports',     'label' => 'Sports',       'icon' => '⚽', 'color' => 'bg-orange-50 dark:bg-orange-900/20 hover:bg-orange-100 dark:hover:bg-orange-900/40', 'text' => 'text-orange-700 dark:text-orange-300'],
    ['slug' => 'politics',   'label' => 'Politics',     'icon' => '🏛️', 'color' => 'bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/40', 'text' => 'text-indigo-700 dark:text-indigo-300'],
    ['slug' => 'education',  'label' => 'Education',    'icon' => '📚', 'color' => 'bg-yellow-50 dark:bg-yellow-900/20 hover:bg-yellow-100 dark:hover:bg-yellow-900/40', 'text' => 'text-yellow-700 dark:text-yellow-300'],
    ['slug' => 'travel',     'label' => 'Travel',       'icon' => '✈️', 'color' => 'bg-cyan-50 dark:bg-cyan-900/20 hover:bg-cyan-100 dark:hover:bg-cyan-900/40',      'text' => 'text-cyan-700 dark:text-cyan-300'],
];
@endphp
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4"
     x-data="{ q: '' }">
    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm flex items-center gap-1.5">
        <span>🗂</span> {{ $title }}
    </h3>
    <div class="grid grid-cols-3 gap-2 mb-3">
        @foreach($cats as $cat)
        <a href="/category/{{ $cat['slug'] }}"
           x-show="!q || '{{ strtolower($cat['label']) }}'.includes(q.toLowerCase())"
           class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl transition-colors cursor-pointer {{ $cat['color'] }}">
            <span class="text-2xl leading-none">{{ $cat['icon'] }}</span>
            <span class="text-[10px] font-semibold leading-tight text-center {{ $cat['text'] }}">{{ $cat['label'] }}</span>
        </a>
        @endforeach
    </div>
    @if($showSearch)
    <div class="relative">
        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input x-model="q" type="text" placeholder="{{ $searchPlaceholder }}"
            class="w-full pl-8 pr-3 py-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 transition-all">
    </div>
    @endif
</div>

@elseif($type === 'social_proof')
@php
$cfg = $widget->config ?? [];
$readerCount = $cfg['reader_count'] ?? '12,400';
$readerLabel = $cfg['reader_label'] ?? 'monthly readers';
$stats = $cfg['stats'] ?? [
    ['platform' => 'Facebook',  'icon' => '📘', 'count' => '8.2K',  'label' => 'Followers', 'url' => '#', 'color' => 'bg-blue-600'],
    ['platform' => 'YouTube',   'icon' => '▶️', 'count' => '3.1K',  'label' => 'Subscribers', 'url' => '#', 'color' => 'bg-red-600'],
    ['platform' => 'Instagram', 'icon' => '📸', 'count' => '1.5K',  'label' => 'Followers', 'url' => '#', 'color' => 'bg-pink-600'],
];
@endphp
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm">🌐 {{ $title }}</h3>

    {{-- Headline reader count --}}
    <div class="flex items-center gap-3 bg-brand-50 dark:bg-brand-900/20 rounded-xl px-4 py-3 mb-3">
        <div class="w-10 h-10 rounded-full bg-brand-500 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-lg font-black text-brand-600 dark:text-brand-400 leading-none">{{ $readerCount }}</p>
            <p class="text-xs text-brand-500 dark:text-brand-400">{{ $readerLabel }}</p>
        </div>
    </div>

    {{-- Social stat rows --}}
    <div class="space-y-2">
        @foreach($stats as $stat)
        <a href="{{ $stat['url'] ?? '#' }}" target="_blank" rel="noopener"
           class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <span class="w-8 h-8 {{ $stat['color'] }} rounded-lg flex items-center justify-center text-sm flex-shrink-0 text-white font-bold">{{ $stat['icon'] }}</span>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $stat['platform'] }}</p>
                <p class="text-xs text-gray-400">{{ $stat['label'] }}</p>
            </div>
            <span class="text-sm font-black text-gray-700 dark:text-gray-300">{{ $stat['count'] }}</span>
        </a>
        @endforeach
    </div>
</div>
@endif
