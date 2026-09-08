@extends('layouts.app')
@section('title', 'Create Event – Nkhoj')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">Create an Event</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Share your event with the community</p>
    </div>

    <form method="POST" action="/events" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Event Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="200"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       placeholder="e.g. Kathmandu Tech Meetup 2025">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" rows="4" maxlength="5000"
                          class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
                          placeholder="Tell people what your event is about…">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Category</label>
                    <select name="category" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">Select category</option>
                        @foreach(['Music','Technology','Sports','Arts','Food','Business','Education','Health','Community','Travel','Fashion','Film'] as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Event Type <span class="text-red-500">*</span></label>
                    <select name="event_type" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="in-person" {{ old('event_type','in-person') === 'in-person' ? 'selected' : '' }}>In Person</option>
                        <option value="online" {{ old('event_type') === 'online' ? 'selected' : '' }}>Online</option>
                        <option value="hybrid" {{ old('event_type') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Start Date & Time <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    @error('starts_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">End Date & Time</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Venue Name</label>
                    <input type="text" name="venue" value="{{ old('venue') }}" maxlength="200"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="e.g. Hotel Yak & Yeti">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Location / Address</label>
                    <input type="text" name="location" value="{{ old('location') }}" maxlength="300"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="e.g. Durbar Marg, Kathmandu">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Organizer Name</label>
                    <input type="text" name="organizer" value="{{ old('organizer', auth()->user()->name) }}" maxlength="200"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Ticket Price (Rs)</label>
                    <input type="number" name="ticket_price" value="{{ old('ticket_price') }}" min="0" step="0.01"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="Leave empty for free">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Registration URL</label>
                <input type="url" name="registration_url" value="{{ old('registration_url') }}" maxlength="500"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       placeholder="https://...">
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Event Banner / Thumbnail</label>
            <input type="file" name="thumbnail" accept="image/*"
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition-colors">
        </div>

        <button type="submit" class="w-full py-4 rounded-2xl font-black text-white text-base transition-all hover:opacity-90 shadow-lg"
                style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">Create Event</button>
    </form>
</div>
@endsection
