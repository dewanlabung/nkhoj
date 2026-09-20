@extends('layouts.app')
@section('title', 'Choose Recovery Method')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">

        {{-- Back link --}}
        <a href="/forgot-password" class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 mb-6 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>

        {{-- Header --}}
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reset your password</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Choose how to verify your identity</p>
        </div>

        {{-- Account card --}}
        <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl mb-6">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="" class="w-12 h-12 rounded-full object-cover flex-shrink-0">
            @else
                <div class="w-12 h-12 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center flex-shrink-0">
                    <span class="text-lg font-bold text-brand-600 dark:text-brand-400">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
            @endif
            <div class="min-w-0">
                <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $user->username }}</p>
            </div>
            <div class="ml-auto flex-shrink-0">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            </div>
        </div>

        @error('method')
        <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-xl text-sm">{{ $message }}</div>
        @enderror

        {{-- Method options --}}
        <form method="POST" action="/forgot-password/choose" class="space-y-3">
            @csrf

            {{-- OTP to primary email --}}
            <label class="flex items-start gap-4 p-4 border-2 border-transparent bg-white dark:bg-gray-800 rounded-2xl cursor-pointer hover:border-brand-400 dark:hover:border-brand-500 transition-colors has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20 shadow-sm">
                <input type="radio" name="method" value="otp_primary" class="mt-1 accent-brand-600" required>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Send code to email</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5 ml-6">{{ \Illuminate\Support\Str::mask($user->email, '*', 2, -strlen(strstr($user->email, '@'))) }}</p>
                </div>
            </label>

            @if($user->recovery_email && $user->recovery_email_verified_at)
            {{-- OTP to recovery email --}}
            <label class="flex items-start gap-4 p-4 border-2 border-transparent bg-white dark:bg-gray-800 rounded-2xl cursor-pointer hover:border-brand-400 dark:hover:border-brand-500 transition-colors has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20 shadow-sm">
                <input type="radio" name="method" value="otp_recovery" class="mt-1 accent-brand-600">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Send code to recovery email</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5 ml-6">{{ \Illuminate\Support\Str::mask($user->recovery_email, '*', 2, -strlen(strstr($user->recovery_email, '@'))) }}</p>
                </div>
            </label>
            @endif

            <button type="submit"
                class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-colors mt-2">
                Continue
            </button>
        </form>

        <div class="mt-5 text-center">
            <p class="text-xs text-gray-400">Not your account? <a href="/forgot-password" class="text-brand-600 hover:text-brand-700 font-medium">Search again</a></p>
        </div>

    </div>
</div>
@endsection
