@extends('layouts.account')
@section('title', 'Security & Sign-in')

@section('main')
<div class="space-y-5">

    {{-- Change password --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-1">Change Password</h2>
        <p class="text-sm text-gray-400 mb-5">Choose a strong password you don't use elsewhere.</p>
        <form method="POST" action="/account/security/password" class="space-y-4 max-w-md">
            @csrf @method('PATCH')
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Current Password</label>
                <input type="password" name="current_password" required autocomplete="current-password"
                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 @error('current_password') border-red-400 @enderror">
                @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">New Password</label>
                <input type="password" name="password" required autocomplete="new-password"
                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                <p class="text-xs text-gray-400 mt-1">Minimum 8 characters</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Confirm New Password</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password"
                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div class="pt-1">
                <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl">Update Password</button>
            </div>
        </form>
    </div>

    {{-- Sign-in info --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-5">Sign-in Details</h2>
        <dl class="space-y-4 text-sm">
            <div class="flex items-center justify-between py-3 border-b border-gray-50 dark:border-gray-700">
                <div>
                    <dt class="font-medium text-gray-800 dark:text-gray-200">Email address</dt>
                    <dd class="text-gray-400 text-xs mt-0.5">Used for signing in</dd>
                </div>
                <span class="text-gray-600 dark:text-gray-300 font-mono text-xs">{{ $user->email }}</span>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-gray-50 dark:border-gray-700">
                <div>
                    <dt class="font-medium text-gray-800 dark:text-gray-200">Email verified</dt>
                    <dd class="text-gray-400 text-xs mt-0.5">Verifies your account ownership</dd>
                </div>
                @if($user->email_verified_at)
                <span class="text-xs text-green-600 font-semibold">✓ Verified {{ $user->email_verified_at->format('M d, Y') }}</span>
                @else
                <span class="text-xs text-yellow-600 font-semibold">Not verified</span>
                @endif
            </div>
            <div class="flex items-center justify-between py-3">
                <div>
                    <dt class="font-medium text-gray-800 dark:text-gray-200">Account created</dt>
                    <dd class="text-gray-400 text-xs mt-0.5">When you joined</dd>
                </div>
                <span class="text-gray-600 dark:text-gray-300 text-xs">{{ $user->created_at->format('M d, Y') }}</span>
            </div>
        </dl>
    </div>

    {{-- Sign out all devices --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-1">Sign Out</h2>
        <p class="text-sm text-gray-400 mb-4">Sign out from this session or all devices.</p>
        <form method="POST" action="/logout">
            @csrf
            <button type="submit" class="px-5 py-2.5 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 text-sm font-semibold rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                Sign Out of This Session
            </button>
        </form>
    </div>
</div>
@endsection
