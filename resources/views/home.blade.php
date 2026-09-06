@extends('layouts.app')
@section('title', 'nkhoj — नेपाली समाचार')

@section('content')

{{-- ══ HERO STRIP: 3 cards like Naver Blog ══ --}}
@if($heroStrip->count())
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
    @foreach($heroStrip as $i => $hero)
    <a href="/posts/{{ $hero->slug }}" class="group relative rounded-xl overflow-hidden block {{ $i === 0 ? 'sm:col-span-1' : '' }}" style="min-height:200px;">
        {{-- Bg image or gradient --}}
        @if($hero->thumbnail_url)
        <img src="{{ $hero->thumbnail_url }}" alt="{{ $hero->title }}"
            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
        <div class="absolute inset-0 bg-gradient-to-br
            {{ $i === 0 ? 'from-brand-500 to-indigo-700' : ($i === 1 ? 'from-teal-500 to-cyan-700' : 'from-orange-500 to-pink-700') }}">
        </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
        <div class="relative flex flex-col justify-end h-full p-4" style="min-height:200px;">
            @if($hero->is_featured)
            <span class="text-xs font-bold text-brand-300 uppercase tracking-widest mb-1">Featured</span>
            @endif
            <span class="text-xs text-white/60 font-nepali mb-1">{{ $hero->category->name_ne ?? $hero->category->name_en }}</span>
            <h2 class="text-sm font-bold text-white line-clamp-2 leading-snug font-nepali group-hover:text-brand-200 transition-colors">{{ $hero->title }}</h2>
            <div class="flex items-center gap-2 mt-2 text-xs text-white/50">
                <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-xs">{{ strtoupper(substr($hero->author->name,0,1)) }}</div>
                <span>{{ $hero->author->name }}</span>
                <span>·</span>
                <span>{{ $hero->published_at->diffForHumans() }}</span>
            </div>
        </div>
    </a>
    @endforeach
</div>
@endif

{{-- ══ CATEGORY PILLS (below hero like Naver) ══ --}}
<div class="flex items-center gap-1.5 overflow-x-auto py-2 mb-6 border-b border-gray-200 scrollbar-hide">
    <a href="/" class="whitespace-nowrap px-4 py-1.5 text-sm font-semibold rounded-full {{ request()->is('/') && !request('cat') ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-100' }} transition-colors">
        सम्पूर्ण
    </a>
    @foreach(\App\Models\Category::orderBy('sort_order')->get() as $cat)
    <a href="/category/{{ $cat->slug }}" class="whitespace-nowrap px-4 py-1.5 text-sm font-medium rounded-full {{ request()->is('category/'.$cat->slug) ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-100' }} transition-colors font-nepali">
        {{ $cat->name_ne ?? $cat->name_en }}
    </a>
    @endforeach
</div>

