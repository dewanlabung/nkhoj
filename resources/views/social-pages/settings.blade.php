@extends('layouts.app')

@section('title', 'Page Settings — ' . $page->name)
@php $pageCategories = $page->categories ?? []; @endphp

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

    <form method="POST" action="/pages/{{ $page->slug }}/settings" enctype="multipart/form-data" class="max-w-xl mx-auto px-4 py-6 space-y-1">
        @csrf @method('PUT')

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm mb-4">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm mb-4">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        {{-- GROUP 1: Page setup --}}
        <div x-data="{ open: true }">
        <div @click="open = !open" class="pt-1 pb-1 px-1 flex items-center justify-between cursor-pointer select-none">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Page setup</p>
            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div x-show="open" x-cloak class="space-y-1">

        {{-- Page name --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <a href="#page-name-section" class="px-4 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Name</p>
                        <p class="text-gray-400 text-xs">{{ $page->name }}</p>
                    </div>
                </div>
            </a>
        </div>

        {{-- Identity details (expanded card) --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden" id="page-name-section">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <p class="font-bold text-gray-900">Page identity</p>
            </div>
            <div class="p-4 space-y-4">
                {{-- Avatar + cover --}}
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-200 ring-2 ring-gray-100">
                            <img src="{{ $page->avatar }}" alt="" class="w-full h-full object-cover" id="avatar-preview">
                        </div>
                        <label class="absolute bottom-0 right-0 bg-gray-100 hover:bg-gray-200 rounded-full p-1 cursor-pointer shadow border border-white transition">
                            <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <input type="file" name="avatar" accept="image/*" class="hidden" onchange="previewImage(this,'avatar-preview')">
                        </label>
                    </div>
                    <div class="flex-1">
                        <div class="relative h-20 rounded-xl overflow-hidden bg-gray-200 mb-2">
                            @if($page->cover_url)
                                <img src="{{ $page->cover_url }}" class="w-full h-full object-cover" id="cover-preview">
                            @else
                                <img src="" class="w-full h-full object-cover hidden" id="cover-preview">
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">No cover</div>
                            @endif
                        </div>
                        <label class="cursor-pointer flex items-center gap-1.5 text-blue-600 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Change cover photo
                            <input type="file" name="cover" accept="image/*" class="hidden" onchange="previewImage(this,'cover-preview')">
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Page name</label>
                    <input type="text" name="name" value="{{ old('name', $page->name) }}" required maxlength="150"
                           class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Username <span class="text-gray-400 font-normal text-xs">(@handle)</span></label>
                    <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden focus-within:border-blue-500">
                        <span class="px-3 py-3 text-sm text-gray-400 bg-gray-50 border-r border-gray-200 select-none">@</span>
                        <input type="text" name="username" value="{{ old('username', $page->username) }}" maxlength="60"
                               placeholder="yourpagename" pattern="[a-zA-Z0-9._-]+"
                               class="flex-1 px-3 py-3 text-sm focus:outline-none bg-transparent">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Letters, numbers, dots, dashes only. Used in @mentions.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Page type</label>
                    <select name="page_type" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                        @foreach(['other' => 'General / Other', 'business' => 'Business', 'creator' => 'Creator / Influencer', 'community' => 'Community', 'nonprofit' => 'Non-profit / NGO'] as $val => $label)
                        <option value="{{ $val }}" {{ ($page->page_type ?? 'other') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bio</label>
                    <textarea name="bio" rows="3" maxlength="500"
                              class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 resize-none">{{ old('bio', $page->bio) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Categories <span class="text-gray-400 font-normal text-xs">(up to 3)</span></label>
                    <div class="flex flex-wrap gap-2"
                         @change="if(document.querySelectorAll('input[name=\'categories[]\']:checked').length > 3) $event.target.checked = false">
                        @foreach($categories as $cat)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="categories[]" value="{{ $cat }}"
                                   class="sr-only peer"
                                   {{ in_array($cat, $pageCategories) ? 'checked' : '' }}
                                   onchange="limitCats(this)">
                            <span class="px-3 py-1.5 rounded-full text-xs font-semibold border transition
                                         peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                         bg-white text-gray-600 border-gray-300 hover:bg-gray-50">{{ $cat }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        </div>
        </div>

        {{-- GROUP 2: Contact & location --}}
        <div x-data="{ open: true }">
        <div @click="open = !open" class="pt-3 pb-1 px-1 flex items-center justify-between cursor-pointer select-none">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Contact &amp; location</p>
            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div x-show="open" x-cloak class="space-y-1">

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <p class="font-bold text-gray-900">Contact info</p>
            </div>
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

        {{-- Social links --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                <p class="font-bold text-gray-900">Social media links</p>
            </div>
            @php $sl = $page->social_links ?? []; @endphp
            <div class="p-4 space-y-2">
                @foreach(['instagram' => ['Instagram', 'https://instagram.com/...'], 'twitter' => ['X / Twitter', 'https://x.com/...'], 'youtube' => ['YouTube', 'https://youtube.com/...'], 'tiktok' => ['TikTok', 'https://tiktok.com/...'], 'facebook' => ['Facebook', 'https://facebook.com/...'], 'linkedin' => ['LinkedIn', 'https://linkedin.com/...'],] as $key => [$label, $ph])
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-500 w-20 shrink-0">{{ $label }}</span>
                    <input type="url" name="social_links[{{ $key }}]" value="{{ old('social_links.'.$key, $sl[$key] ?? '') }}"
                           placeholder="{{ $ph }}"
                           class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <p class="font-bold text-gray-900">Location & Map</p>
            </div>
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
                        <button type="button" onclick="geocodeAddress()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">Search</button>
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

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-bold text-gray-900">Business Hours</p>
            </div>
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

        </div>

        </div>

        {{-- GROUP 3: Audience & visibility --}}
        <div x-data="{ open: true }">
        <div @click="open = !open" class="pt-3 pb-1 px-1 flex items-center justify-between cursor-pointer select-none">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Audience &amp; visibility</p>
            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div x-show="open" x-cloak class="space-y-1">

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <p class="font-bold text-gray-900">Page and tagging</p>
            </div>
            <div class="p-4 space-y-4">
                <label class="flex items-center justify-between gap-3 cursor-pointer">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Allow tagging</p>
                        <p class="text-xs text-gray-400">Let other users tag this page in their posts</p>
                    </div>
                    <div class="relative flex-shrink-0">
                        <input type="checkbox" name="allow_tagging" value="1" class="sr-only peer"
                            {{ $page->allow_tagging ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition peer-checked:translate-x-5"></div>
                    </div>
                </label>
                <label class="flex items-center justify-between py-3 border-t border-gray-100 cursor-pointer">
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Appear in recommendations</p>
                        <p class="text-xs text-gray-400">Show this page in Discover and suggested pages</p>
                    </div>
                    <div class="relative flex-shrink-0">
                        <input type="checkbox" name="allow_recommendations" value="1" class="sr-only peer"
                            {{ ($page->allow_recommendations ?? true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition peer-checked:translate-x-5"></div>
                    </div>
                </label>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <div>
                    <p class="font-bold text-gray-900">Post privacy default</p>
                    <p class="text-xs text-gray-400">Who can see new posts by default</p>
                </div>
            </div>
            <div class="p-4 space-y-2">
                @foreach(['public' => ['Public', 'Anyone can see new posts'], 'followers' => ['Followers only', 'Only people who follow this page']] as $val => [$label, $desc])
                <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition
                    {{ ($page->posts_privacy_default ?? 'public') === $val ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}">
                    <input type="radio" name="posts_privacy_default" value="{{ $val }}" class="text-blue-600"
                        {{ ($page->posts_privacy_default ?? 'public') === $val ? 'checked' : '' }}>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $label }}</p>
                        <p class="text-xs text-gray-500">{{ $desc }}</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        </div>
        </div>

        {{-- GROUP 4: Monetization & CTAs --}}
        <div x-data="{ open: true }">
        <div @click="open = !open" class="pt-3 pb-1 px-1 flex items-center justify-between cursor-pointer select-none">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Monetization &amp; CTAs</p>
            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div x-show="open" x-cloak class="space-y-1">

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <div>
                    <p class="font-bold text-gray-900">Action button</p>
                    <p class="text-xs text-gray-400">Button shown on your page header (Book Now, Contact, etc.)</p>
                </div>
            </div>
            <div class="p-4 space-y-3" x-data="{ type: '{{ $page->action_button_type }}' }">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Button type</label>
                    <select name="action_button_type" x-model="type"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                        <option value="">None (disable)</option>
                        <option value="book">Book Now</option>
                        <option value="contact">Contact Us</option>
                        <option value="shop">Shop Now</option>
                        <option value="call">Call Now</option>
                        <option value="email">Send Email</option>
                        <option value="website">Visit Website</option>
                        <option value="donate">Donate</option>
                        <option value="signup">Sign Up</option>
                        <option value="learn_more">Learn More</option>
                    </select>
                </div>
                <div x-show="type">
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Button label (optional)</label>
                    <input type="text" name="action_button_text" value="{{ $page->action_button_text }}" maxlength="60"
                        placeholder="e.g. Book a table"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div x-show="type">
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Destination URL or email</label>
                    <input type="text" name="action_button_url" value="{{ $page->action_button_url }}"
                        placeholder="https://... or mailto:..."
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                <div>
                    <p class="font-bold text-gray-900">Donation button</p>
                    <p class="text-xs text-gray-400">Add a fundraising or support link for followers to contribute</p>
                </div>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Donation URL</label>
                    <input type="url" name="donation_url" value="{{ old('donation_url', $page->donation_url) }}"
                           placeholder="https://donate.example.com or PayPal link"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Button label <span class="normal-case text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" name="donation_label" value="{{ old('donation_label', $page->donation_label) }}"
                           maxlength="60" placeholder="e.g. Support us, Donate, Buy us a coffee"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>
            </div>
        </div>

        </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition mt-4">
            Save changes
        </button>
    </form>

    {{-- GROUP 5: Page status (separate from main form) --}}
    <div class="max-w-xl mx-auto px-4 pt-3 pb-10">
        <div x-data="{ open: true }">
        <div @click="open = !open" class="pt-1 pb-1 px-1 flex items-center justify-between cursor-pointer select-none">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Page status</p>
            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div x-show="open" x-cloak class="space-y-1">

        {{-- Archive page --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-4 flex items-center gap-3">
                <div class="w-9 h-9 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-900 text-sm">Archive page</p>
                    <p class="text-xs text-gray-400">Temporarily hide from public. Restore anytime.</p>
                </div>
                @if($page->is_archived ?? false)
                    <form method="POST" action="/pages/{{ $page->slug }}/unarchive">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-green-600 hover:underline">Restore</button>
                    </form>
                @else
                    <form method="POST" action="/pages/{{ $page->slug }}/archive" onsubmit="return confirm('Archive this page?')">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-amber-600 hover:underline">Archive</button>
                    </form>
                @endif
            </div>
            @if($page->is_archived ?? false)
            <div class="px-4 pb-3">
                <p class="text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2 font-medium">This page is archived and not visible to the public.</p>
            </div>
            @endif
        </div>

        {{-- Page management history link --}}
        <a href="/pages/{{ $page->slug }}/activity-log" class="bg-white rounded-2xl shadow-sm overflow-hidden flex items-center gap-3 px-4 py-4 hover:bg-gray-50 transition">
            <div class="w-9 h-9 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-900 text-sm">Page management history</p>
                <p class="text-xs text-gray-400">History of actions taken by people who manage this page</p>
            </div>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>

        {{-- Blocked users link --}}
        <a href="/pages/{{ $page->slug }}/blocked-users" class="bg-white rounded-2xl shadow-sm overflow-hidden flex items-center gap-3 px-4 py-4 hover:bg-gray-50 transition">
            <div class="w-9 h-9 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-900 text-sm">Blocking</p>
                <p class="text-xs text-gray-400">Manage who is blocked from this page</p>
            </div>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>

        </div>
        </div>
    </div>
</div>

<script>
function limitCats(el) {
    const all = document.querySelectorAll('input[name="categories[]"]:checked');
    if (all.length > 3) el.checked = false;
}

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
