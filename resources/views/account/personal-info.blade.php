@extends('layouts.account')
@section('title', 'Personal Info')

@section('main')
<div class="space-y-5">

    {{-- Avatar --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-5">Profile Photo</h2>
        <div class="flex items-center gap-5 flex-wrap">
            @if($user->avatar_url)
            <img src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-full object-cover" alt="">
            @else
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-3xl font-bold">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            @endif
            <form method="POST" action="/account/avatar" enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf
                <label class="cursor-pointer px-4 py-2 text-sm font-semibold border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">
                    <input type="file" name="avatar" accept="image/*" class="sr-only" onchange="this.form.submit()">
                    Upload Photo
                </label>
                <p class="text-xs text-gray-400">JPG, PNG, GIF up to 2 MB</p>
            </form>
        </div>
    </div>

    {{-- Personal details --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-5">Personal Details</h2>
        <form method="POST" action="/account/personal-info" class="space-y-4">
            @csrf @method('PATCH')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}"
                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}"
                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Display Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Username</label>
                <div class="flex">
                    <span class="inline-flex items-center px-3 py-2.5 border border-r-0 border-gray-200 dark:border-gray-600 rounded-l-xl bg-gray-50 dark:bg-gray-700 text-gray-400 text-sm">@</span>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}" pattern="[a-zA-Z0-9_\-]+" placeholder="yourname"
                        class="flex-1 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Bio</label>
                <textarea name="bio" rows="3" maxlength="500" placeholder="Tell us about yourself..."
                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none">{{ old('bio', $user->bio) }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Max 500 characters</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Website</label>
                <input type="url" name="website" value="{{ old('website', $user->website) }}" placeholder="https://..."
                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div class="pt-2">
                <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl">Save Changes</button>
            </div>
        </form>
    </div>

    {{-- Email (read-only) --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-2">Email Address</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Your email is used for sign-in and notifications.</p>
        <div class="flex items-center gap-3">
            <div class="flex-1 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded-xl px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300 font-mono">
                {{ $user->email }}
            </div>
            @if($user->email_verified_at)
            <span class="text-xs text-green-600 dark:text-green-400 font-semibold flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Verified
            </span>
            @else
            <span class="text-xs text-yellow-600 dark:text-yellow-400 font-semibold">Unverified</span>
            @endif
        </div>
    </div>
</div>
@endsection
