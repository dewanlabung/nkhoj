@extends('layouts.app')
@section('title', 'Events – Nkhoj Community')

@section('content')
{{-- Hero --}}
<div class="relative rounded-2xl overflow-hidden mb-8" style="background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 50%,#ec4899 100%)">
    <div class="relative px-6 py-10 text-white">
        <p class="text-indigo-200 text-sm font-semibold uppercase tracking-widest mb-2">Happening Near You</p>
        <h1 class="text-3xl md:text-4xl font-black mb-3">Discover Events</h1>
        <p class="text-indigo-100 text-sm mb-6 max-w-lg">Find concerts, meetups, workshops, and community events around you.</p>
        <form method="GET" action="/events" class="flex gap-2 max-w-xl">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search events…"
                   class="flex-1 px-4 py-2.5 rounded-xl text-gray-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-white/50">
            <button class="px-5 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur rounded-xl text-sm font-bold transition-colors">Search</button>
        </form>
    </div>
</div>

<div class="flex gap-6">
    {{-- Left sidebar --}}
    <aside class="hidden lg:block w-56 flex-shrink-0 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Category</p>
            <div class="space-y-1">
                <a href="/events?tab={{ $tab }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ !$category ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750' }} transition-colors">All Categories</a>
                @foreach($categories as $cat)
                    <a href="/events?tab={{ $tab }}&category={{ urlencode($cat) }}{{ $search ? '&q='.urlencode($search) : '' }}"
                       class="block px-3 py-2 rounded-lg text-sm font-medium {{ $category === $cat ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750' }} transition-colors">{{ $cat }}</a>
                @endforeach
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Event Type</p>
            <div class="space-y-1">
                <a href="/events?tab={{ $tab }}{{ $category ? '&category='.urlencode($category) : '' }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ !$type ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750' }} transition-colors">All Types</a>
                @foreach(['in-person' => 'In Person', 'online' => 'Online', 'hybrid' => 'Hybrid'] as $val => $label)
                    <a href="/events?tab={{ $tab }}{{ $category ? '&category='.urlencode($category) : '' }}&type={{ $val }}"
                       class="block px-3 py-2 rounded-lg text-sm font-medium {{ $type === $val ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750' }} transition-colors">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        @auth
        <a href="/events/create" class="block w-full text-center py-3 rounded-2xl font-bold text-white text-sm transition-all"
           style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">+ Create Event</a>
        @endauth
    </aside>

    <div class="flex-1 min-w-0">
        {{-- Tab bar --}}
        <div class="flex items-center gap-1 mb-5 bg-white dark:bg-gray-800 rounded-2xl p-1.5 shadow-sm">
            @foreach(['upcoming' => 'Upcoming', 'featured' => 'Featured', 'past' => 'Past Events'] as $key => $label)
                <a href="/events?tab={{ $key }}{{ $category ? '&category='.urlencode($category) : '' }}{{ $type ? '&type='.$type : '' }}{{ $search ? '&q='.urlencode($search) : '' }}"
                   class="flex-1 text-center py-2 rounded-xl text-sm font-semibold transition-colors {{ $tab === $key ? 'bg-indigo-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
            @auth
            <a href="/events/create" class="ml-1 px-4 py-2 rounded-xl text-sm font-bold text-white hidden md:block"
               style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">+ Create</a>
            @endauth
        </div>

        {{-- Mobile category scroll --}}
        <div class="lg:hidden flex gap-2 overflow-x-auto pb-3 mb-4 scrollbar-hide">
            <a href="/events?tab={{ $tab }}" class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold {{ !$category ? 'bg-indigo-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 shadow-sm' }} transition-colors">All</a>
            @foreach($categories as $cat)
                <a href="/events?tab={{ $tab }}&category={{ urlencode($cat) }}" class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold {{ $category === $cat ? 'bg-indigo-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 shadow-sm' }} transition-colors">{{ $cat }}</a>
            @endforeach
        </div>

        {{-- Featured events --}}
        @if($tab === 'upcoming' && !$search && !$category && $featured->count())
        <div class="mb-6">
            <h2 class="font-bold text-gray-900 dark:text-white mb-3">Featured Events</h2>
            <div class="grid md:grid-cols-3 gap-4">
                @foreach($featured as $ev)
                <a href="/events/{{ $ev->slug }}" class="group relative rounded-2xl overflow-hidden shadow-sm bg-gray-100 dark:bg-gray-800" style="min-height:160px">
                    @if($ev->thumbnail_url)
                        <img src="{{ $ev->thumbnail_url }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center text-5xl" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">🎉</div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    <div class="absolute top-3 left-3">
                        <span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-0.5 rounded-full">Featured</span>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 p-4">
                        <p class="text-white font-bold text-sm line-clamp-2 mb-1">{{ $ev->title }}</p>
                        <p class="text-indigo-200 text-xs">{{ $ev->starts_at->format('M j, Y') }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Events list --}}
        @if($events->count())
        <div class="space-y-4">
            @foreach($events as $event)
            <a href="/events/{{ $event->slug }}" class="group flex gap-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4 hover:shadow-md transition-all">
                {{-- Date badge --}}
                <div class="flex-shrink-0 w-14 h-14 rounded-xl flex flex-col items-center justify-center text-white font-bold" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                    <span class="text-lg leading-none">{{ $event->starts_at->format('j') }}</span>
                    <span class="text-xs uppercase tracking-wide opacity-90">{{ $event->starts_at->format('M') }}</span>
                </div>

                {{-- Thumbnail --}}
                @if($event->thumbnail_url)
                <div class="hidden sm:block flex-shrink-0 w-24 h-20 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700">
                    <img src="{{ $event->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="{{ $event->title }}">
                </div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm group-hover:text-indigo-500 transition-colors line-clamp-2">{{ $event->title }}</h3>
                        @if($event->is_free)
                            <span class="flex-shrink-0 px-2 py-0.5 rounded-full text-xs font-bold bg-green-50 text-green-600">Free</span>
                        @else
                            <span class="flex-shrink-0 px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600">Rs {{ number_format($event->ticket_price, 0) }}</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-x-3 gap-y-1 mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $event->starts_at->format('g:i A') }}
                        </span>
                        @if($event->venue || $event->location)
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $event->venue ?? $event->location }}
                        </span>
                        @endif
                        <span class="px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 capitalize">{{ str_replace('-', ' ', $event->event_type) }}</span>
                        @if($event->category)
                        <span class="px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">{{ $event->category }}</span>
                        @endif
                    </div>
                    @if($event->going_count > 0 || $event->interested_count > 0)
                    <div class="flex gap-3 mt-2 text-xs text-gray-400 dark:text-gray-500">
                        @if($event->going_count > 0)<span>{{ $event->going_count }} going</span>@endif
                        @if($event->interested_count > 0)<span>{{ $event->interested_count }} interested</span>@endif
                    </div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>

        @if($events->hasPages())
        <div class="mt-8">{{ $events->links() }}</div>
        @endif

        @else
        <div class="text-center py-20">
            <p class="text-5xl mb-4">🎉</p>
            <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-2">No events found</h3>
            <p class="text-gray-500 text-sm mb-6">{{ $search ? "No events matching \"$search\"" : 'Be the first to create an event!' }}</p>
            @auth
            <a href="/events/create" class="inline-block px-6 py-3 rounded-xl text-white font-bold text-sm" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">Create an Event</a>
            @endauth
        </div>
        @endif
    </div>
</div>
@endsection
