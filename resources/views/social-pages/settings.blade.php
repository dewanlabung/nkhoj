@extends('layouts.app')

@section('title', 'Page Settings — ' . $page->name)

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" defer></script>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">

    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
        <a href="/pages/{{ $page->slug }}/dashboard" class="p-2 -ml-2 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="font-bold text-gray-900 text-lg">Settings & privacy</h1>
    </div>

    <form method="POST" action="/pages/{{ $page->slug }}/settings" enctype="multipart/form-data" class="max-w-xl mx-auto px-4 py-6 space-y-4">
        @csrf @method('PUT')

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        {{-- Cover photo --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100"><p class="font-bold text-gray-900">Cover photo</p></div>
            <div class="p-4">
                <div class="relative h-28 rounded-xl overflow-hidden bg-gray-200 mb-3">
                    @if($page->cover_url)
                        <img src="{{ $page->cover_url }}" class="w-full h-full object-cover" id="cover-preview">
                    @else
                        <img src="" class="w-full h-full object-cover hidden" id="cover-preview">
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>
                <label class="cursor-pointer flex items-center gap-2 text-blue-600 text-sm font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Upload cover photo
                    <input type="file" name="cover" accept="image/*" class="hidden" onchange="previewImage(this, 'cover-preview')">
                </label>
            </div>
        </div>

        {{-- Page details --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100"><p class="font-bold text-gray-900">Page details</p></div>
            <div class="p-4 space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-200">
                        <img src="{{ $page->avatar }}" alt="" class="w-full h-full object-cover" id="avatar-preview">
                    </div>
                    <label class="cursor-pointer flex items-center gap-2 text-blue-600 text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Change profile photo
                        <input type="file" name="avatar" accept="image/*" class="hidden" onchange="previewImage(this, 'avatar-preview')">
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Page name</label>
                    <input type="text" name="name" value="{{ old('name', $page->name) }}" required maxlength="150"
                           class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bio</label>
                    <textarea name="bio" rows="3" maxlength="500"
                              class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 resize-none">{{ old('bio', $page->bio) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Contact info --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100"><p class="font-bold text-gray-900">Contact info</p></div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Website</label>
                    <input type="url" name="website" value="{{ old('website', $page->website) }}" placeholder="https://..."
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Email</label>
                    <input type="email" name="email" value="{{ old('email', $page->email) }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $page->phone) }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>
            </div>
        </div>

        {{-- Location & Map --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100"><p class="font-bold text-gray-900">Location & Map</p></div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Address / Location name</label>
                    <input type="text" name="location" id="location-input" value="{{ old('location', $page->location) }}" placeholder="e.g. Kathmandu, Nepal"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Search on map</label>
                    <div class="flex gap-2">
                        <input type="text" id="geocode-input" placeholder="Type address and search..."
                               class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                        <button type="button" onclick="geocodeAddress()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                            Search
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Or click the map to set a pin</p>
                </div>
                <div id="settings-map" class="w-full h-48 rounded-xl overflow-hidden border border-gray-200"></div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Latitude</label>
                        <input type="number" name="lat" id="lat-input" step="any" value="{{ old('lat', $page->lat) }}" placeholder="e.g. 27.7172"
                               class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Longitude</label>
                        <input type="number" name="lng" id="lng-input" step="any" value="{{ old('lng', $page->lng) }}" placeholder="e.g. 85.3240"
                               class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                    </div>
                </div>
            </div>
        </div>

        {{-- Business Hours --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100"><p class="font-bold text-gray-900">Business Hours</p></div>
            <div class="p-4 space-y-3">
                @php
                    $daysMap = ['mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thu'=>'Thursday','fri'=>'Friday','sat'=>'Saturday','sun'=>'Sunday'];
                    $bh = $page->business_hours ?? [];
                @endphp
                @foreach($daysMap as $key => $label)
                @php
                    $slot   = $bh[$key] ?? ['open'=>'09:00','close'=>'17:00','closed'=>false];
                    $closed = $slot['closed'] ?? false;
                @endphp
                <div class="flex items-center gap-3" x-data="{ closed: {{ $closed ? 'true' : 'false' }} }">
                    <div class="w-24 flex-shrink-0">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="hours_{{ $key }}_closed" value="1" x-model="closed" {{ $closed ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                        </label>
                    </div>
                    <div class="flex-1 flex items-center gap-2" :class="closed ? 'opacity-40 pointer-events-none' : ''">
                        <input type="time" name="hours_{{ $key }}_open" value="{{ $slot['open'] ?? '09:00' }}"
                               class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500">
                        <span class="text-gray-400 text-sm">–</span>
                        <input type="time" name="hours_{{ $key }}_close" value="{{ $slot['close'] ?? '17:00' }}"
                               class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-blue-500">
                    </div>
                    <span x-show="closed" class="text-xs text-red-500 font-semibold w-14">Closed</span>
                </div>
                @endforeach
                <p class="text-xs text-gray-400">Check the box to mark a day as closed.</p>
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition">
            Save changes
        </button>
    </form>
</div>

<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById(previewId);
            img.src = e.target.result;
            img.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

let map, marker;
document.addEventListener('DOMContentLoaded', function () {
    const initLat = parseFloat(document.getElementById('lat-input').value) || 27.7172;
    const initLng = parseFloat(document.getElementById('lng-input').value) || 85.3240;
    const hasPin  = document.getElementById('lat-input').value !== '';

    map = L.map('settings-map').setView([initLat, initLng], hasPin ? 14 : 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    if (hasPin) {
        marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);
        marker.on('dragend', updateFromMarker);
    }

    map.on('click', function (e) {
        const { lat, lng } = e.latlng;
        setPin(lat, lng);
    });
});

function setPin(lat, lng) {
    document.getElementById('lat-input').value = lat.toFixed(7);
    document.getElementById('lng-input').value = lng.toFixed(7);
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', updateFromMarker);
    }
    map.setView([lat, lng], 15);
}

function updateFromMarker(e) {
    const { lat, lng } = e.target.getLatLng();
    document.getElementById('lat-input').value = lat.toFixed(7);
    document.getElementById('lng-input').value = lng.toFixed(7);
}

function geocodeAddress() {
    const q = document.getElementById('geocode-input').value.trim();
    if (!q) return;
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=1`)
        .then(r => r.json())
        .then(results => {
            if (results.length) {
                const { lat, lon, display_name } = results[0];
                setPin(parseFloat(lat), parseFloat(lon));
                if (!document.getElementById('location-input').value) {
                    document.getElementById('location-input').value = display_name.split(',').slice(0, 3).join(', ');
                }
            } else {
                alert('Location not found. Try a different search.');
            }
        });
}
</script>
@endsection
