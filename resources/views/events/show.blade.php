@extends('layouts.app')
@section('title', $event->title . ' – Nkhoj Events')

@push('head')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Event",
    "name": "{{ addslashes($event->title) }}",
    "startDate": "{{ $event->starts_at->toIso8601String() }}",
    @if($event->ends_at)"endDate": "{{ $event->ends_at->toIso8601String() }}",@endif
    "eventStatus": "https://schema.org/EventScheduled",
    "eventAttendanceMode": "{{ ($event->event_type ?? '') === 'online' ? 'https://schema.org/OnlineEventAttendanceMode' : 'https://schema.org/OfflineEventAttendanceMode' }}",
    @if($event->venue || $event->location)
    "location": {
        "@type": "Place",
        "name": "{{ addslashes($event->venue ?? $event->location) }}"
        @if($event->location), "address": "{{ addslashes($event->location) }}"@endif
    },
    @endif
    @if($event->thumbnail_url)"image": "{{ $event->thumbnail_url }}",@endif
    @if($event->description)"description": "{{ addslashes(strip_tags(Str::limit($event->description, 200))) }}",@endif
    @if($event->organizer)"organizer": { "@type": "Organization", "name": "{{ addslashes($event->organizer) }}" },@endif
    @if(!$event->is_free)
    "offers": {
        "@type": "Offer",
        "price": "{{ $event->ticket_price }}",
        "priceCurrency": "NPR",
        "availability": "https://schema.org/InStock"
    },
    @endif
    "url": "{{ url('/events/' . ($event->slug ?? $event->uuid)) }}"
}
</script>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-6">
        <a href="/events" class="hover:text-indigo-500">Events</a>
        @if($event->category)<span>/</span><a href="/events?category={{ urlencode($event->category) }}" class="hover:text-indigo-500">{{ $event->category }}</a>@endif
        <span>/</span><span class="text-gray-700 dark:text-gray-300 truncate">{{ $event->title }}</span>
    </nav>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden mb-6">
        @if($event->thumbnail_url)
        <div class="relative aspect-video overflow-hidden">
            <img src="{{ $event->thumbnail_url }}" class="w-full h-full object-cover" alt="{{ $event->title }}">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
        </div>
        @endif

        <div class="p-6 md:p-8">
            {{-- Type + category badges --}}
            <div class="flex flex-wrap gap-2 mb-4">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 capitalize">{{ str_replace('-', ' ', $event->event_type) }}</span>
                @if($event->category)<span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">{{ $event->category }}</span>@endif
                @if($event->is_free)<span class="px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-600">Free</span>
                @else<span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-50 text-yellow-700">Rs {{ number_format($event->ticket_price, 0) }}</span>@endif
            </div>

            <h1 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white mb-6">{{ $event->title }}</h1>

            {{-- Event info cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-3">
                    <p class="text-xs font-semibold text-indigo-500 mb-1">Date</p>
                    <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $event->starts_at->format('M j, Y') }}</p>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-3">
                    <p class="text-xs font-semibold text-purple-500 mb-1">Time</p>
                    <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $event->starts_at->format('g:i A') }}</p>
                </div>
                @if($event->venue || $event->location)
                <div class="bg-pink-50 dark:bg-pink-900/20 rounded-xl p-3 col-span-2">
                    <p class="text-xs font-semibold text-pink-500 mb-1">{{ $event->venue ? 'Venue' : 'Location' }}</p>
                    <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $event->venue ?? $event->location }}</p>
                    @if($event->venue && $event->location)<p class="text-xs text-gray-500">{{ $event->location }}</p>@endif
                </div>
                @endif
            </div>

            {{-- Attend buttons --}}
            @auth
            <div class="flex gap-3 mb-6" id="attend-section">
                <button onclick="attend('going')" id="btn-going"
                        class="flex-1 py-3 rounded-xl font-bold text-sm transition-all {{ $isGoing ? 'text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-50 hover:text-indigo-600' }}"
                        style="{{ $isGoing ? 'background:linear-gradient(135deg,#6366f1,#8b5cf6)' : '' }}">
                    ✓ Going <span id="going-count">({{ $event->going_count }})</span>
                </button>
                <button onclick="attend('interested')" id="btn-interested"
                        class="flex-1 py-3 rounded-xl font-bold text-sm transition-all {{ $interested ? 'bg-yellow-400 text-yellow-900' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-yellow-50 hover:text-yellow-600' }}">
                    ★ Interested <span id="interested-count">({{ $event->interested_count }})</span>
                </button>
            </div>
            @else
            <div class="flex gap-3 mb-6">
                <a href="/login" class="flex-1 py-3 rounded-xl font-bold text-sm text-center text-white" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">Login to RSVP</a>
            </div>
            @endauth

            @if($event->description)
            <div class="prose prose-sm dark:prose-invert max-w-none mb-6 text-gray-700 dark:text-gray-300 leading-relaxed">
                <h2 class="font-bold text-gray-900 dark:text-white mb-2">About this event</h2>
                <p>{{ $event->description }}</p>
            </div>
            @endif

            {{-- Organizer + external link --}}
            <div class="flex items-center justify-between flex-wrap gap-4 pt-6 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    @if($event->organizer()->first()?->avatar_url)
                        <img src="{{ $event->organizer->avatar_url }}" class="w-10 h-10 rounded-full object-cover">
                    @else
                        <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">{{ strtoupper(substr($event->organizer ?? 'O', 0, 1)) }}</div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-500">Organized by</p>
                        <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ $event->organizer ?? $event->organizer_name }}</p>
                    </div>
                </div>
                @if($event->registration_url)
                <a href="{{ $event->registration_url }}" target="_blank" rel="noopener"
                   class="px-6 py-2.5 rounded-xl font-bold text-white text-sm transition-all hover:opacity-90" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                    Register Now →
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Related --}}
    @if($related->count())
    <div>
        <h2 class="font-bold text-gray-900 dark:text-white text-lg mb-4">More Events</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($related as $ev)
            <a href="/events/{{ $ev->slug }}" class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition-all">
                <div class="relative aspect-video bg-gray-100 dark:bg-gray-700 overflow-hidden">
                    @if($ev->thumbnail_url)
                        <img src="{{ $ev->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-4xl" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">🎉</div>
                    @endif
                    <div class="absolute top-2 left-2 bg-white/90 dark:bg-gray-900/90 rounded-lg px-1.5 py-0.5 text-center">
                        <p class="text-xs font-black text-indigo-600">{{ $ev->starts_at->format('j') }}</p>
                        <p class="text-xs text-gray-500 uppercase leading-none">{{ $ev->starts_at->format('M') }}</p>
                    </div>
                </div>
                <div class="p-3">
                    <h3 class="font-bold text-gray-900 dark:text-white text-xs line-clamp-2 group-hover:text-indigo-500 transition-colors">{{ $ev->title }}</h3>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>

@auth
<script>
const eventSlug = '{{ $event->slug }}';
async function attend(status) {
    const res = await fetch(`/events/${eventSlug}/attend`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ status })
    });
    const data = await res.json();
    document.getElementById('going-count').textContent = `(${data.going_count})`;
    document.getElementById('interested-count').textContent = `(${data.interested_count})`;

    const btnGoing = document.getElementById('btn-going');
    const btnInt   = document.getElementById('btn-interested');

    if (status === 'going') {
        if (data.attending) {
            btnGoing.style.background = 'linear-gradient(135deg,#6366f1,#8b5cf6)';
            btnGoing.className = btnGoing.className.replace('bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-50 hover:text-indigo-600','') + ' text-white shadow-md';
        } else {
            btnGoing.style.background = '';
            btnGoing.className = 'flex-1 py-3 rounded-xl font-bold text-sm transition-all bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-50 hover:text-indigo-600';
        }
    } else {
        btnInt.className = data.attending
            ? 'flex-1 py-3 rounded-xl font-bold text-sm transition-all bg-yellow-400 text-yellow-900'
            : 'flex-1 py-3 rounded-xl font-bold text-sm transition-all bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-yellow-50 hover:text-yellow-600';
    }
}
</script>
@endauth
@endsection
