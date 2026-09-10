@extends('layouts.app')
@section('title', 'Account Settings')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-5">Profile Information</h2>

        <form method="POST" action="/account/settings" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Username *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400 text-sm">@</span>
                        <input type="text" name="username" value="{{ old('username', auth()->user()->username) }}" required
                            class="w-full pl-7 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 @error('username') border-red-400 @enderror">
                    </div>
                    @error('username')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Email Address *</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 @error('email') border-red-400 @enderror">
                @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Bio</label>
                <textarea name="bio" rows="3" placeholder="Tell readers about yourself..."
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none @error('bio') border-red-400 @enderror">{{ old('bio', auth()->user()->bio ?? '') }}</textarea>
                @error('bio')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Website URL</label>
                <input type="url" name="website" value="{{ old('website', auth()->user()->website ?? '') }}" placeholder="https://example.com"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 @error('website') border-red-400 @enderror">
                @error('website')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Social Links --}}
            <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Social Links</p>
                @php
                $socials = auth()->user()->social_links ?? [];
                $socialFields = [
                    'twitter'   => ['label' => 'X (Twitter)', 'placeholder' => '@username'],
                    'instagram' => ['label' => 'Instagram',   'placeholder' => '@username'],
                    'facebook'  => ['label' => 'Facebook',    'placeholder' => 'username or page'],
                    'youtube'   => ['label' => 'YouTube',     'placeholder' => '@channel'],
                    'tiktok'    => ['label' => 'TikTok',      'placeholder' => '@username'],
                ];
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($socialFields as $key => $field)
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">{{ $field['label'] }}</label>
                        <input type="text" name="social_links[{{ $key }}]"
                            value="{{ old("social_links.$key", $socials[$key] ?? '') }}"
                            placeholder="{{ $field['placeholder'] }}"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    @endforeach
                </div>
            </div>

            @if(session('success'))
            <div class="text-sm text-green-600 bg-green-50 dark:bg-green-900/20 dark:text-green-400 border border-green-200 dark:border-green-800 rounded-lg px-4 py-2.5">
                {{ session('success') }}
            </div>
            @endif

            <div class="flex items-center gap-3 pt-2">
                <button class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    Save Changes
                </button>
                <a href="/account/password" class="text-sm text-brand-600 hover:underline">Change Password →</a>
            </div>
        </form>

        {{-- Two-Factor Authentication --}}
        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
            <h2 class="font-bold text-gray-900 dark:text-white mb-1">Two-Factor Authentication</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Add an extra layer of security to your account using an authenticator app (Google Authenticator, Authy, etc.).</p>

            @if(session('success'))<div class="bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>@endif
            @if(session('info'))<div class="bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('info') }}</div>@endif

            @if(auth()->user()->two_factor_enabled)
            <div class="flex items-center gap-3 mb-4">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Enabled
                </span>
                <span class="text-xs text-gray-400">Enabled {{ auth()->user()->two_factor_confirmed_at?->diffForHumans() }}</span>
            </div>
            <form method="POST" action="/two-factor/disable" x-data="{ open: false }">
                @csrf
                <button type="button" @click="open = !open" class="text-sm text-red-600 hover:text-red-700 font-medium">Disable 2FA</button>
                <div x-show="open" x-transition class="mt-3 bg-red-50 dark:bg-red-900/20 rounded-xl p-4 space-y-3">
                    <p class="text-sm text-red-700 dark:text-red-300 font-medium">Enter your password to disable 2FA:</p>
                    <input type="password" name="password" class="w-full text-sm border border-red-200 dark:border-red-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-red-400" placeholder="Current password">
                    @error('password')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    <button type="submit" class="px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg">Confirm Disable</button>
                </div>
            </form>
            @else
            <a href="/two-factor/setup" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Set Up Two-Factor Authentication
            </a>
            @endif
        </div>
    </div>
</div>
@endsection
