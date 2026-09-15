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
@php $popularTagsList = $data['popular_tags']; @endphp
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm border-b border-gray-100 dark:border-gray-700 pb-2">{{ $title }}</h3>
    <div class="flex flex-wrap gap-1.5 mt-1">
        @foreach($popularTagsList as $i => $tag)
        <a href="/tag/{{ $tag->slug }}"
           class="px-3 py-1 rounded-full text-xs font-medium transition-colors
                  {{ $i === 0
                      ? 'bg-brand-500 text-white hover:bg-brand-600'
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-brand-500 hover:text-white' }}">
            {{ $tag->name }}
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
            <img src="{{ $post->featured_image }}" loading="lazy" class="w-14 h-14 rounded-lg object-cover flex-shrink-0">
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

@elseif($type === 'breaking_ticker')
@php
$cfg = $widget->config ?? [];
$items = $cfg['items'] ?? [
    'ताजा समाचार: नेपालमा आज महत्वपूर्ण घटना घटे',
    'अर्थतन्त्र: बजार परिसूचकमा सुधार',
    'खेलकुद: नेपाल क्रिकेट टिमले जित्यो',
    'मौसम: काठमाण्डौमा वर्षाको सम्भावना',
];
$speed = $cfg['speed'] ?? '30s';
$label = $cfg['label'] ?? 'ब्रेकिङ';
@endphp
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="flex items-stretch">
        <span class="flex-shrink-0 bg-red-600 text-white text-xs font-black px-3 flex items-center tracking-wider uppercase">
            {{ $label }}
        </span>
        <div class="flex-1 overflow-hidden py-2.5 px-2">
            <div class="ticker-track whitespace-nowrap" style="animation: ticker {{ $speed }} linear infinite; display: inline-block;">
                @foreach($items as $item)
                <span class="inline-block text-xs text-gray-700 dark:text-gray-300 mr-10">
                    <span class="text-red-500 mr-1">▶</span> {{ $item }}
                </span>
                @endforeach
                @foreach($items as $item)
                <span class="inline-block text-xs text-gray-700 dark:text-gray-300 mr-10">
                    <span class="text-red-500 mr-1">▶</span> {{ $item }}
                </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
@once
<style>
@keyframes ticker { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
</style>
@endonce

@elseif($type === 'author_spotlight')
@php
$cfg = $widget->config ?? [];
$userId = $cfg['user_id'] ?? null;
$spotlightUser = $userId ? \App\Models\User::find($userId) : null;
if (!$spotlightUser) {
    $spotlightUser = \App\Models\User::withCount('posts')->orderByDesc('posts_count')->first();
}
$postCount = $spotlightUser?->posts_count ?? $spotlightUser?->posts()->published()->count() ?? 0;
$bio = $cfg['bio'] ?? $spotlightUser?->bio ?? 'नेपाली पत्रकारिता र लेखनमा समर्पित।';
@endphp
@if($spotlightUser)
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm flex items-center gap-1.5">
        <span>✍️</span> {{ $title }}
    </h3>
    <div class="flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-full overflow-hidden bg-brand-100 mb-3 ring-2 ring-brand-200">
            @if($spotlightUser->avatar)
                <img src="{{ $spotlightUser->avatar }}" loading="lazy" class="w-full h-full object-cover" alt="{{ $spotlightUser->name }}">
            @else
                <div class="w-full h-full flex items-center justify-center text-2xl font-black text-brand-600">
                    {{ strtoupper(substr($spotlightUser->name, 0, 1)) }}
                </div>
            @endif
        </div>
        <a href="/profile/{{ $spotlightUser->username }}" class="font-bold text-gray-900 dark:text-white hover:text-brand-600 transition-colors text-sm">
            {{ $spotlightUser->name }}
        </a>
        <p class="text-xs text-brand-500 mb-1">{{ number_format($postCount) }} articles</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-3 font-nepali">{{ Str::limit($bio, 80) }}</p>
        @auth
        <button class="px-4 py-1.5 text-xs font-semibold bg-brand-500 hover:bg-brand-600 text-white rounded-full transition-colors">
            Follow
        </button>
        @endauth
    </div>
</div>
@endif

@elseif($type === 'trending_now')
@php $trendingNow = $data['trending_now'] ?? collect(); @endphp
@if($trendingNow->isNotEmpty())
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm flex items-center gap-1.5">
        <span class="text-orange-500">📈</span> {{ $title }}
        <span class="ml-auto text-[10px] font-normal text-gray-400">last 24h</span>
    </h3>
    <ol class="space-y-2.5">
        @foreach($trendingNow as $i => $post)
        <li class="flex gap-2.5 items-start">
            <span class="text-lg font-black leading-none mt-0.5 min-w-[18px] {{ $i < 3 ? 'text-orange-500' : 'text-gray-200 dark:text-gray-600' }}">{{ $i+1 }}</span>
            <div class="flex-1 min-w-0">
                <a href="/posts/{{ $post->slug }}" class="text-xs font-medium text-gray-800 dark:text-gray-200 hover:text-brand-600 dark:hover:text-brand-400 line-clamp-2 font-nepali leading-snug">{{ $post->title }}</a>
                <p class="text-xs text-gray-400 mt-0.5">{{ number_format($post->view_count) }} views · {{ $post->published_at?->diffForHumans() }}</p>
            </div>
        </li>
        @endforeach
    </ol>
</div>
@endif

@elseif($type === 'related_searches')
@php
$cfg = $widget->config ?? [];
$topics = $cfg['topics'] ?? [];
$relatedTags = !empty($topics)
    ? collect($topics)->map(fn($t) => (object)['name' => $t, 'slug' => \Str::slug($t)])
    : ($data['popular_tags'] ?? collect())->take(12);
@endphp
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm">🔍 {{ $title }}</h3>
    <div class="flex flex-wrap gap-1.5">
        @foreach($relatedTags as $tag)
        <a href="/tag/{{ $tag->slug }}"
            class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors flex items-center gap-1">
            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            {{ $tag->name }}
        </a>
        @endforeach
    </div>
</div>

@elseif($type === 'comment_highlights')
@php $commentHighlights = $data['comment_highlights'] ?? collect(); @endphp
@if($commentHighlights->isNotEmpty())
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm">💬 {{ $title }}</h3>
    <div class="space-y-3">
        @foreach($commentHighlights as $comment)
        <div class="border-l-2 border-brand-200 dark:border-brand-800 pl-3">
            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed font-nepali line-clamp-2">{{ $comment->body }}</p>
            <div class="flex items-center gap-2 mt-1.5">
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">{{ $comment->displayName() }}</span>
                <span class="text-gray-300 dark:text-gray-600">·</span>
                <a href="/posts/{{ $comment->post->slug }}#comment-{{ $comment->id }}"
                   class="text-xs text-gray-400 hover:text-brand-500 transition-colors line-clamp-1 flex-1 truncate">
                    {{ Str::limit($comment->post->title, 30) }}
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@elseif($type === 'dark_mode_toggle')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4"
     x-data="{
        theme: localStorage.getItem('theme') || 'system',
        apply(t) {
            this.theme = t;
            localStorage.setItem('theme', t);
            const root = document.documentElement;
            if (t === 'dark') root.setAttribute('data-theme','dark');
            else if (t === 'light') root.setAttribute('data-theme','light');
            else root.removeAttribute('data-theme');
        }
     }">
    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm">🎨 {{ $title }}</h3>
    <div class="grid grid-cols-3 gap-2">
        <button @click="apply('light')"
            :class="theme === 'light' ? 'ring-2 ring-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'bg-gray-50 dark:bg-gray-700'"
            class="flex flex-col items-center gap-1 py-3 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 transition-all">
            <span class="text-xl">☀️</span> Light
        </button>
        <button @click="apply('dark')"
            :class="theme === 'dark' ? 'ring-2 ring-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'bg-gray-50 dark:bg-gray-700'"
            class="flex flex-col items-center gap-1 py-3 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 transition-all">
            <span class="text-xl">🌙</span> Dark
        </button>
        <button @click="apply('system')"
            :class="theme === 'system' ? 'ring-2 ring-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'bg-gray-50 dark:bg-gray-700'"
            class="flex flex-col items-center gap-1 py-3 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 transition-all">
            <span class="text-xl">💻</span> Auto
        </button>
    </div>
</div>

@elseif($type === 'social_proof')
@php
$cfg      = $widget->config ?? [];
$settings = $data['settings'] ?? [];
$readerCount = $settings['reader_count'] ?? $cfg['reader_count'] ?? '12,400+';
$readerLabel = $settings['reader_label'] ?? $cfg['reader_label'] ?? 'Get fresh content from नखोज';
$siteName = $settings['site_name'] ?? config('app.name', 'नखोज');

// Top authors for stacked avatars
$avatarAuthors = \App\Models\User::withCount('posts')->orderByDesc('posts_count')->limit(4)->get();

// Social platform config: [key in settings => [label, color, svg icon path]]
$socialLinks = [
    'social_facebook'  => ['Facebook',  '#1877F2', 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z'],
    'social_twitter'   => ['Twitter/X', '#000000', 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.737-8.835L2.25 2.25H9.08l4.261 5.634 4.903-5.634zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
    'social_instagram' => ['Instagram', '#E1306C', 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z'],
    'social_youtube'   => ['YouTube',   '#FF0000', 'M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z'],
    'social_tiktok'    => ['TikTok',    '#010101', 'M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.28 8.28 0 004.84 1.55V6.79a4.85 4.85 0 01-1.07-.1z'],
    'social_linkedin'  => ['LinkedIn',  '#0A66C2', 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z M4 6a2 2 0 100-4 2 2 0 000 4z'],
    'social_newsletter'=> ['Newsletter','#6B7280', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
];
$activeLinks = collect($socialLinks)->filter(fn($v, $k) => !empty($settings[$k]));
@endphp
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    {{-- Header band --}}
    <div class="bg-gradient-to-br from-brand-600 to-brand-800 px-5 pt-6 pb-8 text-center">
        {{-- Stacked avatars --}}
        <div class="flex justify-center mb-3">
            <div class="flex -space-x-3">
                @foreach($avatarAuthors as $au)
                <img src="{{ $au->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($au->name).'&size=40&background=random' }}"
                     alt="{{ $au->name }}"
                     class="w-10 h-10 rounded-full border-2 border-white object-cover"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($au->name) }}&size=40&background=6366f1&color=fff'">
                @endforeach
                @if($avatarAuthors->isEmpty())
                @foreach(['A','B','C','D'] as $l)
                <div class="w-10 h-10 rounded-full border-2 border-white bg-brand-400 flex items-center justify-center text-white font-bold text-sm">{{ $l }}</div>
                @endforeach
                @endif
            </div>
        </div>
        {{-- Reader count --}}
        <p class="text-3xl font-black text-white leading-none">{{ $readerCount }}</p>
        <p class="text-sm text-brand-100 mt-1">{{ $readerLabel }}</p>
    </div>

    {{-- Social icon buttons --}}
    @if($activeLinks->isNotEmpty())
    <div class="px-5 py-4 flex flex-wrap justify-center gap-2">
        @foreach($activeLinks as $key => $meta)
        @php [$label, $color, $iconPath] = $meta; $url = $settings[$key]; @endphp
        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
           title="{{ $label }}"
           class="w-10 h-10 rounded-full flex items-center justify-center text-white shadow-sm hover:scale-110 transition-transform"
           style="background-color: {{ $color }}">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="{{ $iconPath }}"/>
            </svg>
        </a>
        @endforeach
    </div>
    @else
    {{-- Fallback when no social links configured --}}
    <div class="px-5 py-4 text-center text-xs text-gray-400 dark:text-gray-500">
        Configure social links in Admin → Settings
    </div>
    @endif
</div>
@endif
