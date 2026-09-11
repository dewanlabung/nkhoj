@extends('layouts.app')
@section('title', $post->seo_title ?? $post->title)
@section('description', $post->seo_desc ?? $post->excerpt)
@section('og_type', 'article')
@if($post->thumbnail_url)
@section('og_image', $post->thumbnail_url)
@endif
@section('canonical', url('/posts/'.$post->slug))
@push('jsonld')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {"@type": "ListItem", "position": 1, "name": "Home", "item": "{{ url('/') }}"},
        {"@type": "ListItem", "position": 2, "name": "{{ addslashes($post->category->name_en) }}", "item": "{{ url('/category/'.$post->category->slug) }}"},
        {"@type": "ListItem", "position": 3, "name": "{{ addslashes($post->title) }}", "item": "{{ url('/posts/'.$post->slug) }}"}
    ]
}
</script>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "NewsArticle",
    "headline": "{{ addslashes($post->seo_title ?? $post->title) }}",
    "description": "{{ addslashes($post->seo_desc ?? $post->excerpt) }}",
    "url": "{{ url('/posts/'.$post->slug) }}",
    @if($post->thumbnail_url)
    "image": ["{{ $post->thumbnail_url }}"],
    @endif
    "datePublished": "{{ $post->published_at->toIso8601String() }}",
    "dateModified": "{{ $post->updated_at->toIso8601String() }}",
    "author": {
        "@type": "Person",
        "name": "{{ addslashes($post->author->name) }}",
        "url": "{{ url('/profile/'.$post->author->username) }}"
    },
    "publisher": {
        "@type": "Organization",
        "name": "{{ config('app.name', 'nkhoj') }}"
    }
}
</script>
@endpush

@section('content')
{{-- Reading progress bar --}}
<div id="reading-progress" class="fixed top-0 left-0 right-0 z-50 h-1 bg-gray-200/50 dark:bg-gray-700/50">
    <div id="reading-progress-bar" class="h-full bg-brand-500 transition-all duration-75" style="width:0%"></div>
