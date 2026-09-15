@extends('layouts.admin')
@section('title', 'Security Settings')

@section('content')
@php
$sec = $s['security'] ?? [];
$cap = $s['captcha'] ?? [];
$cronToken = $s['cron_token'] ?? null;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- ═══ LEFT COLUMN (3/5) ══════════════════════════════════ --}}
    <div class="lg:col-span-3 space-y-5">

        {{-- General Security --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 dark:text-white mb-5">General</h2>

            <form method="POST" action="/admin/security" class="space-y-6">
                @csrf

                {{-- Login Security --}}
                <div>
                    <h3 class="text-sm font-bold text-brand-600 dark:text-brand-400 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Login Security
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">
                                Max Login Attempts <span class="text-red-400">*</span>
                            </label>
                            <input type="number" name="max_login_attempts" min="1" max="100"
                                value="{{ old('max_login_attempts', $sec['max_login_attempts'] ?? 5) }}"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <p class="text-xs text-gray-400 mt-1">Lock account after this many failed attempts.</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">
                                Lockout Time (minutes) <span class="text-red-400">*</span>
                            </label>
                            <input type="number" name="lockout_time" min="1" max="1440"
                                value="{{ old('lockout_time', $sec['lockout_time'] ?? 5) }}"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <p class="text-xs text-gray-400 mt-1">Wait time after exceeding login limits.</p>
                        </div>
                    </div>
                </div>

                {{-- Password Security --}}
                <div class="pt-4 border-t border-gray-50 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-brand-600 dark:text-brand-400 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Password Security
                    </h3>
                    <div class="grid grid-cols-2 gap-4 items-start">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">
                                Min Password Length <span class="text-red-400">*</span>
                            </label>
                            <input type="number" name="min_password_length" min="4" max="128"
                                value="{{ old('min_password_length', $sec['min_password_length'] ?? 8) }}"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <p class="text-xs text-gray-400 mt-1">Minimum characters required for new passwords.</p>
                        </div>
                        <div x-data="{ on: {{ ($sec['password_complexity'] ?? false) ? 'true' : 'false' }} }">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-2">Complexity Requirement</label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="password_complexity" value="0">
                                <div class="relative" @click="on = !on">
                                    <input type="checkbox" name="password_complexity" value="1" class="sr-only" :checked="on">
                                    <div class="w-11 h-6 rounded-full transition-colors" :class="on ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-5' : ''"></div>
                                </div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Require numbers and special characters</span>
                            </label>
                            <p class="text-xs text-gray-400 mt-1 ml-14">Users must use at least one number and special character if enabled.</p>
                        </div>
                    </div>
                </div>

                {{-- Spam Protection --}}
                <div class="pt-4 border-t border-gray-50 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-brand-600 dark:text-brand-400 mb-1 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        Spam Protection
                    </h3>
                    <p class="text-xs text-gray-400 mb-4">Manage how external links are processed to protect your SEO score and prevent spam.</p>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Post Content</label>
                            <select name="post_links" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="nofollow" {{ ($sec['post_links'] ?? 'nofollow') === 'nofollow' ? 'selected' : '' }}>Add "Nofollow" (SEO Safe)</option>
                                <option value="remove"   {{ ($sec['post_links'] ?? '') === 'remove' ? 'selected' : '' }}>Remove Links (Keep Text)</option>
                                <option value="allow"    {{ ($sec['post_links'] ?? '') === 'allow' ? 'selected' : '' }}>Allow All Links</option>
                                <option value="blank"    {{ ($sec['post_links'] ?? '') === 'blank' ? 'selected' : '' }}>Open in New Tab (target=_blank)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Public Interaction Content <span class="text-gray-400 font-normal">(Comments, user profiles)</span></label>
                            <select name="public_links" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="remove"   {{ ($sec['public_links'] ?? 'remove') === 'remove' ? 'selected' : '' }}>Remove Links (Keep Text)</option>
                                <option value="nofollow" {{ ($sec['public_links'] ?? '') === 'nofollow' ? 'selected' : '' }}>Add "Nofollow" (SEO Safe)</option>
                                <option value="allow"    {{ ($sec['public_links'] ?? '') === 'allow' ? 'selected' : '' }}>Allow All Links</option>
                            </select>
                        </div>
                    </div>
                </div>

                @if(session('success') && !request()->has('captcha'))
                <div class="text-sm text-green-600 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg px-4 py-2.5">
                    {{ session('success') }}
                </div>
                @endif

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Cron Job Token --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6"
            x-data="{ show: false }">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Cron Job Token
                </h2>
                @if($cronToken)
                <span class="text-xs font-semibold px-2.5 py-1 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full border border-green-200 dark:border-green-700">Active</span>
                @else
                <span class="text-xs font-semibold px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-400 rounded-full border border-gray-200 dark:border-gray-600">Inactive</span>
                @endif
            </div>

            <div class="mb-2">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Token</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" readonly
                        value="{{ $cronToken ?? str_repeat('•', 30) }}"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 pr-10 font-mono focus:outline-none bg-gray-50 dark:bg-gray-700/50">
                    <button type="button" @click="show = !show"
                        class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
            </div>
            <p class="text-xs text-gray-400 mb-4">This secure token is required to execute automated tasks. Any changes (Generate or Revoke) are saved immediately.</p>

            <div class="flex items-center gap-2">
                <form method="POST" action="/admin/security/cron/generate" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-3.5 py-2 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-700 text-xs font-semibold rounded-lg hover:bg-green-100 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Generate
                    </button>
                </form>

                @if($cronToken)
                <button type="button"
                    onclick="navigator.clipboard.writeText('{{ $cronToken }}').then(()=>{ this.textContent='✓ Copied'; setTimeout(()=>this.textContent='Copy',2000) })"
                    class="flex items-center gap-1.5 px-3.5 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-700 text-xs font-semibold rounded-lg hover:bg-blue-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    Copy
                </button>

                <form method="POST" action="/admin/security/cron/revoke" class="inline"
                    onsubmit="return confirm('Revoke the cron token? Existing cron jobs will stop working.')">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-3.5 py-2 bg-red-50 dark:bg-red-900/20 text-red-400 border border-red-200 dark:border-red-700 text-xs font-semibold rounded-lg hover:bg-red-100 hover:text-red-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Revoke
                    </button>
                </form>

                <div class="ml-auto text-xs text-gray-400 font-mono break-all">
                    <span>URL: {{ url('/api/cron?token=' . substr($cronToken,0,6)) }}…</span>
                </div>
                @endif
            </div>
        </div>
    </div>

        {{-- Security Headers --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6"
            x-data="{ on: {{ ($sec['security_headers_enabled'] ?? false) ? 'true' : 'false' }} }">
            <h2 class="font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Security Headers
            </h2>
            <p class="text-xs text-gray-400 mb-4">Sends browser security headers on every response: X-Frame-Options (clickjacking), X-Content-Type-Options (MIME sniffing), Referrer-Policy, and Permissions-Policy.</p>
            <form method="POST" action="/admin/security/headers">
                @csrf
                <input type="hidden" name="security_headers_enabled" :value="on ? '1' : '0'">
                <label class="flex items-center gap-3 cursor-pointer" @click="on = !on">
                    <div class="relative flex-shrink-0">
                        <div class="w-11 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-5' : ''"></div>
                    </div>
                    <span class="text-sm text-gray-700 dark:text-gray-300" x-text="on ? 'Enabled' : 'Disabled'"></span>
                </label>
                <button type="submit" class="mt-3 text-xs bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg px-3 py-1.5 transition-colors">Save</button>
            </form>
        </div>

        {{-- Rate Limits --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            @php $rl = $sec['rate_limits'] ?? []; @endphp
            <h2 class="font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Rate Limits (per minute per user)
            </h2>
            <p class="text-xs text-gray-400 mb-4">Controls how many requests a single user can make per minute before they're throttled.</p>
            <form method="POST" action="/admin/security/rate-limits" class="grid grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Login Attempts</label>
                    <input type="number" name="rate_login_per_minute" min="1" max="100"
                        value="{{ old('rate_login_per_minute', $rl['login_per_minute'] ?? 5) }}"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">API Requests</label>
                    <input type="number" name="rate_api_per_minute" min="1" max="10000"
                        value="{{ old('rate_api_per_minute', $rl['api_per_minute'] ?? 60) }}"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Comments</label>
                    <input type="number" name="rate_comments_per_minute" min="1" max="1000"
                        value="{{ old('rate_comments_per_minute', $rl['comments_per_minute'] ?? 10) }}"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Posts / Uploads</label>
                    <input type="number" name="rate_posts_per_minute" min="1" max="1000"
                        value="{{ old('rate_posts_per_minute', $rl['posts_per_minute'] ?? 5) }}"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="col-span-2">
                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg px-4 py-2.5 transition-colors">Save Rate Limits</button>
                </div>
            </form>
        </div>

    </div>

    {{-- ═══ RIGHT COLUMN (2/5) ══════════════════════════════════ --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Captcha Settings --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6"
            x-data="{ provider: '{{ $cap['provider'] ?? 'turnstile' }}', enabled: {{ ($cap['enabled'] ?? false) ? 'true' : 'false' }}, showSecret: false }">
            <h2 class="font-bold text-gray-900 dark:text-white mb-5">Captcha Settings</h2>

            <form method="POST" action="/admin/security/captcha" class="space-y-4">
                @csrf
                <input type="hidden" name="captcha_provider" :value="provider">

                {{-- Status toggle --}}
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Status</span>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="captcha_enabled" value="0">
                        <div class="relative" @click="enabled = !enabled">
                            <input type="checkbox" name="captcha_enabled" value="1" class="sr-only" :checked="enabled">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="enabled ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="enabled ? 'translate-x-5' : ''"></div>
                        </div>
                        <span class="text-sm text-gray-500" x-text="enabled ? 'Enable' : 'Disabled'"></span>
                    </label>
                </div>

                {{-- Provider tabs --}}
                <div class="grid grid-cols-2 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                    <button type="button" @click="provider = 'turnstile'"
                        :class="provider === 'turnstile' ? 'bg-brand-500 text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'"
                        class="py-2.5 text-xs font-bold transition-colors">
                        Cloudflare Turnstile
                    </button>
                    <button type="button" @click="provider = 'recaptcha'"
                        :class="provider === 'recaptcha' ? 'bg-brand-500 text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'"
                        class="py-2.5 text-xs font-bold transition-colors border-l border-gray-200 dark:border-gray-600">
                        Google reCAPTCHA
                    </button>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Site Key</label>
                    <input type="text" name="captcha_site_key"
                        value="{{ old('captcha_site_key', $cap['site_key'] ?? '') }}"
                        placeholder="{{ $cap['provider'] ?? 'turnstile' === 'turnstile' ? '0x4AAAAAAA...' : '6LcXXXXX...' }}"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Secret Key</label>
                    <div class="relative">
                        <input :type="showSecret ? 'text' : 'password'" name="captcha_secret"
                            value="{{ old('captcha_secret', $cap['secret'] ?? '') }}"
                            placeholder="••••••"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 pr-10 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <button type="button" @click="showSecret = !showSecret" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    Save Changes
                </button>
            </form>

            {{-- Warning box --}}
            <div class="mt-4 flex gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-4">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5C2.962 18.333 3.924 20 5.464 20z"/></svg>
                <div>
                    <p class="text-xs font-bold text-amber-700 dark:text-amber-400 mb-1">Warning</p>
                    <p class="text-xs text-amber-600 dark:text-amber-500 leading-relaxed">
                        We strongly recommend using <strong>Cloudflare Turnstile</strong> as it offers a privacy-focused, cookie-free solution (GDPR compliant). Google reCAPTCHA may collect user data and track visitors, which requires you to obtain explicit user consent via a Cookie Banner. Ensuring compliance with data privacy laws is the sole responsibility of the site administrator.
                    </p>
                </div>
            </div>
        </div>

        {{-- Two-Factor Auth placeholder --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Two-Factor Authentication
            </h2>
            <p class="text-xs text-gray-400 mb-4">Require users to verify their identity with a second factor.</p>
            <div class="divide-y divide-gray-50 dark:divide-gray-700 border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
                @foreach(['Admin accounts','Editor accounts'] as $acc)
                <label class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer">
                    <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">Require for {{ $acc }}</span>
                    <div x-data="{ on: false }" class="relative" @click="on = !on">
                        <div class="w-10 h-5 rounded-full transition-colors" :class="on ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-5' : ''"></div>
                    </div>
                </label>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-3 text-center">TOTP (Authenticator apps) — Coming soon</p>
        </div>

        {{-- Security Analysis Link --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-red-100 dark:border-red-900/40 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h2 class="font-bold text-gray-900 dark:text-white text-sm">Security Analysis</h2>
                    <p class="text-xs text-gray-400">IP bans, threat log, attack patterns</p>
                </div>
            </div>
            <a href="/admin/security/analysis"
               class="block w-full text-center bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg px-4 py-2.5 transition-colors">
                Open Analysis Dashboard →
            </a>
        </div>

        {{-- Audit Log quick-view --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h2 class="font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Recent Activity
            </h2>
            @php
            $recent = \App\Models\User::select('name','username','last_seen_at','role')
                ->whereNotNull('last_seen_at')->orderByDesc('last_seen_at')->take(5)->get();
            @endphp
            @if($recent->isEmpty())
            <p class="text-sm text-gray-400 text-center py-2">No activity yet.</p>
            @else
            <ul class="space-y-2">
                @foreach($recent as $u)
                <li class="flex items-center gap-2.5 text-sm">
                    <div class="w-7 h-7 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($u->name,0,1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-700 dark:text-gray-200 truncate">{{ $u->name }}</p>
                        <p class="text-xs text-gray-400">{{ $u->last_seen_at->diffForHumans() }} · {{ ucfirst($u->role) }}</p>
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
</div>
@endsection
