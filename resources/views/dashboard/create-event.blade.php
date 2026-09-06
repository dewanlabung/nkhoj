@extends('layouts.app')
@section('title', 'Add Event — nkhoj')

@section('content')
<div x-data="eventEditor()" class="max-w-4xl mx-auto space-y-5">

    {{-- ═══ HEADER ══════════════════════════════════════════════ --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center text-base">📅</span>
                Add Event
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">Home / Posts / <span class="text-gray-700 dark:text-gray-300">Add Event</span></p>
        </div>
        <a href="/dashboard/posts" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Posts
        </a>
    </div>

    <form method="POST" action="/dashboard/posts" enctype="multipart/form-data" id="event-form">
        @csrf
        <input type="hidden" name="post_format" value="event">
        <input type="hidden" name="tags" :value="tags">

        <div class="space-y-4">

        {{-- ═══ IMAGE ════════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-5">Image</h2>

            {{-- Thumbnail upload area --}}
            <div class="mb-4">
                <div class="relative border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-900/40"
                    style="min-height:180px;"
                    @dragover.prevent @drop.prevent="handleDrop($event)">
                    <div x-show="!thumbPreview && !thumbnailUrl" class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-gray-400">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm">Click or drag to upload event image</p>
                    </div>
                    <img x-show="thumbPreview || thumbnailUrl" :src="thumbPreview || thumbnailUrl" class="w-full h-48 object-cover">
                    <label class="absolute top-2 right-2 w-8 h-8 bg-white dark:bg-gray-700 rounded-lg shadow flex items-center justify-center cursor-pointer hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <input type="file" name="thumbnail" accept="image/*" class="hidden" @change="previewThumb($event)">
                    </label>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">External Image URL</label>
                    <input type="text" name="thumbnail_url" x-model="thumbnailUrl" placeholder="External Image URL"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Image Description</label>
                    <input type="text" name="image_description" placeholder="Image Description"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        {{-- ═══ EVENT DETAILS ════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-5">Event Details</h2>
            <div class="space-y-4">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Start Date &amp; Time</label>
                        <div class="relative">
                            <input type="datetime-local" name="event_start_at"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg pl-9 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">End Date &amp; Time</label>
                        <div class="relative">
                            <input type="datetime-local" name="event_end_at"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg pl-9 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Organizer</label>
                    <div class="relative">
                        <input type="text" name="event_organizer" placeholder="Organizer name or organization"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg pl-9 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Location</label>
                    <div class="relative mb-2">
                        <input type="text" name="event_venue" placeholder="Venue name"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg pl-9 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-2.077 3.678-5.032 3.678-8.327a8 8 0 10-16 0c0 3.295 1.734 6.25 3.678 8.327a19.576 19.576 0 002.682 2.282 16.975 16.975 0 001.215.742zM12 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
                    </div>
                    <textarea name="event_address" rows="3" placeholder="Full address (street, city, country)"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                </div>
            </div>
        </div>

        {{-- ═══ SETTINGS ══════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-5">Settings</h2>
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Category <span class="text-red-400">*</span></label>
                        <select name="category_id" required class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="">Select a category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name_ne ?? $cat->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Status</label>
                        <select name="status" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>

                {{-- Toggle settings --}}
                @php
                $toggles = [
                    ['name' => 'is_featured',   'label' => 'Add to Featured'],
                    ['name' => 'add_slider',     'label' => 'Add to Slider'],
                    ['name' => 'add_breaking',   'label' => 'Add to Breaking News'],
                    ['name' => 'add_recommended','label' => 'Add to Recommended'],
                    ['name' => 'members_only',   'label' => 'Show Only to Registered Users'],
                ];
                @endphp
                <div class="divide-y divide-gray-50 dark:divide-gray-700/50 border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
                    @foreach($toggles as $t)
                    <label class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer transition-colors">
                        <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ $t['label'] }}</span>
                        <div x-data="{ on: false }" class="relative">
                            <input type="hidden" name="{{ $t['name'] }}" value="0">
                            <input type="checkbox" name="{{ $t['name'] }}" value="1" class="sr-only peer" @change="on = $event.target.checked">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer peer-checked:bg-brand-500 transition-colors"></div>
                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ═══ GENERAL ═══════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-5">General</h2>
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Title <span class="text-red-400">*</span></label>
                    <input type="text" name="title" required placeholder="Event title"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 @error('title') border-red-400 @enderror">
                    @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Slug <span class="text-xs font-normal text-gray-400">(auto-generated if blank)</span></label>
                    <input type="text" name="slug" placeholder="event-slug-url"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Summary &amp; Description</label>
                    <textarea name="excerpt" rows="3" placeholder="Brief description of the event..."
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                </div>

                {{-- Tags --}}
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Tags</label>
                    <div x-data="{ input: '' }" class="border border-gray-200 dark:border-gray-600 dark:bg-gray-700 rounded-lg px-3 py-2 flex flex-wrap gap-1.5 min-h-[42px]">
                        <template x-for="(tag, i) in tags.split(',').map(t=>t.trim()).filter(Boolean)" :key="i">
                            <span class="flex items-center gap-1 text-xs bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-700 rounded-full px-2.5 py-0.5 font-medium">
                                <span x-text="tag"></span>
                                <button type="button" @click="removeTag(tag)" class="hover:text-red-500">×</button>
                            </span>
                        </template>
                        <input type="text" x-model="input" placeholder="Type tag and hit enter"
                            @keydown.enter.prevent="addTag(input); input=''"
                            @keydown.comma.prevent="addTag(input); input=''"
                            class="flex-1 min-w-[120px] text-sm outline-none bg-transparent dark:text-white placeholder-gray-300 dark:placeholder-gray-600">
                    </div>
                </div>

                {{-- Content body --}}
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Content</label>
                    <textarea name="body" rows="10" placeholder="Full event description, details, and any additional information..."
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-y leading-relaxed"></textarea>
                </div>
            </div>
        </div>

        {{-- ═══ EVENT SCHEDULE ════════════════════════════════════ --}}
        <div x-data="{ open: true, items: [''] }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-bold text-gray-900 dark:text-white">Event Schedule</span>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
            </button>
            <div x-show="open" x-collapse class="px-6 pb-5 border-t border-gray-50 dark:border-gray-700/50 pt-4">
                <div class="mb-3">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide block mb-1.5">Section Title</label>
                    <input type="text" name="schedule_title" value="Event Schedule"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="space-y-2 mb-3">
                    <template x-for="(item, i) in items" :key="i">
                        <div class="flex gap-2">
                            <input type="text" :name="'event_schedule[]'" x-model="items[i]"
                                placeholder="09:00–09:30 · Opening & Registration · Opening speech"
                                class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <button type="button" @click="items.splice(i,1)" x-show="items.length > 1"
                                class="w-8 h-9 flex items-center justify-center text-gray-300 hover:text-red-400 transition-colors flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="items.push('')"
                    class="flex items-center gap-1.5 text-sm text-brand-600 dark:text-brand-400 font-semibold hover:text-brand-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add New
                </button>
                <p class="text-xs text-gray-400 mt-2">Format: 09:00–09:30 · Session Name · Description</p>
            </div>
        </div>

        {{-- ═══ EVENT HIGHLIGHTS ══════════════════════════════════ --}}
        <div x-data="{ open: true, items: [''] }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-bold text-gray-900 dark:text-white">Event Highlights</span>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
            </button>
            <div x-show="open" x-collapse class="px-6 pb-5 border-t border-gray-50 dark:border-gray-700/50 pt-4">
                <div class="mb-3">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide block mb-1.5">Section Title</label>
                    <input type="text" name="highlights_title" value="Event Highlights"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="space-y-2 mb-3">
                    <template x-for="(item, i) in items" :key="i">
                        <div class="flex gap-2">
                            <input type="text" :name="'event_highlights[]'" x-model="items[i]"
                                placeholder="Age limit – 15+ required"
                                class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <button type="button" @click="items.splice(i,1)" x-show="items.length > 1"
                                class="w-8 h-9 flex items-center justify-center text-gray-300 hover:text-red-400 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="items.push('')"
                    class="flex items-center gap-1.5 text-sm text-brand-600 dark:text-brand-400 font-semibold hover:text-brand-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add New
                </button>
            </div>
        </div>

        {{-- ═══ SPEAKERS & GUESTS ═════════════════════════════════ --}}
        <div x-data="{ open: true, items: [{ name: '', role: '', bio: '' }] }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="font-bold text-gray-900 dark:text-white">Speakers &amp; Guests</span>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
            </button>
            <div x-show="open" x-collapse class="px-6 pb-5 border-t border-gray-50 dark:border-gray-700/50 pt-4 space-y-3">
                <template x-for="(sp, i) in items" :key="i">
                    <div class="border border-gray-100 dark:border-gray-700 rounded-xl p-3 space-y-2 bg-gray-50 dark:bg-gray-700/30 relative">
                        <button type="button" @click="items.splice(i,1)" x-show="items.length > 1"
                            class="absolute top-2 right-2 w-6 h-6 flex items-center justify-center text-gray-300 hover:text-red-400 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" :name="'event_speakers[' + i + '][name]'" x-model="sp.name" placeholder="Speaker name"
                                class="text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <input type="text" :name="'event_speakers[' + i + '][role]'" x-model="sp.role" placeholder="Role / Title"
                                class="text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <input type="text" :name="'event_speakers[' + i + '][bio]'" x-model="sp.bio" placeholder="Short bio (optional)"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </template>
                <button type="button" @click="items.push({ name: '', role: '', bio: '' })"
                    class="flex items-center gap-1.5 text-sm text-brand-600 dark:text-brand-400 font-semibold hover:text-brand-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add New
                </button>
            </div>
        </div>

        {{-- ═══ LOCATION (OPTIONAL) ═══════════════════════════════ --}}
        <div x-data="locationPicker()" x-init="init()" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="font-bold text-gray-900 dark:text-white">Location <span class="text-gray-400 font-normal text-sm">(Optional)</span></span>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
            </button>
            <div x-show="open" x-collapse class="px-6 pb-5 border-t border-gray-50 dark:border-gray-700/50 pt-4 space-y-3">
                <div class="relative">
                    <input type="text" x-model="query" @input.debounce.600ms="geocode()" placeholder="Type a location name..."
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg pl-9 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    {{-- Suggestions dropdown --}}
                    <div x-show="suggestions.length" @click.outside="suggestions=[]"
                        class="absolute z-10 top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg overflow-hidden">
                        <template x-for="s in suggestions" :key="s.place_id">
                            <button type="button" @click="selectPlace(s)"
                                class="w-full text-left px-3 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border-b border-gray-50 dark:border-gray-700 last:border-0 transition-colors"
                                x-text="s.display_name"></button>
                        </template>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Latitude</label>
                        <input type="number" name="event_lat" step="0.000001" x-model="lat" placeholder="0.000000"
                            @change="updateMap()"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Longitude</label>
                        <input type="number" name="event_lng" step="0.000001" x-model="lng" placeholder="0.000000"
                            @change="updateMap()"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                {{-- OpenStreetMap embed --}}
                <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 bg-gray-100" style="height:240px;">
                    <iframe :src="mapSrc()" width="100%" height="240" style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
                <p class="text-xs text-gray-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Search for a location or enter coordinates. Map updates automatically.
                </p>
            </div>
        </div>

        {{-- ═══ REGISTRATION & TICKETS ════════════════════════════ --}}
        <div x-data="{ open: true, type: 'none' }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    <span class="font-bold text-gray-900 dark:text-white">Event Registration &amp; Tickets</span>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
            </button>
            <div x-show="open" x-collapse class="px-6 pb-5 border-t border-gray-50 dark:border-gray-700/50 pt-4 space-y-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-2">Registration Type</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach([
                            ['none','No Registration Required'],
                            ['free','Free Registration'],
                            ['paid','Paid Tickets'],
                        ] as [$val,$lbl])
                        <label class="flex items-center gap-2 cursor-pointer border rounded-lg px-4 py-2.5 transition-colors"
                            :class="type === '{{ $val }}' ? 'border-brand-400 bg-brand-50 dark:bg-brand-900/20 text-brand-700 dark:text-brand-300' : 'border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/40 text-gray-700 dark:text-gray-300'">
                            <input type="radio" name="event_registration_type" value="{{ $val }}" x-model="type" class="sr-only">
                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center transition-colors"
                                :class="type === '{{ $val }}' ? 'border-brand-500' : 'border-gray-300 dark:border-gray-500'">
                                <div class="w-2 h-2 rounded-full bg-brand-500" x-show="type === '{{ $val }}'"></div>
                            </div>
                            <span class="text-sm font-medium">{{ $lbl }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div x-show="type === 'paid'" x-collapse class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Ticket Price</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-sm text-gray-400">Rs.</span>
                            <input type="number" name="event_ticket_price" min="0" placeholder="0"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg pl-9 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Registration URL</label>
                        <input type="url" name="event_registration_url" placeholder="https://..."
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div x-show="type === 'free'" x-collapse>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Registration URL</label>
                    <input type="url" name="event_registration_url" placeholder="https://..."
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        {{-- ═══ FAQ (OPTIONAL) ════════════════════════════════════ --}}
        <div x-data="{ open: false, items: [{ q: '', a: '' }] }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-bold text-gray-900 dark:text-white">Frequently Asked Questions <span class="text-gray-400 font-normal text-sm">(Optional)</span></span>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
            </button>
            <div x-show="open" x-collapse class="px-6 pb-5 border-t border-gray-50 dark:border-gray-700/50 pt-4 space-y-3">
                <div class="mb-3">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide block mb-1.5">Section Title</label>
                    <input type="text" name="faq_title" value="Frequently Asked Questions"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <template x-for="(faq, i) in items" :key="i">
                    <div class="border border-gray-100 dark:border-gray-700 rounded-xl p-3 space-y-2 bg-gray-50 dark:bg-gray-700/30 relative">
                        <button type="button" @click="items.splice(i,1)" x-show="items.length > 1"
                            class="absolute top-2 right-2 w-6 h-6 flex items-center justify-center text-gray-300 hover:text-red-400 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <input type="text" :name="'event_faq_q[]'" x-model="faq.q" placeholder="Question"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <textarea :name="'event_faq_a[]'" x-model="faq.a" rows="2" placeholder="Answer"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                    </div>
                </template>
                <button type="button" @click="items.push({ q: '', a: '' })"
                    class="flex items-center gap-1.5 text-sm text-brand-600 dark:text-brand-400 font-semibold hover:text-brand-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Question
                </button>
            </div>
        </div>

        {{-- ═══ META OPTIONS (OPTIONAL) ══════════════════════════ --}}
        <div x-data="{ open: false }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span class="font-bold text-gray-900 dark:text-white">Meta Options <span class="text-gray-400 font-normal text-sm">(Optional)</span></span>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
            </button>
            <div x-show="open" x-collapse class="px-6 pb-5 border-t border-gray-50 dark:border-gray-700/50 pt-4 space-y-3">
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Meta Title</label>
                    <input type="text" name="seo_title" placeholder="Meta Title (max 60 chars)" maxlength="160"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Meta Description</label>
                    <textarea name="seo_desc" rows="3" placeholder="Meta Description (max 155 chars)" maxlength="320"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Meta Keywords</label>
                    <input type="text" name="seo_keywords" placeholder="event, nepal, conference, festival"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-xs text-gray-400 mt-1">Enter keywords separated by commas</p>
                </div>
            </div>
        </div>

        {{-- ═══ BOTTOM ACTIONS ════════════════════════════════════ --}}
        <div class="flex items-center justify-end gap-3 py-4">
            <button type="submit" name="status" value="draft"
                class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Save as Draft
            </button>
            <button type="submit" name="status" value="published"
                class="px-8 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
                Add Event
            </button>
        </div>

        </div>{{-- /space-y-4 --}}
    </form>
</div>

@push('scripts')
<script>
function eventEditor() {
    return {
        tags: '',
        thumbnailUrl: '',
        thumbPreview: '',

        addTag(name) {
            if (!name.trim()) return;
            const parts = this.tags.split(',').map(t => t.trim()).filter(Boolean);
            if (!parts.includes(name.trim())) parts.push(name.trim());
            this.tags = parts.join(', ');
        },

        removeTag(name) {
            const parts = this.tags.split(',').map(t => t.trim()).filter(t => t && t !== name);
            this.tags = parts.join(', ');
        },

        previewThumb(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = ev => { this.thumbPreview = ev.target.result; this.thumbnailUrl = ''; };
            reader.readAsDataURL(file);
        },

        handleDrop(e) {
            const file = e.dataTransfer.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = ev => this.thumbPreview = ev.target.result;
            reader.readAsDataURL(file);
        },
    };
}

function locationPicker() {
    return {
        open: true,
        query: '',
        lat: '',
        lng: '',
        suggestions: [],

        init() {},

        mapSrc() {
            const lat = parseFloat(this.lat) || 27.7172;
            const lng = parseFloat(this.lng) || 85.3240;
            const zoom = (this.lat && this.lng) ? 13 : 5;
            return `https://www.openstreetmap.org/export/embed.html?bbox=${lng-0.05},${lat-0.05},${lng+0.05},${lat+0.05}&layer=mapnik&marker=${lat},${lng}`;
        },

        async geocode() {
            if (this.query.length < 3) { this.suggestions = []; return; }
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(this.query)}&format=json&limit=5`, {
                    headers: { 'Accept-Language': 'en' }
                });
                this.suggestions = await res.json();
            } catch { this.suggestions = []; }
        },

        selectPlace(s) {
            this.lat = parseFloat(s.lat).toFixed(6);
            this.lng = parseFloat(s.lon).toFixed(6);
            this.query = s.display_name;
            this.suggestions = [];
        },

        updateMap() {
            // Map iframe re-renders via :src binding
        },
    };
}
</script>
@endpush
@endsection
