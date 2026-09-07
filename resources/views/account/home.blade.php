@extends('layouts.account')
@section('title', 'Account Home')

@section('main')
{{-- Welcome --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 mb-5">
    <div class="flex items-center gap-5">
        @if($user->avatar_url)
        <img src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-full object-cover flex-shrink-0" alt="">
        @else
        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-3xl font-bold flex-shrink-0">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        @endif
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Welcome back</p>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ $user->displayName() }}</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $user->email }}</p>
            @if($user->bio)
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $user->bio }}</p>
            @endif
        </div>
    </div>
</div>

{{-- Quick links grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

    <a href="/account/personal-info" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 hover:shadow-md transition-shadow group">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-900 dark:text-white text-sm group-hover:text-brand-600 transition-colors">Personal Info</p>
                <p class="text-xs text-gray-400 mt-0.5">Your name, photo, and bio</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    {{ $user->first_name ? $user->displayName() : $user->name }}
                </p>
            </div>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-brand-400 transition-colors mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
    </a>

    <a href="/account/security" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 hover:shadow-md transition-shadow group">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-900 dark:text-white text-sm group-hover:text-brand-600 transition-colors">Security</p>
                <p class="text-xs text-gray-400 mt-0.5">Password and sign-in options</p>
                <p class="text-xs text-green-600 dark:text-green-400 mt-2 font-medium">Password protected</p>
            </div>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-brand-400 transition-colors mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
    </a>

    <a href="/account/subscriptions" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 hover:shadow-md transition-shadow group">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-900 dark:text-white text-sm group-hover:text-brand-600 transition-colors">Subscriptions</p>
                <p class="text-xs text-gray-400 mt-0.5">Your membership and billing</p>
                @if($sub)
                <p class="text-xs text-purple-600 dark:text-purple-400 mt-2 font-medium">{{ $sub->plan->name }} — Active</p>
                @else
                <p class="text-xs text-gray-400 mt-2">No active plan</p>
                @endif
            </div>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-brand-400 transition-colors mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
    </a>

    <a href="/account/privacy" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 hover:shadow-md transition-shadow group">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-900 dark:text-white text-sm group-hover:text-brand-600 transition-colors">Data & Privacy</p>
                <p class="text-xs text-gray-400 mt-0.5">Control your data and account</p>
                <p class="text-xs text-gray-400 mt-2">Joined {{ $user->created_at->format('M Y') }}</p>
            </div>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-brand-400 transition-colors mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
    </a>

</div>

{{-- Account info bar --}}
<div class="mt-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
    <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Account Details</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-0.5">Username</dt>
            <dd class="text-gray-900 dark:text-white font-medium">{{ $user->username ? '@'.$user->username : '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-0.5">Role</dt>
            <dd class="text-gray-900 dark:text-white font-medium">{{ $user->roleLabel() }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-0.5">Member Since</dt>
            <dd class="text-gray-900 dark:text-white font-medium">{{ $user->created_at->format('M d, Y') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-0.5">Last Active</dt>
            <dd class="text-gray-900 dark:text-white font-medium">{{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Now' }}</dd>
        </div>
    </dl>
</div>
@endsection
