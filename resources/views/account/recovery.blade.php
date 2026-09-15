@extends('layouts.account')
@section('title', 'Account Recovery')

@section('main')
<div class="space-y-5">

    @if(session('success'))<div class="px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-xl text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="px-4 py-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-xl text-sm">{{ session('error') }}</div>@endif

    {{-- Recovery email --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-1">Recovery Email</h2>
        <p class="text-sm text-gray-400 mb-5">A backup email used to recover your account if you lose access to your primary email. Must be different from your main email.</p>

        @if($user->recovery_email)
        <div class="flex items-center gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 mb-4">
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->recovery_email }}</p>
                @if($user->recovery_email_verified_at)
                <p class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1 mt-0.5">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Verified {{ $user->recovery_email_verified_at->diffForHumans() }}
                </p>
                @else
                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-0.5">Unverified — check your inbox for the verification link</p>
                @endif
            </div>
            <form method="POST" action="/account/recovery/remove-email">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Remove recovery email?')"
                    class="text-xs text-red-500 hover:text-red-700 font-medium">Remove</button>
            </form>
        </div>
        @endif

        <form method="POST" action="/account/recovery/email" class="flex gap-3 flex-wrap">
            @csrf
            <input type="email" name="recovery_email"
                value="{{ old('recovery_email', $user->recovery_email) }}"
                placeholder="Recovery email address"
                class="flex-1 min-w-0 px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400">
            @error('recovery_email')<p class="w-full text-xs text-red-500 -mt-2">{{ $message }}</p>@enderror
            <button type="submit"
                class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-colors flex-shrink-0">
                {{ $user->recovery_email ? 'Update & Resend Verification' : 'Add Recovery Email' }}
            </button>
        </form>
    </div>

    {{-- Recovery options info --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-4">Recovery Options</h2>
        <div class="space-y-4">

            {{-- Forgot password --}}
            <div class="flex items-start gap-4">
                <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Forgot Password</p>
                    <p class="text-xs text-gray-400 mt-0.5">Reset via email, username, or recovery email.</p>
                </div>
                <a href="/forgot-password" class="text-xs text-brand-600 hover:text-brand-700 font-medium flex-shrink-0">Go →</a>
            </div>

            {{-- Forgot username --}}
            <div class="flex items-start gap-4">
                <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Forgot Username</p>
                    <p class="text-xs text-gray-400 mt-0.5">We'll email your username to your registered address.</p>
                </div>
                <a href="/forgot-username" class="text-xs text-brand-600 hover:text-brand-700 font-medium flex-shrink-0">Go →</a>
            </div>

            {{-- Two-factor auth --}}
            <div class="flex items-start gap-4">
                <div class="w-9 h-9 rounded-xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Two-Factor Authentication</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Status:
                        @if(auth()->user()->two_factor_enabled)
                            <span class="text-green-600 dark:text-green-400 font-semibold">Enabled</span>
                        @else
                            <span class="text-gray-400">Not enabled</span>
                        @endif
                    </p>
                </div>
                <a href="/account/security" class="text-xs text-brand-600 hover:text-brand-700 font-medium flex-shrink-0">Manage →</a>
            </div>

        </div>
    </div>

    {{-- Your login info --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-4">Your Login Information</h2>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between items-center py-2 border-b border-gray-50 dark:border-gray-700">
                <span class="text-gray-500 dark:text-gray-400">Username</span>
                <span class="font-mono font-semibold text-gray-900 dark:text-white">{{ auth()->user()->username }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-50 dark:border-gray-700">
                <span class="text-gray-500 dark:text-gray-400">Primary email</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ auth()->user()->email }}</span>
            </div>
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-500 dark:text-gray-400">Recovery email</span>
                @if(auth()->user()->recovery_email)
                    <span class="font-semibold text-gray-900 dark:text-white">
                        {{ auth()->user()->recovery_email }}
                        @if(auth()->user()->recovery_email_verified_at)
                            <span class="ml-1 text-xs text-green-600 dark:text-green-400">✓</span>
                        @else
                            <span class="ml-1 text-xs text-yellow-500">unverified</span>
                        @endif
                    </span>
                @else
                    <span class="text-gray-400 text-xs">Not set</span>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