</div>
<script>
(function() {
    const bar = document.getElementById('reading-progress-bar');
    if (!bar) return;
    window.addEventListener('scroll', function() {
        const doc = document.documentElement;
        const scrolled = doc.scrollTop || document.body.scrollTop;
        const total = doc.scrollHeight - doc.clientHeight;
        bar.style.width = total > 0 ? Math.min(100, (scrolled / total) * 100) + '%' : '0%';
    }, { passive: true });
})();
</script>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- Article --}}
    <article class="lg:col-span-2">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
            <a href="/" class="hover:text-brand-600">Home</a>
            <span>/</span>
            <a href="/category/{{ $post->category->slug }}" class="hover:text-brand-600">{{ $post->category->name_en }}</a>
            <span>/</span>
            <span class="text-gray-600 truncate">{{ Str::limit($post->title, 40) }}</span>
        </nav>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @if($post->is_sensitive ?? false)
            <div class="bg-amber-50 border-b border-amber-200 px-6 py-3 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p class="text-sm text-amber-800 font-semibold">Sensitive content — this article may include content some readers find distressing.</p>
            </div>
            @endif
            @if($post->thumbnail_url)
            <img src="{{ $post->thumbnail_url }}" alt="{{ $post->title }}" class="w-full h-72 object-cover">
            @endif

            <div class="p-8">
                {{-- Category + Tags --}}
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <a href="/category/{{ $post->category->slug }}"
                        class="px-3 py-1 bg-brand-50 text-brand-600 text-xs font-semibold rounded-full uppercase tracking-wide hover:bg-brand-100">
                        {{ $post->category->name_ne ?? $post->category->name_en }}
                    </a>
                    @foreach($post->tags as $tag)
                    <a href="/tag/{{ $tag->slug }}" class="text-xs text-gray-400 hover:text-gray-600">#{{ $tag->name_en }}</a>
                    @endforeach
                </div>

                {{-- Title --}}
                <h1 class="text-3xl font-bold text-gray-900 leading-tight mb-4">{{ $post->title }}</h1>

                {{-- Meta --}}
                <div class="flex items-center gap-4 pb-6 border-b border-gray-100 mb-6">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-full bg-brand-500 flex items-center justify-center text-white font-semibold text-sm">
                            {{ strtoupper(substr($post->author->name, 0, 1)) }}
                        </div>
                        <div>
                            <a href="/profile/{{ $post->author->username }}" class="text-sm font-semibold text-gray-800 hover:text-brand-600">{{ $post->author->name }}</a>
                            <p class="text-xs text-gray-400">{{ $post->published_at->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="ml-auto flex items-center gap-4 text-xs text-gray-400">
                        <span>{{ $post->readingTimeMinutes() }} min read</span>
                        <span>{{ number_format($post->view_count) }} views</span>
                        @if(auth()->check() && auth()->id() === $post->author_id)
                        <a href="/dashboard/posts/{{ $post->id }}/edit"
                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-600 border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors font-medium">
                            ✏️ Edit
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Excerpt --}}
                @if($post->excerpt)
                <p class="text-lg text-gray-600 leading-relaxed mb-6 font-medium font-nepali">{{ $post->excerpt }}</p>
                @endif

                {{-- Pro badge --}}
                @if($post->is_pro)
                <div class="mb-4">
                    <span class="inline-flex items-center gap-1.5 bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 text-xs font-bold px-3 py-1.5 rounded-full border border-brand-200 dark:border-brand-700/50">
                        ✨ Pro Content
                    </span>
                </div>
                @endif

                {{-- Body with content locker --}}
                <x-content-locker :post="$post">
                    @if(is_array($post->body))
                        @foreach($post->body as $block)
                            @if(($block['type'] ?? '') === 'paragraph')
                                <p>{{ $block['content'] ?? '' }}</p>
                            @elseif(($block['type'] ?? '') === 'heading')
                                <h2>{{ $block['content'] ?? '' }}</h2>
                            @else
                                <p>{{ $block['content'] ?? json_encode($block) }}</p>
                            @endif
                        @endforeach
                    @else
                        {!! nl2br(e($post->body)) !!}
                    @endif
                </x-content-locker>

                {{-- ═══════════════════ SOURCES ═══════════════════ --}}
                @if($post->sources && count($post->sources))
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        Sources
                    </h4>
                    <ul class="space-y-1">
                        @foreach($post->sources as $src)
                        @if(!empty($src['label']) || !empty($src['url']))
                        <li class="text-sm text-gray-600">
                            @if(!empty($src['url']))
                            <a href="{{ $src['url'] }}" target="_blank" rel="nofollow noopener"
                                class="text-brand-600 hover:underline">{{ $src['label'] ?: $src['url'] }}</a>
                            @else
                            <span>{{ $src['label'] }}</span>
                            @endif
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- ═══════════════════ FAQ ═══════════════════ --}}
                @if($post->article_faq && count($post->article_faq))
                <div class="mt-8 pt-6 border-t border-gray-100" x-data="{ open: null }">
                    <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Frequently Asked Questions
                    </h4>
                    <div class="space-y-2">
                        @foreach($post->article_faq as $i => $faq)
                        @if(!empty($faq['q']))
                        <div class="border border-gray-100 rounded-xl overflow-hidden" x-data="{ open: false }">
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 transition-colors">
                                <span class="text-sm font-semibold text-gray-800">{{ $faq['q'] }}</span>
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-collapse class="px-4 py-3 text-sm text-gray-600 bg-white">
                                {{ $faq['a'] ?? '' }}
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ═══════════════════ REACTIONS ═══════════════════ --}}
                <div class="mt-10 pt-6 border-t border-gray-100"
                    x-data="{
                        reactions: {{ json_encode($reactionCounts) }},
                        active: '{{ $userReaction ?? '' }}',
                        loading: false,
                        emojis: [
                            { key: 'heart',    icon: '❤️',  label: 'मन पर्यो' },
                            { key: 'laugh',    icon: '😄',  label: 'हाँसो' },
                            { key: 'wow',      icon: '😮',  label: 'अचम्म' },
                            { key: 'sad',      icon: '😢',  label: 'दुःख' },
                            { key: 'angry',    icon: '😡',  label: 'रिस' },
                            { key: 'amazing',  icon: '🤩',  label: 'उत्कृष्ट' },
                        ],
                        react(key) {
                            if (this.loading) return;
                            this.loading = true;
                            fetch('/react/{{ $post->id }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                },
                                body: JSON.stringify({ emoji: key })
                            })
                            .then(r => r.json())
                            .then(d => {
                                this.reactions = d.counts;
                                this.active = d.action === 'removed' ? '' : key;
                                this.loading = false;
                            })
                            .catch(() => this.loading = false);
                        },
                        count(key) { return this.reactions[key] || 0; },
                        total() { return Object.values(this.reactions).reduce((a,b) => a+b, 0); }
                    }">
                    <p class="text-sm font-semibold text-gray-500 mb-3 font-nepali">यो लेख कस्तो लाग्यो?</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="e in emojis" :key="e.key">
                            <button
                                @click="react(e.key)"
                                :disabled="loading"
                                :class="active === e.key
                                    ? 'bg-brand-50 border-brand-400 text-brand-700 shadow-sm scale-105'
                                    : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-brand-50 hover:border-brand-300'"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-full border text-sm font-medium transition-all duration-150 disabled:opacity-50">
                                <span x-text="e.icon" class="text-lg leading-none"></span>
                                <span class="font-nepali text-xs" x-text="e.label"></span>
                                <span x-show="count(e.key) > 0" x-text="count(e.key)"
                                    class="bg-brand-500 text-white text-xs rounded-full px-1.5 min-w-[18px] text-center font-bold"></span>
                            </button>
                        </template>
                    </div>
                    <p class="mt-2 text-xs text-gray-400" x-show="total() > 0">
                        जम्मा <span x-text="total()"></span> प्रतिक्रिया
                    </p>
                </div>

                {{-- ═══════════════════ SHARE + BOOKMARK ═══════════════════ --}}
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center gap-3 flex-wrap">
                    <div x-data="{ shares: {{ $post->share_count ?? 0 }}, trackShare() { fetch('/posts/{{ $post->slug }}/share', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } }).then(r=>r.json()).then(d=>{ this.shares=d.share_count; }).catch(()=>{}); } }" class="flex flex-wrap items-center gap-2 w-full">
                    <span class="text-sm text-gray-500 font-medium">Share:</span>
                    <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . url()->current()) }}"
                        target="_blank" rel="noopener" @click="trackShare()"
                        class="px-3 py-1.5 text-xs font-medium bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition-colors">
                        WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                        target="_blank" @click="trackShare()"
                        class="px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors">
                        Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(url()->current()) }}"
                        target="_blank" @click="trackShare()"
                        class="px-3 py-1.5 text-xs font-medium bg-sky-50 text-sky-600 rounded-lg hover:bg-sky-100 transition-colors">
                        Twitter/X
                    </a>
                    <button @click="navigator.clipboard.writeText(window.location.href).then(()=>{ $el.textContent='Copied!'; trackShare(); setTimeout(()=>$el.textContent='Copy Link',2000); })"
                        class="px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">
                        Copy Link
                    </button>
                    <span x-show="shares > 0" class="text-xs text-gray-400 ml-1" x-text="shares + ' shares'"></span>
                    </div>

                    @auth
                    {{-- Bookmark button --}}
                    <div class="ml-auto flex items-center gap-2 relative"
                        x-data="{ saved: {{ $isBookmarked ? 'true' : 'false' }}, loading: false }"
                        x-cloak>
                        <button
                            @click="
                                loading = true;
                                fetch('/bookmark/{{ $post->id }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
                                .then(r => r.json())
                                .then(d => { saved = d.action === 'saved'; loading = false; })
                                .catch(() => loading = false)
                            "
                            :disabled="loading"
                            :class="saved ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50">
                            <span x-text="saved ? '🔖 सेभ गरियो' : '🔖 सेभ गर्नुहोस्'"></span>
                        </button>
                        {{-- Report button --}}
                        <div x-data="{ open: false, done: false, reason: '', sending: false }">
                            <button @click="open = !open" title="Report this article"
                                class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6H10.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                Report
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false"
                                class="absolute z-20 right-0 mt-2 w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl p-4">
                                <template x-if="done">
                                    <p class="text-sm text-green-600 text-center py-2">✓ Report submitted. Thank you.</p>
                                </template>
                                <template x-if="!done">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Why are you reporting this?</p>
                                        <select x-model="reason" class="w-full text-xs border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1.5 mb-3 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                            <option value="">Select a reason…</option>
                                            <option value="spam">Spam or misleading</option>
                                            <option value="misinformation">Misinformation</option>
                                            <option value="hate_speech">Hate speech / harassment</option>
                                            <option value="violence">Violence / dangerous content</option>
                                            <option value="copyright">Copyright violation</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <button :disabled="!reason || sending" @click="
                                            sending = true;
                                            fetch('/report', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ type: 'post', id: {{ $post->id }}, reason }) })
                                            .then(r => r.json()).then(() => { done = true; sending = false; }).catch(() => { sending = false; });
                                        "
                                        class="w-full text-xs bg-red-600 text-white rounded-lg py-1.5 font-semibold disabled:opacity-50 hover:bg-red-700 transition-colors">
                                            Submit Report
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    @endauth
                </div>
            </div>
        </div>

        {{-- ═══════════════════ COMMENTS ═══════════════════ --}}
        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
            x-data="{
                showForm: false,
                replyTo: null,
                replyName: '',
                body: '',
                guestName: '',
                loading: false,
                comments: [],
                submitComment(parentId) {
                    if (!this.body.trim()) return;
                    this.loading = true;
                    const fd = new FormData();
                    fd.append('body', this.body);
                    fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
                    if (parentId) fd.append('parent_id', parentId);
                    if (!{{ auth()->check() ? 'true' : 'false' }}) fd.append('guest_name', this.guestName || 'अतिथि');
                    fetch('/posts/{{ $post->slug }}/comments', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(() => { this.body=''; this.replyTo=null; this.loading=false; location.reload(); })
                    .catch(() => this.loading = false);
                }
            }">
            <h3 class="text-lg font-bold text-gray-900 mb-6 font-nepali flex items-center gap-2">
                💬 टिप्पणीहरू
                <span class="bg-gray-100 text-gray-500 text-sm px-2 py-0.5 rounded-full font-sans">{{ $comments->count() }}</span>
            </h3>

            {{-- Comment form --}}
            <div class="mb-6 bg-gray-50 rounded-xl p-4">
                <textarea x-model="body" placeholder="आफ्नो विचार लेख्नुहोस्..." rows="3"
                    class="w-full px-4 py-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none bg-white font-nepali"></textarea>
                @guest
                <input x-model="guestName" type="text" placeholder="तपाईंको नाम (ऐच्छिक)"
                    class="mt-2 w-full px-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                @endguest
                <div class="mt-2 flex justify-end">
                    <button @click="submitComment(null)" :disabled="!body.trim() || loading"
                        class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-50">
                        <span x-text="loading ? 'पोस्ट गर्दै...' : 'टिप्पणी पोस्ट गर्नुहोस्'"></span>
                    </button>
                </div>
            </div>

            {{-- Comments list --}}
            <div class="space-y-5">
                @forelse($comments as $comment)
                <div class="flex gap-3" id="comment-{{ $comment->id }}">
                    <div class="w-9 h-9 rounded-full bg-brand-400 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr($comment->displayName(), 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="bg-gray-50 rounded-xl px-4 py-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-semibold text-gray-800">{{ $comment->displayName() }}</span>
                                <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-700 leading-relaxed font-nepali">{{ $comment->body }}</p>
                        </div>
                        {{-- Emoji reactions + reply button --}}
                        <div class="mt-1 flex items-center gap-1 px-2 flex-wrap"
                             x-data="{ counts: {{ json_encode($comment->reactionCounts()) }}, open: false }">
                            @foreach(['👍','❤️','😂','😮','😢','😡'] as $emoji)
                            <button @click="
                                fetch('/comments/{{ $comment->id }}/react', {
                                    method: 'POST',
                                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                                    body: JSON.stringify({emoji: '{{ $emoji }}'})
                                }).then(r=>r.json()).then(d=>{ counts = d.counts; })
                            " class="inline-flex items-center gap-0.5 text-xs px-1.5 py-0.5 rounded-full hover:bg-gray-100 transition-colors"
                            :class="counts['{{ $emoji }}'] ? 'bg-gray-100' : ''">
                                <span>{{ $emoji }}</span>
                                <span x-show="counts['{{ $emoji }}']" x-text="counts['{{ $emoji }}']" class="text-gray-500 font-medium"></span>
                            </button>
                            @endforeach
                            <span class="mx-1 text-gray-200">|</span>
                            <button
                                @click="replyTo = {{ $comment->id }}; replyName = '{{ $comment->displayName() }}'; $nextTick(() => $el.closest('.comment-wrap').querySelector('textarea').focus())"
                                class="text-xs text-gray-400 hover:text-brand-600 font-medium transition-colors">
                                ↩ जवाफ
                            </button>
                            @auth
                            @if(auth()->id() === $comment->user_id || auth()->user()?->isAdmin())
                            <form method="POST" action="/comments/{{ $comment->id }}" class="inline ml-1" onsubmit="return confirm('हटाउनुहोस्?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600 transition-colors">हटाउनुहोस्</button>
                            </form>
                            @endif
                            @endauth
                        </div>

                        {{-- Inline reply form --}}
                        <div x-show="replyTo === {{ $comment->id }}" x-cloak class="mt-3 ml-4 comment-wrap">
                            <textarea x-model="body" placeholder="@{{ $comment->displayName() }} लाई जवाफ..." rows="2"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none font-nepali"></textarea>
                            <div class="mt-1 flex gap-2 justify-end">
                                <button @click="replyTo = null; body=''" class="text-xs text-gray-400 hover:text-gray-600">रद्द</button>
                                <button @click="submitComment({{ $comment->id }})" :disabled="!body.trim() || loading"
                                    class="px-3 py-1.5 bg-brand-500 text-white text-xs font-semibold rounded-lg disabled:opacity-50">
                                    पोस्ट गर्नुहोस्
                                </button>
                            </div>
                        </div>

                        {{-- Nested replies with load-more --}}
                        @if($comment->replies->count())
                        @php $allReplies = $comment->replies; $firstReplies = $allReplies->take(3); $moreReplies = $allReplies->skip(3); @endphp
                        <div class="mt-3 ml-4 space-y-3" x-data="{ showAll: false }">
                            @foreach($firstReplies as $reply)
                            <div class="flex gap-3">
                                <div class="w-7 h-7 rounded-full bg-indigo-300 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($reply->displayName(), 0, 1)) }}
                                </div>
                                <div class="flex-1 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl px-4 py-2">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $reply->displayName() }}</span>
                                        <span class="text-xs text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 font-nepali">{{ $reply->body }}</p>
                                </div>
                            </div>
                            @endforeach
                            @if($moreReplies->count())
                            <template x-if="showAll">
                                <div class="space-y-3">
                                    @foreach($moreReplies as $reply)
                                    <div class="flex gap-3">
                                        <div class="w-7 h-7 rounded-full bg-indigo-300 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($reply->displayName(), 0, 1)) }}
                                        </div>
                                        <div class="flex-1 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl px-4 py-2">
                                            <div class="flex items-center justify-between mb-0.5">
                                                <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $reply->displayName() }}</span>
                                                <span class="text-xs text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 font-nepali">{{ $reply->body }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </template>
                            <button @click="showAll = !showAll" class="text-xs text-brand-600 hover:text-brand-700 font-medium mt-1">
                                <span x-text="showAll ? 'Hide replies' : '+ {{ $moreReplies->count() }} more {{ Str::plural("reply", $moreReplies->count()) }}'"></span>
                            </button>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-400">
                    <div class="text-3xl mb-2">💬</div>
                    <p class="text-sm font-nepali">पहिलो टिप्पणी गर्नुहोस्!</p>
                </div>
                @endforelse
            </div>
        </div>
    {{-- Series navigation --}}
    @if($post->series_id && $post->relationLoaded('series') && $post->series)
    @php
        $seriesPosts = $post->series->posts()->published()->orderBy('series_order')->get();
        $seriesIdx   = $seriesPosts->search(fn($p) => $p->id === $post->id);
        $prevPost    = $seriesIdx > 0 ? $seriesPosts[$seriesIdx - 1] : null;
        $nextPost    = $seriesIdx < $seriesPosts->count() - 1 ? $seriesPosts[$seriesIdx + 1] : null;
    @endphp
    <div class="bg-brand-50 dark:bg-brand-900/10 border border-brand-100 dark:border-brand-900/30 rounded-2xl p-5 mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="text-lg">📚</span>
            <div>
                <p class="text-xs text-brand-500 font-semibold uppercase tracking-wide">Part {{ $seriesIdx + 1 }} of {{ $seriesPosts->count() }}</p>
                <a href="/series/{{ $post->series->slug }}" class="font-bold text-gray-900 dark:text-white hover:text-brand-600 transition-colors text-sm">
                    {{ $post->series->title }}
                </a>
            </div>
        </div>
        <div class="flex gap-3">
            @if($prevPost)
            <a href="/posts/{{ $prevPost->slug }}"
               class="flex-1 flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-brand-300 transition-colors text-xs">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span class="line-clamp-1 font-nepali text-gray-700 dark:text-gray-300">{{ $prevPost->title }}</span>
            </a>
            @endif
            @if($nextPost)
            <a href="/posts/{{ $nextPost->slug }}"
               class="flex-1 flex items-center justify-end gap-2 px-3 py-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-brand-300 transition-colors text-xs">
                <span class="line-clamp-1 font-nepali text-gray-700 dark:text-gray-300">{{ $nextPost->title }}</span>
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif
        </div>
    </div>
    @endif
    </article>

    {{-- Sidebar --}}
    <aside class="space-y-6">
        {{-- Author card --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
            <a href="/profile/{{ $post->author->username }}">
                <div class="w-16 h-16 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold text-2xl mx-auto mb-3">
                    {{ strtoupper(substr($post->author->name, 0, 1)) }}
                </div>
                <h4 class="font-bold text-gray-900 hover:text-brand-600 transition-colors">{{ $post->author->name }}</h4>
                <p class="text-xs text-gray-400 mt-1">@<span>{{ $post->author->username }}</span></p>
            </a>
            @auth
            @if(auth()->id() !== $post->author_id)
            <div class="mt-3"
                x-data="{
                    following: {{ auth()->user()->following()->where('following_id', $post->author_id)->exists() ? 'true' : 'false' }},
                    loading: false
                }">
                <button
                    @click="
                        loading=true;
                        fetch('/follow/{{ $post->author_id }}', { method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}})
                        .then(r=>r.json()).then(d=>{ following = d.action==='followed'; loading=false; })
                        .catch(()=>loading=false)
                    "
                    :disabled="loading"
                    :class="following ? 'bg-white text-brand-600 border-brand-300 border' : 'bg-brand-500 text-white'"
                    class="w-full py-2 rounded-full text-sm font-semibold transition-all">
                    <span x-text="following ? '✓ फलो गरिएको' : '+ फलो गर्नुहोस्'"></span>
                </button>
            </div>
            @endif
            @endauth
        </div>

        {{-- Related --}}
        @if($related->count())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-4 font-nepali">सम्बन्धित लेखहरू</h3>
            <div class="space-y-4">
                @foreach($related as $rel)
                <a href="/posts/{{ $rel->slug }}" class="flex gap-3 group">
                    @if($rel->thumbnail_url)
                    <img src="{{ $rel->thumbnail_url }}" alt="" class="w-16 h-12 object-cover rounded-lg flex-shrink-0">
                    @else
                    <div class="w-16 h-12 rounded-lg bg-brand-100 flex-shrink-0 flex items-center justify-center text-brand-400 font-bold">
                        {{ strtoupper(substr($rel->title, 0, 1)) }}
                    </div>
                    @endif
                    <div>
                        <p class="text-sm font-medium text-gray-800 group-hover:text-brand-600 line-clamp-2 transition-colors font-nepali">{{ $rel->title }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $rel->published_at->diffForHumans() }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Tag-based recommendations --}}
        @if(isset($tagRecommended) && $tagRecommended->count())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-4 text-sm">तपाईंलाई मन पर्न सक्छ</h3>
            <div class="space-y-3">
                @foreach($tagRecommended as $rec)
                <a href="/posts/{{ $rec->slug }}" class="flex gap-3 group">
                    @if($rec->thumbnail_url)
                    <img src="{{ $rec->thumbnail_url }}" loading="lazy" alt="" class="w-14 h-10 object-cover rounded-lg flex-shrink-0">
                    @else
                    <div class="w-14 h-10 rounded-lg bg-indigo-50 flex-shrink-0 flex items-center justify-center text-indigo-400 font-bold text-xs">
                        {{ strtoupper(substr($rec->title, 0, 1)) }}
                    </div>
                    @endif
                    <div>
                        <p class="text-sm font-medium text-gray-800 group-hover:text-brand-600 line-clamp-2 transition-colors font-nepali leading-snug">{{ $rec->title }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $rec->category?->name_ne ?? $rec->category?->name_en }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Tags --}}
        @if($post->tags->count())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-3 text-sm">ट्यागहरू</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                <a href="/tag/{{ $tag->slug }}" class="text-xs px-3 py-1 bg-gray-100 text-gray-600 rounded-full hover:bg-brand-50 hover:text-brand-600 transition-colors">
                    #{{ $tag->name_en }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Sidebar widgets (sidebar + post_sidebar positions) --}}
        @if(isset($sidebarWidgets) && $sidebarWidgets->isNotEmpty())
            @foreach($sidebarWidgets as $widget)
                @include('partials._widget', ['widget' => $widget, 'data' => $widgetData ?? []])
            @endforeach
        @endif
    </aside>
</div>
@endsection
