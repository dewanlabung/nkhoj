@extends('layouts.app')
@section('title', 'Forgot Username')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-brand-100 dark:bg-brand-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Forgot your username?</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter your email and we'll send your username.</p>
        </div>

        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-xl text-sm text-center">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="/forgot-username" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                    placeholder="you@example.com">
                @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-colors">
                Send Username
            </button>
        </form>

        <div class="mt-6 text-center space-y-2">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <a href="/forgot-password" class="text-brand-600 hover:text-brand-700 font-medium">Forgot password instead?</a>
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <a href="/login" class="text-gray-600 dark:text-gray-400 hover:underline">Back to sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection
