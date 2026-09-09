@extends('layouts.app')

@section('title', 'Saved Items')

@section('content')
<div class="min-h-screen bg-gray-50">

    {{-- Header --}}
    <div class="sticky top-0 z-20 bg-white border-b border-gray-200">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center gap-3">
            <a href="javascript:history.back()" class="p-2 -ml-2 text-gray-500 hover:text-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="font-bold text-gray-900 text-lg leading-none">Saved</h1>
                <p class="text-xs text-gray-500">Only you can see what you've saved</p>
            </div>
        </div>

        {{-- Filter tabs --}}
        <div class="max-w-5xl mx-auto px-4 flex gap-1 pb-0 overflow-x-auto scrollbar-hide">
            @php
                $tabs = [
                    'all'   => 'All Saved',
                    'post'  => 'Articles',
                    'page'  => 'Pages',
                    'video' => 'Videos',
                    'reel'  => 'Reels',
                    'event' => 'Events',
                ];
                $disabled = ['video', 'reel', 'event'];
            @endphp
            @foreach($tabs as $key => $label)
                @if(in_array($key, $disabled))
                    <span class="shrink-0 px-4 py-2.5 text-sm font-medium text-gray-300 cursor-not-allowed border-b-2 border-transparent">
                        {{ $label }}
                    </span>
                @else
                    <a href="/bookmarks?type={{ $key }}"
                       class="shrink-0 px-4 py-2.5 text-sm font-medium border-b-2 transition whitespace-nowrap
                              {{ $filter === $key
                                  ? 'border-blue-600 text-blue-600'
                                  : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                        {{ $label }}
                    </a>
                @endif
            @endforeach
            @foreach($collections as $col)
                <a href="/bookmarks?collection={{ $col->id }}"
                   class="shrink-0 px-4 py-2.5 text-sm font-medium border-b-2 transition whitespace-nowrap
                          {{ request('collection') == $col->id
                              ? 'border-brand-500 text-brand-600'
                              : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                    📁 {{ $col->name }}
                    <span class="ml-1 text-xs text-gray-400">({{ $col->bookmarks_count }})</span>
                </a>
            @endforeach
        </div>

        {{-- New collection button --}}
        <div class="max-w-5xl mx-auto px-4 py-2 flex justify-end"
            x-data="{ open: false, name: '' }">
            <button @click="open = !open" class="text-xs text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Collection
            </button>
            <div x-show="open" x-cloak class="absolute mt-6 right-4 bg-white border border-gray-200 rounded-xl shadow-lg p-4 z-30 w-64">
                <p class="text-sm font-semibold text-gray-700 mb-2">Create Collection</p>
                <input x-model="name" type="text" placeholder="e.g. Politics, Sports..."
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 mb-2">
                <div class="flex gap-2">
                    <button @click="open=false;name=''" class="text-xs text-gray-400 hover:text-gray-600">Cancel</button>
                    <button @click="
                        if(!name.trim()) return;
                        fetch('/bookmark-collections', {
                            method: 'POST',
                            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                            body: JSON.stringify({name})
                        }).then(()=>location.reload());
                    " class="ml-auto px-3 py-1 bg-brand-500 text-white text-xs font-semibold rounded-lg hover:bg-brand-600">
                        Create
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 py-6">

        @if($bookmarks->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($bookmarks as $bookmark)
                    @php $item = $bookmark->bookmarkable; @endphp
                    @if(!$item) @continue @endif

                    @if($bookmark->bookmarkable_type === 'App\Models\Post')
                        {{-- Article card --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group relative">
                            <a href="/posts/{{ $item->slug }}">
                                @if($item->thumbnail_url)
                                    <img src="{{ $item->thumbnail_url }}"
                                         class="w-full h-40 object-cover group-hover:opacity-95 transition">
                                @else
                                    <div class="w-full h-40 bg-gradient-to-br from-blue-100 to-indigo-200 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </a>
                            <div class="p-4">
                                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Article</span>
                                <a href="/posts/{{ $item->slug }}"
                                   class="block font-bold text-gray-900 text-sm mt-1 line-clamp-2 group-hover:text-blue-600 transition">
                                    {{ $item->title }}
                                </a>
                                <p class="text-xs text-gray-400 mt-1">Saved {{ $bookmark->created_at->diffForHumans() }}</p>
                            </div>
                            {{-- Unsave button --}}
                            <button onclick="unsave({{ $bookmark->id }}, this)"
                                    class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm rounded-full p-1.5 shadow text-gray-500 hover:text-red-500 transition"
                                    title="Remove from saved">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                                </svg>
                            </button>
                        </div>

                    @elseif($bookmark->bookmarkable_type === 'App\Models\SocialPage')
                        {{-- Page card --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
                            <a href="/pages/{{ $item->slug }}">
                                <div class="h-24 {{ $item->cover_url ? '' : 'bg-gradient-to-br from-orange-400 to-amber-500' }}">
                                    @if($item->cover_url)
                                        <img src="{{ $item->cover_url }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                            </a>
                            <div class="px-4 pb-4 -mt-6">
                                <div class="w-14 h-14 rounded-full border-2 border-white overflow-hidden bg-gray-200 mb-2 shadow">
                                    <img src="{{ $item->avatar }}" class="w-full h-full object-cover">
                                </div>
                                <span class="text-xs font-semibold text-orange-600 uppercase tracking-wide">Page</span>
                                <a href="/pages/{{ $item->slug }}"
                                   class="block font-bold text-gray-900 text-sm hover:text-blue-600 transition line-clamp-1">
                                    {{ $item->name }}
                                    @if($item->is_verified)
                                        <svg class="w-3.5 h-3.5 text-blue-500 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </a>
                                <p class="text-gray-400 text-xs">{{ number_format($item->followers_count) }} followers · Saved {{ $bookmark->created_at->diffForHumans() }}</p>
                            </div>
                            {{-- Unsave button --}}
                            <button onclick="unsave({{ $bookmark->id }}, this)"
                                    class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm rounded-full p-1.5 shadow text-gray-500 hover:text-red-500 transition"
                                    title="Remove from saved">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($bookmarks->hasPages())
                <div class="mt-8">{{ $bookmarks->links() }}</div>
            @endif

        @else
            {{-- Empty state --}}
            <div class="max-w-sm mx-auto text-center pt-16">
                <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                    </svg>
                </div>
                @if($filter !== 'all')
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No saved {{ ucfirst($filter) }}s yet</h3>
                    <p class="text-gray-500 text-sm mb-6">Items you save will appear here.</p>
                    <a href="/bookmarks" class="text-blue-600 font-semibold text-sm hover:underline">View all saved items</a>
                @else
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Save things for later</h3>
                    <p class="text-gray-500 text-sm">When you tap <strong>Save</strong> on a post, article, or page, it will appear here for easy access anytime.</p>
                @endif
            </div>
        @endif
    </div>
</div>

<script>
function unsave(bookmarkId, btn) {
    if (!confirm('Remove from saved?')) return;
    fetch('/bookmarks/' + bookmarkId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json'
        }
    }).then(r => r.json()).then(d => {
        if (d.success) {
            btn.closest('.relative, .bg-white').remove();
        }
    });
}
</script>
@endsection