{{-- ══ MAIN TWO-PANEL LAYOUT ══ --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    {{-- ══ FEED: Google Plus card grid (3 cols within the 3/4 space) ══ --}}
    <div class="lg:col-span-3">

        {{-- Editor's Pick row --}}
        @if($editorsPick->count())
        <div class="mb-7">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-xs font-bold text-brand-600 uppercase tracking-widest">सम्पादकको छनोट</span>
                <div class="flex-1 h-px bg-gray-100"></div>
                <span class="text-xs text-gray-400">Editor's Pick</span>
            </div>
            <div class="grid grid-cols-3 gap-3">
                @foreach($editorsPick as $pick)
                <a href="/posts/{{ $pick->slug }}" class="group bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden block">
                    <div class="relative" style="aspect-ratio:16/9; overflow:hidden;">
                        @if($pick->thumbnail_url)
                        <img src="{{ $pick->thumbnail_url }}" alt="{{ $pick->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                        <div class="w-full h-full bg-gradient-to-br from-brand-100 to-indigo-100 flex items-center justify-center">
                            <span class="text-3xl font-black text-brand-200">{{ strtoupper(substr($pick->title,0,1)) }}</span>
                        </div>
                        @endif
                        <div class="absolute top-2 left-2">
                            <span class="text-xs px-2 py-0.5 bg-brand-500 text-white rounded-full font-semibold">{{ $pick->category->name_ne ?? $pick->category->name_en }}</span>
                        </div>
                    </div>
                    <div class="p-3">
                        <h3 class="text-sm font-bold text-gray-900 group-hover:text-brand-600 transition-colors line-clamp-2 font-nepali leading-snug">{{ $pick->title }}</h3>
                        <div class="flex items-center gap-1.5 mt-1.5 text-xs text-gray-400">
                            <div class="w-4 h-4 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold text-xs">{{ strtoupper(substr($pick->author->name,0,1)) }}</div>
                            <span>{{ $pick->author->name }}</span>
                            <span>·</span>
                            <span>{{ number_format($pick->view_count) }} views</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ══ Google Plus-style card grid ══ --}}
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-widest">नवीनतम</h2>
            <div class="flex-1 h-px bg-gray-100"></div>
            <span class="text-xs text-gray-400">Latest posts</span>
        </div>

        {{-- Card grid: 2 columns --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse($posts as $post)
            <article class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden group flex flex-col">

                {{-- Card image (Google Plus style — image at top) --}}
                <a href="/posts/{{ $post->slug }}" class="block relative overflow-hidden flex-shrink-0" style="aspect-ratio:16/9;">
                    @if($post->thumbnail_url)
                    <img src="{{ $post->thumbnail_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div class="w-full h-full bg-gradient-to-br from-brand-50 via-indigo-50 to-brand-100 flex items-center justify-center">
                        <span class="text-4xl font-black text-brand-200/80">{{ strtoupper(substr($post->title,0,1)) }}</span>
                    </div>
                    @endif
                    <div class="absolute top-3 left-3">
                        <a href="/category/{{ $post->category->slug }}"
                            class="text-xs px-2.5 py-1 bg-brand-500 text-white rounded-full font-bold hover:bg-brand-600 transition-colors">
                            {{ $post->category->name_ne ?? $post->category->name_en }}
                        </a>
                    </div>
                </a>

                {{-- Card content --}}
                <div class="p-4 flex-1 flex flex-col">
                    {{-- Author row + Follow (Naver style inline) --}}
                    <div class="flex items-center gap-2 mb-2">
                        <a href="/profile/{{ $post->author->username }}" class="w-7 h-7 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 hover:ring-2 hover:ring-brand-300 transition-all">
                            {{ strtoupper(substr($post->author->name,0,1)) }}
                        </a>
                        <div class="flex-1 min-w-0">
                            <a href="/profile/{{ $post->author->username }}" class="text-xs font-semibold text-gray-700 hover:text-brand-600 transition-colors">{{ $post->author->name }}</a>
                            <div class="text-xs text-gray-400">{{ $post->published_at->diffForHumans() }}</div>
                        </div>
                        @auth
                        @if(auth()->id() !== $post->author_id)
                        <button onclick="toggleFollow(this, {{ $post->author_id }})"
                            class="text-xs px-2 py-1 border border-gray-200 rounded-full text-gray-500 hover:border-brand-400 hover:text-brand-600 transition-colors flex-shrink-0"
                            data-following="false">
                            + Add
                        </button>
                        @endif
                        @endauth
                    </div>

                    {{-- Title --}}
                    <h3 class="font-bold text-gray-900 group-hover:text-brand-600 transition-colors line-clamp-2 text-sm leading-snug font-nepali flex-1 mb-2">
                        <a href="/posts/{{ $post->slug }}">{{ $post->title }}</a>
                    </h3>

                    {{-- Excerpt --}}
                    @if($post->excerpt)
                    <p class="text-xs text-gray-500 line-clamp-2 mb-3 font-nepali leading-relaxed">{{ $post->excerpt }}</p>
                    @endif

                    {{-- Tags + views footer --}}
                    <div class="flex items-center justify-between pt-2 border-t border-gray-50">
                        <div class="flex gap-1">
                            @foreach($post->tags->take(2) as $tag)
                            <a href="/tag/{{ $tag->slug }}" class="text-xs text-brand-500 hover:text-brand-700 font-medium">#{{ $tag->name_en }}</a>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <span>{{ $post->readingTimeMinutes() }}min</span>
                            <span>·</span>
                            <span>{{ number_format($post->view_count) }} views</span>
                        </div>
                    </div>
                </div>
            </article>
            @empty
            <div class="col-span-2 text-center py-16 bg-white rounded-xl border border-gray-100">
                <div class="text-5xl mb-4">📰</div>
                <p class="text-gray-500 font-nepali">अहिलेसम्म कुनै लेख छैन।</p>
                @auth
                <a href="/dashboard/posts/create" class="mt-4 inline-block px-4 py-2 bg-brand-500 text-white text-sm rounded-lg hover:bg-brand-600">
                    पहिलो लेख लेख्नुहोस्
                </a>
                @endauth
            </div>
            @endforelse
        </div>

        @if($posts->hasPages())
        <div class="flex justify-center mt-8">{{ $posts->links() }}</div>
        @endif
    </div>

    {{-- ══ RIGHT SIDEBAR ══ --}}
    <aside class="space-y-4">

        {{-- User panel: always shown, not a widget --}}
        @auth
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-brand-500 to-indigo-600 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                    </div>
                    <div>
                        <p class="text-white font-semibold text-sm">{{ auth()->user()->name }}</p>
                        <p class="text-white/60 text-xs">{{ auth()->user()->username }}</p>
                    </div>
                </div>
                <form method="POST" action="/logout" class="inline">
                    @csrf
                    <button class="text-xs text-white/60 hover:text-white">log out</button>
                </form>
            </div>
            <div class="p-4 space-y-3">
                <div class="grid grid-cols-2 gap-2">
                    <a href="/profile/{{ auth()->user()->username }}"
                        class="text-center py-2 text-sm font-semibold text-brand-600 bg-brand-50 hover:bg-brand-100 rounded-lg transition-colors border border-brand-200">
                        मेरो ब्लग
                    </a>
                    <a href="/dashboard/posts/create"
                        class="text-center py-2 text-sm font-semibold text-white bg-brand-500 hover:bg-brand-600 rounded-lg transition-colors">
                        ✍ लेख्नुहोस्
                    </a>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-700 pt-3">
                    <div class="flex border-b border-gray-100 dark:border-gray-700 mb-2 text-xs">
                        <span class="pb-1.5 px-2 font-semibold text-brand-600 border-b-2 border-brand-500">मेरो समाचार</span>
                        <a href="/dashboard" class="pb-1.5 px-2 text-gray-400 hover:text-gray-600">गतिविधि</a>
                    </div>
                    <p class="text-xs text-gray-400 text-center py-3 font-nepali">नयाँ समाचार छैन।</p>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 text-center">
            <div class="text-4xl mb-3">✍️</div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-1 font-nepali">नखोजमा सामेल हुनुहोस्</h3>
            <p class="text-xs text-gray-400 mb-4">हजारौं नेपाली पाठकसँग आफ्नो कथा साझा गर्नुहोस्।</p>
            <a href="/register" class="block text-center bg-brand-500 text-white font-semibold text-sm py-2.5 rounded-lg hover:bg-brand-600 transition-colors mb-2">
                Free मा सुरु गर्नुहोस्
            </a>
            <a href="/login" class="block text-center text-sm text-gray-400 hover:text-gray-600">साइन इन</a>
        </div>
        @endauth

        {{-- Dynamic sidebar widgets from admin --}}
        @if($sidebarWidgets->isNotEmpty())
            @foreach($sidebarWidgets as $widget)
                @include('partials._widget', ['widget' => $widget, 'data' => $widgetData])
            @endforeach
        @else
            {{-- Fallback: hardcoded trending + categories when no widgets are configured --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
                <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm flex items-center gap-1.5">
                    <span class="text-red-500">🔥</span> ट्रेन्डिङ
                </h3>
                <ol class="space-y-2.5">
                    @foreach($trending as $i => $post)
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

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
                <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm">📂 विषयहरू</h3>
                <div class="space-y-1">
                    @foreach($categories as $cat)
                    <a href="/category/{{ $cat->slug }}"
                        class="flex items-center justify-between py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 transition-colors group">
                        <span class="font-nepali text-sm">{{ $cat->name_ne ?? $cat->name_en }}</span>
                        <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full group-hover:bg-brand-50 dark:group-hover:bg-brand-900/30 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ $cat->posts_count }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        @endif

    </aside>
</div>

@push('scripts')
<script>
async function toggleFollow(btn, userId) {
    btn.disabled = true;
    try {
        const r = await fetch('/follow/' + userId, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        });
        const d = await r.json();
        if (d.action === 'followed') {
            btn.textContent = '✓ Following';
            btn.classList.add('border-brand-400', 'text-brand-600');
            btn.dataset.following = 'true';
        } else {
            btn.textContent = '+ Add';
            btn.classList.remove('border-brand-400', 'text-brand-600');
            btn.dataset.following = 'false';
        }
    } catch(e) {}
    btn.disabled = false;
}
</script>
@endpush

@endsection
