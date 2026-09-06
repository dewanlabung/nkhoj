@extends('layouts.app')
@section('title', 'Change Password')

@section('content')
<div class="max-w-lg">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-5">Change Password</h2>

        <form method="POST" action="/account/password" class="space-y-4">
            @csrf

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Current Password *</label>
                <input type="password" name="current_password" required
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 @error('current_password') border-red-400 @enderror">
                @error('current_password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">New Password *</label>
                <input type="password" name="password" required
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 @error('password') border-red-400 @enderror">
                @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-gray-400 mt-1">Minimum 8 characters.</p>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Confirm New Password *</label>
                <input type="password" name="password_confirmation" required
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    Update Password
                </button>
                <a href="/account/settings" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">← Back to Settings</a>
            </div>
        </form>
    </div>
</div>
@endsection
