@extends('layouts.account')
@section('title', 'Data & Privacy')

@section('main')
<div class="space-y-5">

    {{-- Data export --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-1">Export Your Data</h2>
        <p class="text-sm text-gray-400 mb-5">Download a copy of everything you've created on Nkhoj — posts, comments, bookmarks, and account details — as a JSON file. The download link will appear on this page (and emailed to you if mail is set up).</p>

        @if(session('success'))<div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-xl text-sm">{{ session('success') }}</div>@endif
        @if(session('info'))<div class="mb-4 px-4 py-3 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-xl text-sm">{{ session('info') }}</div>@endif

        @if($user->data_export_ready_at && $user->data_export_ready_at->gt(now()->subHours(24)))
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl px-4 py-3 mb-4 flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Your export is ready</p>
                <p class="text-xs text-blue-600 dark:text-blue-400">Ready {{ $user->data_export_ready_at->diffForHumans() }} · Expires {{ $user->data_export_ready_at->addHours(24)->diffForHumans() }}</p>
            </div>
            <a href="/account/export/download?token={{ $user->data_export_token }}"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">Download</a>
        </div>
        @endif

        <form method="POST" action="/account/data-privacy/export">
            @csrf
            <button type="submit"
                {{ ($user->data_export_requested_at && $user->data_export_requested_at->gt(now()->subHours(24)) && !$user->data_export_ready_at) ? 'disabled' : '' }}
                class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                @if($user->data_export_requested_at && $user->data_export_requested_at->gt(now()->subHours(24)) && !$user->data_export_ready_at)
                    Preparing export…
                @else
                    Request Data Export
                @endif
            </button>
        </form>
    </div>

    {{-- Account deletion --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-red-100 dark:border-red-900/50 shadow-sm p-6">
        <h2 class="font-bold text-red-700 dark:text-red-400 mb-1">Delete Account</h2>

        @if($user->isPendingDeletion())
        <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4 mb-4">
            <p class="text-sm font-semibold text-red-700 dark:text-red-300 mb-1">Account scheduled for permanent deletion</p>
            <p class="text-xs text-red-600 dark:text-red-400">
                Requested {{ $user->deletion_requested_at->diffForHumans() }}.
                Permanent deletion on <strong>{{ $user->deletion_requested_at->addDays(30)->format('F d, Y') }}</strong>.
            </p>
        </div>
        <form method="POST" action="/account/data-privacy/cancel-deletion">
            @csrf
            <button type="submit" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl">
                Cancel Deletion
            </button>
        </form>

        @else
        <p class="text-sm text-gray-400 mb-5">Permanently delete your account and all associated data. This starts a 30-day grace period — you can cancel any time before the deadline.</p>

        <form method="POST" action="/account/data-privacy/delete" x-data="{ open: false }">
            @csrf
            <button type="button" @click="open = !open" class="text-sm text-red-600 hover:text-red-700 font-medium">Request Account Deletion</button>
            <div x-show="open" x-transition class="mt-4 bg-red-50 dark:bg-red-900/20 rounded-xl p-4 space-y-3 max-w-sm">
                <p class="text-sm text-red-700 dark:text-red-300 font-medium">Enter your password to confirm:</p>
                @error('password')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                <input type="password" name="password" class="w-full text-sm border border-red-200 dark:border-red-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-400" placeholder="Current password">
                <div class="text-xs text-red-600 dark:text-red-400 space-y-1">
                    <p>• All your posts and content will be permanently deleted</p>
                    <p>• Your account cannot be recovered after 30 days</p>
                    <p>• Subscriptions will be cancelled</p>
                </div>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg">Start 30-Day Deletion Grace Period</button>
            </div>
        </form>
        @endif
    </div>

</div>
@endsection
