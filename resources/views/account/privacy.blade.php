@extends('layouts.account')
@section('title', 'Data & Privacy')

@section('main')
<div class="space-y-5">

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-1">Your Data</h2>
        <p class="text-sm text-gray-400 mb-5">Information about what we store about your account.</p>
        <dl class="space-y-4 text-sm">
            <div class="flex items-center justify-between py-3 border-b border-gray-50 dark:border-gray-700">
                <div>
                    <dt class="font-medium text-gray-800 dark:text-gray-200">Account data</dt>
                    <dd class="text-xs text-gray-400 mt-0.5">Name, email, avatar, bio, preferences</dd>
                </div>
                <span class="text-xs text-gray-500">Stored</span>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-gray-50 dark:border-gray-700">
                <div>
                    <dt class="font-medium text-gray-800 dark:text-gray-200">Published content</dt>
                    <dd class="text-xs text-gray-400 mt-0.5">Articles, comments, and posts you've created</dd>
                </div>
                <span class="text-xs text-gray-500">Stored</span>
            </div>
            <div class="flex items-center justify-between py-3">
                <div>
                    <dt class="font-medium text-gray-800 dark:text-gray-200">Payment info</dt>
                    <dd class="text-xs text-gray-400 mt-0.5">Subscription status only — no card details stored here</dd>
                </div>
                <span class="text-xs text-gray-500">Minimal</span>
            </div>
        </dl>
    </div>

    {{-- Danger zone --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-red-100 dark:border-red-900/50 shadow-sm p-6">
        <h2 class="font-bold text-red-700 dark:text-red-400 mb-1">Danger Zone</h2>
        <p class="text-sm text-gray-400 mb-5">Permanently delete your account and all associated data. This action cannot be undone.</p>

        <details class="group">
            <summary class="cursor-pointer text-sm font-semibold text-red-600 hover:text-red-700 list-none flex items-center gap-2">
                <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                Delete my account
            </summary>
            <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-100 dark:border-red-800">
                <p class="text-sm text-red-700 dark:text-red-300 mb-4">
                    Type <strong>DELETE</strong> to confirm you want to permanently delete your account.
                </p>
                <form method="POST" action="/account/delete" onsubmit="return confirm('This is permanent. Are you absolutely sure?')">
                    @csrf @method('DELETE')
                    <div class="flex gap-3 flex-wrap">
                        <input type="text" name="confirm" placeholder="Type DELETE to confirm"
                            class="border border-red-200 dark:border-red-700 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 w-64">
                        <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg">
                            Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </details>
    </div>
</div>
@endsection
