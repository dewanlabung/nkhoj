@extends('layouts.app')

@section('title', 'Pages Map — Discover Local')

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" defer></script>
@endpush

@section('content')
<div class="flex flex-col" style="height: calc(100vh - 64px)">

    {{-- Top bar --}}
    <div class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3 flex-shrink-0">
        <a href="/pages" class="p-2 -ml-2 text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1">
            <h1 class="font-bold text-gray-900 text-sm">Pages Map</h1>
            <p class="text-xs text-gray-400" id="pin-count">Loading...</p>
        </div>
        <button onclick="locateMe()" class="flex items-center gap-1.5 text-sm text-blue-600 font-semibold hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Near me
        </button>
    </div>

    {{-- Category filter pills --}}
    <div class="bg-white border-b border-gray-100 px-4 py-2 flex gap-2 overflow-x-auto flex-shrink-0">
        @foreach(['All', 'Business', 'Education', 'Music', 'Food', 'Health', 'Tech', 'Sports', 'Art'] as $cat)
        <button onclick="filterCategory('{{ $cat }}')" data-cat="{{ $cat }}"
            class="cat-pill px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap border transition
                   {{ $cat === 'All' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' }}">
            {{ $cat }}
        </button>
        @endforeach
    </div>

    {{-- Map --}}
    <div id="pages-map" class="flex-1"></div>

    {{-- Page detail drawer --}}
    <div id="page-drawer" class="hidden fixed bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-2xl border-t border-gray-200 p-4 z-[9999] max-w-xl mx-auto">
        <div class="w-10 h-1 bg-gray-300 rounded-full mx-auto mb-4"></div>
        <div class="flex items-start gap-3">
            <img id="drawer-avatar" src="" class="w-14 h-14 rounded-full object-cover border border-gray-200 flex-shrink-0">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1">
                    <p id="drawer-name" class="font-bold text-gray-900 truncate"></p>
                    <svg id="drawer-verified" class="w-4 h-4 text-blue-500 hidden" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </div>
                <p id="drawer-category" class="text-xs text-blue-600 font-medium"></p>
                <p id="drawer-followers" class="text-xs text-gray-400 mt-0.5"></p>
                <p id="drawer-rating" class="text-xs text-yellow-500 font-medium hidden"></p>
            </div>
            <button onclick="document.getElementById('page-drawer').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="mt-4 flex gap-2">
            <a id="drawer-link" href="#" class="flex-1 text-center py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                View Page
            </a>
            <a id="drawer-directions" href="#" target="_blank"
               class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">
                Directions
            </a>
        </div>
    </div>
</div>

<script>
let allPins = [];
let markers = [];
let map;
let currentCat = 'All';

document.addEventListener('DOMContentLoaded', function () {
    map = L.map('pages-map').setView([27.7172, 85.3240], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    fetch('/api/pages/map-pins')
        .then(r => r.json())
        .then(pins => {
            allPins = pins;
            document.getElementById('pin-count').textContent = pins.length + ' pages on map';
            renderPins(pins);
            if (pins.length) {
                const lats = pins.map(p => p.lat);
                const lngs = pins.map(p => p.lng);
                map.fitBounds([[Math.min(...lats), Math.min(...lngs)], [Math.max(...lats), Math.max(...lngs)]], { padding: [40, 40] });
            }
        });
});

function renderPins(pins) {
    markers.forEach(m => map.removeLayer(m));
    markers = [];

    pins.forEach(pin => {
        const icon = L.divIcon({
            className: '',
            html: `<div style="width:36px;height:36px;border-radius:50%;border:3px solid #2563eb;overflow:hidden;background:#e5e7eb;box-shadow:0 2px 6px rgba(0,0,0,0.3)">
                     <img src="${pin.avatar}" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'">
                   </div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 18],
        });
        const m = L.marker([pin.lat, pin.lng], { icon }).addTo(map);
        m.on('click', () => showDrawer(pin));
        markers.push(m);
    });
}

function filterCategory(cat) {
    currentCat = cat;
    document.querySelectorAll('.cat-pill').forEach(el => {
        const active = el.dataset.cat === cat;
        el.className = el.className.replace(/bg-blue-600 text-white border-blue-600|bg-white text-gray-600 border-gray-300 hover:bg-gray-50/g, '');
        el.classList.add(...(active ? ['bg-blue-600', 'text-white', 'border-blue-600'] : ['bg-white', 'text-gray-600', 'border-gray-300', 'hover:bg-gray-50']));
    });
    const filtered = cat === 'All' ? allPins : allPins.filter(p => p.category === cat);
    document.getElementById('pin-count').textContent = filtered.length + ' pages on map';
    renderPins(filtered);
}

function showDrawer(pin) {
    document.getElementById('drawer-avatar').src     = pin.avatar;
    document.getElementById('drawer-name').textContent = pin.name;
    document.getElementById('drawer-category').textContent = pin.category || '';
    document.getElementById('drawer-followers').textContent = pin.followers_count.toLocaleString() + ' followers';
    document.getElementById('drawer-link').href = '/pages/' + pin.slug;
    document.getElementById('drawer-directions').href =
        `https://www.openstreetmap.org/?mlat=${pin.lat}&mlon=${pin.lng}&zoom=15`;

    const verified = document.getElementById('drawer-verified');
    pin.is_verified ? verified.classList.remove('hidden') : verified.classList.add('hidden');

    const ratingEl = document.getElementById('drawer-rating');
    if (pin.rating_avg > 0) {
        ratingEl.textContent = '⭐ ' + parseFloat(pin.rating_avg).toFixed(1);
        ratingEl.classList.remove('hidden');
    } else {
        ratingEl.classList.add('hidden');
    }

    document.getElementById('page-drawer').classList.remove('hidden');
    map.setView([pin.lat, pin.lng], 15, { animate: true });
}

function locateMe() {
    if (!navigator.geolocation) return alert('Geolocation not supported.');
    navigator.geolocation.getCurrentPosition(pos => {
        const { latitude: lat, longitude: lng } = pos.coords;
        map.setView([lat, lng], 13);
        L.circle([lat, lng], { radius: 500, color: '#2563eb', fillOpacity: 0.1 }).addTo(map);
        // sort by proximity and rerender
        const sorted = [...allPins].sort((a, b) =>
            Math.pow(a.lat - lat, 2) + Math.pow(a.lng - lng, 2) -
            Math.pow(b.lat - lat, 2) - Math.pow(b.lng - lng, 2)
        );
        renderPins(sorted);
        document.getElementById('pin-count').textContent = 'Sorted by distance from you';
    }, () => alert('Could not get your location.'));
}
</script>
@endsection
