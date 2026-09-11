@extends('layouts.app')
@section('title', 'Forgot Password')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-brand-100 dark:bg-brand-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reset your password</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter your email, username, or recovery email.</p>
        </div>

        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-xl text-sm text-center">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="/forgot-password" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email or username</label>
                <input type="text" name="login" value="{{ old('login') }}" required autofocus
                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                    placeholder="Email, username, or recovery email">
                @error('login')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-colors">
                Send Reset Link
            </button>
        </form>

        <div class="mt-6 text-center space-y-2">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <a href="/forgot-username" class="text-brand-600 hover:text-brand-700 font-medium">Forgot your username?</a>
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <a href="/login" class="text-gray-600 dark:text-gray-400 hover:underline">Back to sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection
