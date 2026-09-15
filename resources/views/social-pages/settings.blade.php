@extends('layouts.app')

@section('title', 'Page Settings — ' . $page->name)
@php $pageCategories = $page->categories ?? []; @endphp

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" defer></script>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">

    @if(isset($isOwner) && !$isOwner)
    <div class="bg-blue-600 text-white px-4 py-2 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        Managing <strong class="mx-1">{{ $page->name }}</strong> as <strong class="ml-1 capitalize">{{ $userRole ?? 'admin' }}</strong>
    </div>
    @endif

    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
        <a href="/pages/{{ $page->slug }}/dashboard" class="p-2 -ml-2 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="font-bold text-gray-900 text-lg">Settings & privacy</h1>
    </div>

    <form method="POST" action="/pages/{{ $page->slug }}/settings" enctype="multipart/form-data" class="max-w-xl mx-auto px-4 py-4 space-y-5">
        @csrf @method('PUT')

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        {{-- GROUP 1: Page setup --}}
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1 mb-2">Page setup</p>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden divide-y divide-gray-100">

                {{-- Photos --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Photos</p>
                            <p class="text-xs text-gray-400">Avatar and cover image</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100">
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
                    </div>
                </div>

                {{-- Page name --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/></svg>
                        </div>
                        <div class="flex-1"><p class="text-sm font-semibold text-gray-900">Page name</p></div>
                        <p class="text-sm text-gray-400 truncate max-w-[130px]">{{ $page->name }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100">
                        <input type="text" name="name" value="{{ old('name', $page->name) }}" required maxlength="150"
                               class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                {{-- Username --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div class="flex-1"><p class="text-sm font-semibold text-gray-900">Username</p></div>
                        <p class="text-sm text-gray-400 truncate max-w-[130px]">{{ $page->username ? '@'.$page->username : 'Not set' }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100">
                        <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden focus-within:border-blue-500">
                            <span class="px-3 py-3 text-sm text-gray-400 bg-gray-50 border-r border-gray-200 select-none">@</span>
                            <input type="text" name="username" value="{{ old('username', $page->username) }}" maxlength="60"
                                   placeholder="yourpagename" pattern="[a-zA-Z0-9._-]+"
                                   class="flex-1 px-3 py-3 text-sm focus:outline-none bg-transparent">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Letters, numbers, dots, dashes only.</p>
                    </div>
                </div>

                {{-- Page type --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-teal-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        </div>
                        <div class="flex-1"><p class="text-sm font-semibold text-gray-900">Page type</p></div>
                        @php $typeLabels = ['other'=>'General','business'=>'Business','community'=>'Community','nonprofit'=>'Non-profit','public_place'=>'Public Place']; @endphp
                        <p class="text-sm text-gray-400 truncate max-w-[130px]">{{ $typeLabels[$page->page_type ?? 'other'] ?? 'General' }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100">
                        <select name="page_type" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                            @foreach(['other' => 'General / Other', 'business' => 'Business', 'community' => 'Community', 'nonprofit' => 'Non-profit / NGO', 'public_place' => 'Public Place'] as $val => $label)
                            <option value="{{ $val }}" {{ ($page->page_type ?? 'other') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Bio --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                        </div>
                        <div class="flex-1"><p class="text-sm font-semibold text-gray-900">Bio</p></div>
                        <p class="text-sm text-gray-400 truncate max-w-[130px]">{{ $page->bio ? \Illuminate\Support\Str::limit($page->bio, 22) : 'Not set' }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100">
                        <textarea name="bio" rows="3" maxlength="500"
                                  class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 resize-none">{{ old('bio', $page->bio) }}</textarea>
                    </div>
                </div>

                {{-- Categories --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-pink-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </div>
                        <div class="flex-1"><p class="text-sm font-semibold text-gray-900">Categories</p></div>
                        <p class="text-sm text-gray-400 truncate max-w-[130px]">{{ count($pageCategories) > 0 ? implode(', ', array_slice($pageCategories, 0, 2)) : 'None' }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100">
                        <p class="text-xs text-gray-500 font-semibold mb-2">Select up to 3</p>
                        <div class="flex flex-wrap gap-2">
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

        {{-- GROUP 2: Contact & location --}}
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1 mb-2">Contact &amp; location</p>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden divide-y divide-gray-100">

                {{-- Website --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-sky-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </div>
                        <div class="flex-1"><p class="text-sm font-semibold text-gray-900">Website</p></div>
                        <p class="text-sm text-gray-400 truncate max-w-[130px]">{{ $page->website ?: 'Not set' }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100">
                        <input type="url" name="website" value="{{ old('website', $page->website) }}" placeholder="https://..."
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                {{-- Email --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="flex-1"><p class="text-sm font-semibold text-gray-900">Email</p></div>
                        <p class="text-sm text-gray-400 truncate max-w-[130px]">{{ $page->email ?: 'Not set' }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100">
                        <input type="email" name="email" value="{{ old('email', $page->email) }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                {{-- Phone --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-yellow-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div class="flex-1"><p class="text-sm font-semibold text-gray-900">Phone</p></div>
                        <p class="text-sm text-gray-400 truncate max-w-[130px]">{{ $page->phone ?: 'Not set' }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100">
                        <input type="text" name="phone" value="{{ old('phone', $page->phone) }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                {{-- Social links --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-pink-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                        @php $sl = $page->social_links ?? []; $slCount = count(array_filter($sl)); @endphp
                        <div class="flex-1"><p class="text-sm font-semibold text-gray-900">Social links</p></div>
                        <p class="text-sm text-gray-400 truncate max-w-[130px]">{{ $slCount > 0 ? $slCount.' connected' : 'None' }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100 space-y-2">
                        @foreach(['instagram'=>'Instagram','twitter'=>'X / Twitter','youtube'=>'YouTube','tiktok'=>'TikTok','facebook'=>'Facebook','linkedin'=>'LinkedIn'] as $key=>$label)
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 w-20 shrink-0">{{ $label }}</span>
                            <input type="url" name="social_links[{{ $key }}]" value="{{ old('social_links.'.$key, $sl[$key] ?? '') }}"
                                   placeholder="https://..."
                                   class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Location & map --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="flex-1"><p class="text-sm font-semibold text-gray-900">Location</p></div>
                        <p class="text-sm text-gray-400 truncate max-w-[130px]">{{ $page->location ?: 'Not set' }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100 space-y-3">
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
                                <input type="number" name="lat" id="lat-input" step="any" value="{{ old('lat', $page->lat) }}" placeholder="27.7172"
                                       class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Longitude</label>
                                <input type="number" name="lng" id="lng-input" step="any" value="{{ old('lng', $page->lng) }}" placeholder="85.3240"
                                       class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Business hours --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex-1"><p class="text-sm font-semibold text-gray-900">Business hours</p></div>
                        <p class="text-sm text-gray-400 truncate max-w-[130px]">{{ !empty($page->business_hours) ? 'Configured' : 'Not set' }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100 space-y-3">
                        @php
                            $daysMap = ['mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thu'=>'Thursday','fri'=>'Friday','sat'=>'Saturday','sun'=>'Sunday'];
                            $bh = $page->business_hours ?? [];
                        @endphp
                        @foreach($daysMap as $key => $dayLabel)
                        @php
                            $slot   = $bh[$key] ?? ['open'=>'09:00','close'=>'17:00','closed'=>false];
                            $closed = $slot['closed'] ?? false;
                        @endphp
                        <div class="flex items-center gap-3" x-data="{ closed: {{ $closed ? 'true' : 'false' }} }">
                            <div class="w-24 flex-shrink-0">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="hours_{{ $key }}_closed" value="1" x-model="closed" {{ $closed ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm font-medium text-gray-700">{{ $dayLabel }}</span>
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
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1 mb-2">Audience &amp; visibility</p>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden divide-y divide-gray-100">

                {{-- Allow tagging toggle --}}
                <label class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition">
                    <div class="w-9 h-9 bg-violet-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900">Allow tagging</p>
                        <p class="text-xs text-gray-400">Let others tag this page in posts</p>
                    </div>
                    <div class="relative flex-shrink-0">
                        <input type="checkbox" name="allow_tagging" value="1" class="sr-only peer" {{ $page->allow_tagging ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition peer-checked:translate-x-5"></div>
                    </div>
                </label>

                {{-- Appear in recommendations toggle --}}
                <label class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition">
                    <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900">Appear in recommendations</p>
                        <p class="text-xs text-gray-400">Show in Discover and suggested pages</p>
                    </div>
                    <div class="relative flex-shrink-0">
                        <input type="checkbox" name="allow_recommendations" value="1" class="sr-only peer" {{ ($page->allow_recommendations ?? true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition peer-checked:translate-x-5"></div>
                    </div>
                </label>

                {{-- Post privacy default --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Post privacy</p>
                            <p class="text-xs text-gray-400">Default visibility for new posts</p>
                        </div>
                        <p class="text-sm text-gray-400 mr-1">{{ ($page->posts_privacy_default ?? 'public') === 'public' ? 'Public' : 'Followers' }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100 space-y-2">
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
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1 mb-2">Monetization &amp; CTAs</p>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden divide-y divide-gray-100">

                {{-- Action button --}}
                <div x-data="{ open: false, type: '{{ $page->action_button_type }}' }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Action button</p>
                            <p class="text-xs text-gray-400">Button shown on your page header</p>
                        </div>
                        <p class="text-sm text-gray-400 mr-1">{{ $page->action_button_type ? ucfirst(str_replace('_',' ',$page->action_button_type)) : 'None' }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100 space-y-3">
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

                {{-- Donation button --}}
                <div x-data="{ open: false }">
                    <div @click="open = !open" class="px-4 py-3.5 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition select-none">
                        <div class="w-9 h-9 bg-rose-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Donation button</p>
                            <p class="text-xs text-gray-400">Fundraising or support link</p>
                        </div>
                        <p class="text-sm text-gray-400 mr-1">{{ $page->donation_url ? 'Set' : 'Not set' }}</p>
                        <svg class="w-4 h-4 text-gray-300 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div x-show="open" x-cloak class="px-4 pt-3 pb-4 border-t border-gray-100 space-y-3">
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

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition mt-2">
            Save changes
        </button>
    </form>

    {{-- GROUP 5: Page status (outside main form) --}}
    <div class="max-w-xl mx-auto px-4 pt-3 pb-10">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1 mb-2">Page status</p>
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden divide-y divide-gray-100">

            {{-- Archive page --}}
            <div class="px-4 py-3.5 flex items-center gap-3">
                <div class="w-9 h-9 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">Archive page</p>
                    <p class="text-xs text-gray-400">{{ ($page->is_archived ?? false) ? 'Currently archived' : 'Temporarily hide from public' }}</p>
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

            {{-- Activity log --}}
            <a href="/pages/{{ $page->slug }}/activity-log" class="px-4 py-3.5 flex items-center gap-3 hover:bg-gray-50 transition">
                <div class="w-9 h-9 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">Activity log</p>
                    <p class="text-xs text-gray-400">History of actions by page managers</p>
                </div>
                <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>

            {{-- Blocked users --}}
            <a href="/pages/{{ $page->slug }}/blocked-users" class="px-4 py-3.5 flex items-center gap-3 hover:bg-gray-50 transition">
                <div class="w-9 h-9 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">Blocking</p>
                    <p class="text-xs text-gray-400">Manage who is blocked from this page</p>
                </div>
                <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>

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
    const latEl = document.getElementById('lat-input');
    const lngEl = document.getElementById('lng-input');
    if (!latEl) return;
    const initLat = parseFloat(latEl.value) || 27.7172;
    const initLng = parseFloat(lngEl.value) || 85.3240;
    const hasPin  = latEl.value !== '';

    map = L.map('settings-map').setView([initLat, initLng], hasPin ? 14 : 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    if (hasPin) {
        marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);
        marker.on('dragend', updateFromMarker);
    }

    map.on('click', function (e) {
        setPin(e.latlng.lat, e.latlng.lng);
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
