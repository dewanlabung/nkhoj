@extends('layouts.admin')
@section('title', 'Email Settings')

@section('content')
@php
$em = $s['email'] ?? [];
$templates = [
    ['id' => 'pure-minimalist',    'label' => 'Pure Minimalist',    'bg' => '#fff',    'text' => '#111', 'accent' => '#111'],
    ['id' => 'corporate-accent',   'label' => 'Corporate Accent',   'bg' => '#fff',    'text' => '#222', 'accent' => '#2563eb'],
    ['id' => 'modern-accent',      'label' => 'Modern Accent',      'bg' => '#f8fafc', 'text' => '#1e293b','accent' => '#0ea5e9'],
    ['id' => 'linear-look',        'label' => 'Linear Look',        'bg' => '#fff',    'text' => '#333', 'accent' => '#7c3aed'],
    ['id' => 'obsidian',           'label' => 'Obsidian',           'bg' => '#18181b', 'text' => '#fff', 'accent' => '#a1a1aa'],
    ['id' => 'brand-cover',        'label' => 'Brand Cover',        'bg' => '#fff',    'text' => '#222', 'accent' => '#6366f1'],
    ['id' => 'editorial-chic',     'label' => 'Editorial Chic',     'bg' => '#fafaf9', 'text' => '#292524','accent' => '#d97706'],
    ['id' => 'elegant-minimal',    'label' => 'Elegant Minimal',    'bg' => '#fff',    'text' => '#374151','accent' => '#6b7280'],
    ['id' => 'elegant-corporate',  'label' => 'Elegant Corporate',  'bg' => '#f9fafb', 'text' => '#111827','accent' => '#1d4ed8'],
    ['id' => 'midnight-saas',      'label' => 'Midnight SaaS',      'bg' => '#0f172a', 'text' => '#f8fafc','accent' => '#38bdf8'],
];
$active = $em['template'] ?? 'pure-minimalist';
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

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- ═══ LEFT COLUMN — Options + Test Email ═══════════════════ --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Options --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 dark:text-white mb-5">Options</h2>

            <form method="POST" action="/admin/email-settings" class="space-y-5">
                @csrf

                {{-- Email Verification toggle --}}
                <div class="flex items-center justify-between" x-data="{ on: {{ ($em['verification'] ?? false) ? 'true' : 'false' }} }">
                    <div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Email Verification</p>
                        <p class="text-xs text-gray-400 mt-0.5">Require users to verify their email after registration.</p>
                    </div>
                    <div class="flex-shrink-0 ml-4">
                        <input type="hidden" name="email_verification" value="0">
                        <div class="relative w-12 h-6 cursor-pointer" @click="on = !on">
                            <input type="checkbox" name="email_verification" value="1" class="sr-only" :checked="on" @click.stop>
                            <div class="w-12 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-6' : ''"></div>
                        </div>
                    </div>
                </div>

                {{-- Contact forward toggle --}}
                <div class="flex items-center justify-between border-t border-gray-50 dark:border-gray-700 pt-4" x-data="{ on: {{ ($em['contact_forward'] ?? false) ? 'true' : 'false' }}, showEmail: {{ ($em['contact_forward'] ?? false) ? 'true' : 'false' }} }">
                    <div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Send Contact Messages to Email Address</p>
                        <p class="text-xs text-gray-400 mt-0.5">Forward contact form submissions to an inbox.</p>
                    </div>
                    <div class="flex-shrink-0 ml-4">
                        <input type="hidden" name="contact_forward" value="0">
                        <div class="relative w-12 h-6 cursor-pointer" @click="on = !on; showEmail = !showEmail">
                            <input type="checkbox" name="contact_forward" value="1" class="sr-only" :checked="on" @click.stop>
                            <div class="w-12 h-6 rounded-full transition-colors" :class="on ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-6' : ''"></div>
                        </div>
                    </div>
                </div>

                {{-- Contact email --}}
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">
                        Email <span class="font-normal text-gray-400">(Contact messages forwarded here)</span>
                    </label>
                    <input type="email" name="contact_email"
                        value="{{ old('contact_email', $em['contact_email'] ?? '') }}"
                        placeholder="admin@yourdomain.com"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-xs text-gray-400 mt-1">Contact messages will be sent to this email.</p>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Send Test Email --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 dark:text-white mb-1">Send Test Email</h2>
            <p class="text-xs text-gray-400 mb-4">
                You can send a test mail to check if your mail server is working.
            </p>
            <form method="POST" action="/admin/email-settings/test" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Email <span class="text-red-400">*</span></label>
                    <input type="email" name="test_email" required
                        placeholder="test@example.com"
                        value="{{ old('test_email', auth()->user()->email) }}"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <button type="submit"
                    class="w-full py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Send Email
                </button>
            </form>
        </div>
    </div>

    {{-- ═══ RIGHT COLUMN — SMTP Settings ══════════════════════════ --}}
    <div class="lg:col-span-3 space-y-5">

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 dark:text-white mb-5">Settings</h2>

            {{-- Gmail API connect card --}}
            @php $gmailEmail = \App\Services\Mail\GmailClient::connectedEmail(); @endphp
            <div class="mb-4 rounded-lg border {{ $gmailEmail ? 'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/10' : 'border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/30' }} p-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 {{ $gmailEmail ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}" viewBox="0 0 24 24" fill="currentColor"><path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 010 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.910 1.528-1.145C21.69 2.28 24 3.434 24 5.457z"/></svg>
                    <div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Gmail API</p>
                        @if($gmailEmail)
                        <p class="text-xs text-green-600 dark:text-green-400">Connected as <strong>{{ $gmailEmail }}</strong> · Set "Mail Service" to <strong>Gmail API</strong> below</p>
                        @else
                        <p class="text-xs text-gray-500 dark:text-gray-400">Send via Gmail API (bypasses SMTP, avoids SSL issues). Requires Google OAuth.</p>
                        @endif
                    </div>
                </div>
                @if($gmailEmail)
                <form method="POST" action="/admin/gmail/disconnect" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium border border-red-200 dark:border-red-800 px-3 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">Disconnect</button>
                </form>
                @else
                <a href="/admin/gmail/connect" class="flex-shrink-0 flex items-center gap-1.5 text-xs text-brand-600 dark:text-brand-400 font-medium border border-brand-200 dark:border-brand-700 px-3 py-1.5 rounded-lg hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Connect Gmail
                </a>
                @endif
            </div>

            <form method="POST" action="/admin/email-settings/smtp" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Mail Service --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Mail Service <span class="text-red-400">*</span></label>
                        <select name="mail_service" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            @foreach(['mailpit' => 'Mailpit (Local)', 'sendmail' => 'Sendmail', 'smtp' => 'Custom SMTP', 'gmail-api' => 'Gmail API (OAuth)', 'mailgun' => 'Mailgun', 'ses' => 'Amazon SES', 'postmark' => 'Postmark', 'resend' => 'Resend', 'brevo' => 'Brevo (Sendinblue)', 'sendgrid' => 'SendGrid'] as $val => $label)
                            <option value="{{ $val }}" {{ ($em['service'] ?? 'mailpit') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Mail Protocol --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Mail Protocol <span class="text-red-400">*</span></label>
                        <select name="mail_protocol" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="smtp"     {{ ($em['protocol'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="sendmail" {{ ($em['protocol'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                            <option value="phpmail"  {{ ($em['protocol'] ?? '') === 'phpmail' ? 'selected' : '' }}>PHPMail</option>
                        </select>
                    </div>

                    {{-- Encryption --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Encryption <span class="text-red-400">*</span></label>
                        <select name="mail_encryption" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="tls"  {{ ($em['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl"  {{ ($em['encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="none" {{ ($em['encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                </div>

                {{-- Host + Port --}}
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Mail Host</label>
                        <input type="text" name="mail_host"
                            value="{{ old('mail_host', $em['host'] ?? '') }}"
                            placeholder="smtp.mailgun.org"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Mail Port</label>
                        <input type="number" name="mail_port" min="1" max="65535"
                            value="{{ old('mail_port', $em['port'] ?? 587) }}"
                            placeholder="587"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                {{-- Username / Password --}}
                <div class="grid grid-cols-2 gap-4" x-data="{ showPw: false }">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Mail Username</label>
                        <input type="email" name="mail_username"
                            value="{{ old('mail_username', $em['username'] ?? '') }}"
                            placeholder="user@yourdomain.com"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Mail Password</label>
                        <div class="relative">
                            <input :type="showPw ? 'text' : 'password'" name="mail_password"
                                placeholder="Leave blank to keep current"
                                class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 pr-9 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <button type="button" @click="showPw = !showPw"
                                class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 transition-colors">
                                <svg x-show="!showPw" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPw" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- From address / Mail Title / Reply-To --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">From Address</label>
                        <input type="email" name="mail_from"
                            value="{{ old('mail_from', $em['from_address'] ?? '') }}"
                            placeholder="no-reply@yourdomain.com"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Mail Title (From Name)</label>
                        <input type="text" name="mail_title"
                            value="{{ old('mail_title', $em['title'] ?? config('app.name')) }}"
                            placeholder="{{ config('app.name') }}"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Reply-To</label>
                        <input type="email" name="reply_to"
                            value="{{ old('reply_to', $em['reply_to'] ?? '') }}"
                            placeholder="support@yourdomain.com"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Email Templates --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 dark:text-white mb-5 text-brand-600 dark:text-brand-400">Email Templates</h2>

            <form method="POST" action="/admin/email-settings/template"
                x-data="{ selected: '{{ $active }}' }">
                @csrf
                <input type="hidden" name="template" :value="selected">

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 mb-5">
                    @foreach($templates as $t)
                    <button type="button"
                        @click="selected = '{{ $t['id'] }}'"
                        :class="selected === '{{ $t['id'] }}' ? 'ring-2 ring-brand-500' : 'ring-1 ring-gray-200 dark:ring-gray-600 hover:ring-brand-300'"
                        class="rounded-xl overflow-hidden transition-all focus:outline-none">
                        {{-- Template preview card --}}
                        <div style="background: {{ $t['bg'] }}; min-height: 80px;" class="relative p-3 flex flex-col justify-between">
                            {{-- fake header bar --}}
                            <div class="space-y-1">
                                <div style="background: {{ $t['accent'] }}; height: 4px; border-radius: 2px; width: 60%;"></div>
                                <div style="background: {{ $t['accent'] }}22; height: 3px; border-radius: 2px; width: 80%;"></div>
                            </div>
                            {{-- fake body lines --}}
                            <div class="space-y-1 mt-2">
                                <div style="background: {{ $t['text'] }}18; height: 2px; border-radius: 2px;"></div>
                                <div style="background: {{ $t['text'] }}12; height: 2px; border-radius: 2px; width: 75%;"></div>
                                <div style="background: {{ $t['text'] }}12; height: 2px; border-radius: 2px; width: 55%;"></div>
                            </div>
                            {{-- fake button --}}
                            <div style="background: {{ $t['accent'] }}; height: 8px; border-radius: 4px; width: 50%; margin-top: 6px;"></div>
                            {{-- selected checkmark --}}
                            <div x-show="selected === '{{ $t['id'] }}'"
                                class="absolute top-1.5 right-1.5 w-5 h-5 bg-brand-500 rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                        <div style="background: {{ $t['bg'] }}; border-top: 1px solid {{ $t['text'] }}11;"
                            class="px-2 py-1.5 text-center">
                            <p style="color: {{ $t['text'] }}; font-size: 10px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $t['label'] }}</p>
                        </div>
                    </button>
                    @endforeach
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
