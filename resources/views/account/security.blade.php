@extends('layouts.account')
@section('title', 'Security & Sign-in')

@section('main')
<div class="space-y-5">

    {{-- Quick links --}}
    <div class="grid grid-cols-2 gap-3">
        <a href="/account/sessions" class="flex items-center gap-3 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:border-brand-300 dark:hover:border-brand-600 transition-colors group">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Sessions</p>
                <p class="text-xs text-gray-400 truncate">Active devices & history</p>
            </div>
        </a>
        <a href="/account/linked-apps" class="flex items-center gap-3 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:border-brand-300 dark:hover:border-brand-600 transition-colors group">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Linked Accounts</p>
                <p class="text-xs text-gray-400 truncate">Google, Facebook</p>
            </div>
        </a>
    </div>

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

    {{-- Two-Factor Authentication --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-1">Two-Factor Authentication</h2>
        <p class="text-sm text-gray-400 mb-5">Add an extra layer of security with an authenticator app.</p>

        @if(session('success'))<div class="bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>@endif
        @if(session('info'))<div class="bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 text-sm rounded-lg px-4 py-3 mb-4">{{ session('info') }}</div>@endif

        @if($user->two_factor_enabled)
        <div class="flex items-center gap-3 mb-5">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Enabled
            </span>
            <span class="text-xs text-gray-400">Since {{ $user->two_factor_confirmed_at?->format('M d, Y') }}</span>
        </div>
        {{-- Recovery Codes --}}
        @php $recoveryCodes = $user->two_factor_recovery_codes ?? []; @endphp
        <div class="mt-5 mb-5 bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-4" x-data="{ showCodes: false }">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Recovery Codes</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ count($recoveryCodes) }} code{{ count($recoveryCodes) !== 1 ? 's' : '' }} remaining — use if you lose your authenticator</p>
                </div>
                <button type="button" @click="showCodes = !showCodes"
                    class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                    <span x-text="showCodes ? 'Hide' : 'Show'">Show</span>
                </button>
            </div>
            <div x-show="showCodes" x-transition class="mt-3 space-y-1">
                @forelse($recoveryCodes as $code)
                <code class="block font-mono text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-3 py-1.5 text-gray-700 dark:text-gray-300 select-all">{{ $code }}</code>
                @empty
                <p class="text-xs text-gray-400">No recovery codes available.</p>
                @endforelse
                <form method="POST" action="/two-factor/recovery-codes/regenerate" class="mt-3">
                    @csrf
                    <button type="submit" class="text-xs text-orange-600 hover:text-orange-700 font-medium">
                        Regenerate all codes (old codes will stop working)
                    </button>
                </form>
            </div>
        </div>

        <form method="POST" action="/two-factor/disable" x-data="{ open: false }">
            @csrf
            <button type="button" @click="open = !open" class="text-sm text-red-600 hover:text-red-700 font-medium">Disable Two-Factor Authentication</button>
            <div x-show="open" x-transition class="mt-4 bg-red-50 dark:bg-red-900/20 rounded-xl p-4 space-y-3 max-w-sm">
                <p class="text-sm text-red-700 dark:text-red-300 font-medium">Enter your password to confirm:</p>
                <input type="password" name="password" class="w-full text-sm border border-red-200 dark:border-red-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-400" placeholder="Current password">
                @error('password')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg">Confirm Disable</button>
            </div>
        </form>
        @else
        <a href="/two-factor/setup" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Enable Two-Factor Authentication
        </a>
        @endif
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
