@extends('layouts.admin')
@section('title', 'Authentication Settings')

@section('content')
@php
$auth = $s['auth'] ?? [];
@endphp

@if(session('success'))
<div class="mb-4 flex items-center gap-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-400 text-sm rounded-xl px-4 py-3">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 flex items-center gap-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-400 text-sm rounded-xl px-4 py-3">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    {{ session('error') }}
</div>
@endif

<form method="POST" action="/admin/auth-settings">
@csrf

<div class="space-y-5">

    {{-- ── Registration ──────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-0">
            <div class="lg:col-span-2 p-6 border-b lg:border-b-0 lg:border-r border-gray-100 dark:border-gray-700">
                <h2 class="font-bold text-gray-900 dark:text-white">Registration</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure user registration settings.</p>
            </div>
            <div class="lg:col-span-3 p-6 space-y-5">

                {{-- Disable registration --}}
                <div class="flex items-center justify-between"
                     x-data="{ on: {{ ($auth['disable_registration'] ?? false) ? 'true' : 'false' }} }">
                    <div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Disable registration</p>
                        <p class="text-xs text-gray-400 mt-0.5">All registration-related functionality will be disabled and hidden from users.</p>
                    </div>
                    <label class="flex items-center cursor-pointer flex-shrink-0 ml-4">
                        <input type="hidden" name="disable_registration" value="0">
                        <div class="relative" @click="on = !on">
                            <input type="checkbox" name="disable_registration" value="1" class="sr-only" :checked="on">
                            <div class="w-12 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-6' : ''"></div>
                        </div>
                    </label>
                </div>

                {{-- Require email confirmation --}}
                <div class="flex items-center justify-between border-t border-gray-50 dark:border-gray-700 pt-4"
                     x-data="{ on: {{ ($auth['require_email_confirmation'] ?? false) ? 'true' : 'false' }} }">
                    <div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Require email confirmation</p>
                        <p class="text-xs text-gray-400 mt-0.5">Require newly registered users to validate their email address before being able to login.</p>
                    </div>
                    <label class="flex items-center cursor-pointer flex-shrink-0 ml-4">
                        <input type="hidden" name="require_email_confirmation" value="0">
                        <div class="relative" @click="on = !on">
                            <input type="checkbox" name="require_email_confirmation" value="1" class="sr-only" :checked="on">
                            <div class="w-12 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-6' : ''"></div>
                        </div>
                    </label>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Social Login Settings ──────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-0">
            <div class="lg:col-span-2 p-6 border-b lg:border-b-0 lg:border-r border-gray-100 dark:border-gray-700">
                <h2 class="font-bold text-gray-900 dark:text-white">Social Login Settings</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure general settings for social login.</p>
            </div>
            <div class="lg:col-span-3 p-6 space-y-5">

                {{-- Social login requires existing account --}}
                <div class="flex items-center justify-between"
                     x-data="{ on: {{ ($auth['social_login_require_account'] ?? false) ? 'true' : 'false' }} }">
                    <div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Social login requires existing account</p>
                        <p class="text-xs text-gray-400 mt-0.5">Users will only be able to login via socials if they have connected it from their account settings page.</p>
                    </div>
                    <label class="flex items-center cursor-pointer flex-shrink-0 ml-4">
                        <input type="hidden" name="social_login_require_account" value="0">
                        <div class="relative" @click="on = !on">
                            <input type="checkbox" name="social_login_require_account" value="1" class="sr-only" :checked="on">
                            <div class="w-12 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-6' : ''"></div>
                        </div>
                    </label>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Single Device Login ─────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-0">
            <div class="lg:col-span-2 p-6 border-b lg:border-b-0 lg:border-r border-gray-100 dark:border-gray-700">
                <h2 class="font-bold text-gray-900 dark:text-white">Single Device Login</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Control how many devices can access an account simultaneously.</p>
            </div>
            <div class="lg:col-span-3 p-6">
                <div class="flex items-center justify-between"
                     x-data="{ on: {{ ($auth['single_device_login'] ?? false) ? 'true' : 'false' }} }">
                    <div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Single device login</p>
                        <p class="text-xs text-gray-400 mt-0.5">Logging in on a new device will automatically log out all other sessions.</p>
                    </div>
                    <label class="flex items-center cursor-pointer flex-shrink-0 ml-4">
                        <input type="hidden" name="single_device_login" value="0">
                        <div class="relative" @click="on = !on">
                            <input type="checkbox" name="single_device_login" value="1" class="sr-only" :checked="on">
                            <div class="w-12 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-6' : ''"></div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Domain Blacklist ────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-0">
            <div class="lg:col-span-2 p-6 border-b lg:border-b-0 lg:border-r border-gray-100 dark:border-gray-700">
                <h2 class="font-bold text-gray-900 dark:text-white">Domain Blacklist</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Comma-separated list of domains. Users will not be able to register or login using any email address from specified domains.</p>
            </div>
            <div class="lg:col-span-3 p-6">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Domains</label>
                <textarea name="domain_blacklist" rows="3"
                    placeholder="spam.com, disposable.org"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none">{{ $auth['domain_blacklist'] ?? '' }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Separate domains with commas.</p>
            </div>
        </div>
    </div>

    {{-- ── Google Login ─────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm"
         x-data="{ open: {{ ($auth['google_login_enabled'] ?? false) ? 'true' : 'false' }} }">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-0">
            <div class="lg:col-span-2 p-6 border-b lg:border-b-0 lg:border-r border-gray-100 dark:border-gray-700">
                <h2 class="font-bold text-gray-900 dark:text-white">Google Login</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure Google OAuth authentication settings.</p>
            </div>
            <div class="lg:col-span-3 p-6 space-y-4">

                {{-- Enable toggle --}}
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Google login</p>
                        <p class="text-xs text-gray-400 mt-0.5">Enable logging into the site via Google.</p>
                    </div>
                    <label class="flex items-center cursor-pointer flex-shrink-0 ml-4">
                        <input type="hidden" name="google_login_enabled" value="0">
                        <div class="relative" @click="open = !open">
                            <input type="checkbox" name="google_login_enabled" value="1" class="sr-only" :checked="open">
                            <div class="w-12 h-6 rounded-full transition-colors" :class="open ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="open ? 'translate-x-6' : ''"></div>
                        </div>
                    </label>
                </div>

                {{-- Credentials (shown when enabled) --}}
                <div x-show="open" x-cloak class="space-y-3 border-t border-gray-50 dark:border-gray-700 pt-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Google Client ID</label>
                        <input type="text" name="google_client_id"
                            value="{{ old('google_client_id', $auth['google_client_id'] ?? '') }}"
                            placeholder="442813778020-xxxxxxxxxxxxxxxx.apps.googleusercontent.com"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Google Client Secret</label>
                        <input type="password" name="google_client_secret"
                            value="{{ old('google_client_secret', $auth['google_client_secret'] ?? '') }}"
                            placeholder="GOCSPX-xxxxxxxxxxxxxxxxxxxxxxxx"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                    </div>
                    <p class="text-xs text-gray-400">
                        Get credentials from
                        <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-brand-500 hover:underline">Google Cloud Console</a>.
                        Add authorized redirect URI: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{{ url('/auth/google/callback') }}</code>
                    </p>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Facebook Login ───────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm"
         x-data="{ open: {{ ($auth['facebook_login_enabled'] ?? false) ? 'true' : 'false' }} }">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-0">
            <div class="lg:col-span-2 p-6 border-b lg:border-b-0 lg:border-r border-gray-100 dark:border-gray-700">
                <h2 class="font-bold text-gray-900 dark:text-white">Facebook Login</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure Facebook OAuth authentication settings.</p>
            </div>
            <div class="lg:col-span-3 p-6 space-y-4">

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Facebook login</p>
                        <p class="text-xs text-gray-400 mt-0.5">Enable logging into the site via Facebook.</p>
                    </div>
                    <label class="flex items-center cursor-pointer flex-shrink-0 ml-4">
                        <input type="hidden" name="facebook_login_enabled" value="0">
                        <div class="relative" @click="open = !open">
                            <input type="checkbox" name="facebook_login_enabled" value="1" class="sr-only" :checked="open">
                            <div class="w-12 h-6 rounded-full transition-colors" :class="open ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="open ? 'translate-x-6' : ''"></div>
                        </div>
                    </label>
                </div>

                <div x-show="open" x-cloak class="space-y-3 border-t border-gray-50 dark:border-gray-700 pt-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Facebook App ID</label>
                        <input type="text" name="facebook_client_id"
                            value="{{ old('facebook_client_id', $auth['facebook_client_id'] ?? '') }}"
                            placeholder="1234567890123456"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Facebook App Secret</label>
                        <input type="password" name="facebook_client_secret"
                            value="{{ old('facebook_client_secret', $auth['facebook_client_secret'] ?? '') }}"
                            placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                    </div>
                    <p class="text-xs text-gray-400">
                        Get credentials from
                        <a href="https://developers.facebook.com/apps/" target="_blank" class="text-brand-500 hover:underline">Facebook Developers</a>.
                        Add Valid OAuth Redirect URI: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{{ url('/auth/facebook/callback') }}</code>
                    </p>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Save button ─────────────────────────────────────────────── --}}
    <div class="flex justify-end">
        <button type="submit"
            class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Save changes
        </button>
    </div>

</div>
</form>
@endsection
