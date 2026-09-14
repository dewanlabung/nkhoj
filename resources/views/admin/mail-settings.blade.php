@extends('layouts.admin')
@section('title', 'Mail Settings')

@section('content')
<div class="max-w-2xl">
    <nav class="text-xs text-gray-400 mb-4 flex items-center gap-1.5">
        <a href="/admin" class="hover:text-brand-500">Home</a>
        <span>›</span><span>Mail Settings</span>
    </nav>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-xl text-sm">
        @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Mail Server Configuration</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Configure SMTP settings for sending emails.</p>

        <form method="POST" action="/admin/mail-settings" class="space-y-6">
            @csrf

            {{-- Mailer Type --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Mail Driver</label>
                <select name="mail_mailer" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500" required>
                    <option value="smtp" {{ old('mail_mailer', $settings['mail_mailer']) === 'smtp' ? 'selected' : '' }}>SMTP</option>
                    <option value="sendmail" {{ old('mail_mailer', $settings['mail_mailer']) === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                    <option value="log" {{ old('mail_mailer', $settings['mail_mailer']) === 'log' ? 'selected' : '' }}>Log (Testing)</option>
                </select>
            </div>

            {{-- SMTP Section --}}
            <div id="smtpSection" class="space-y-4 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg border border-gray-200 dark:border-gray-600">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">SMTP Configuration</p>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Host</label>
                        <input type="text" name="mail_host" value="{{ old('mail_host', $settings['mail_host']) }}"
                            placeholder="smtp.gmail.com" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">e.g., smtp.gmail.com, mail.yourdomain.com</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Port</label>
                        <input type="number" name="mail_port" value="{{ old('mail_port', $settings['mail_port']) }}"
                            placeholder="587" min="1" max="65535" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">587 (TLS) or 465 (SSL)</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Encryption</label>
                    <select name="mail_encryption" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">None</option>
                        <option value="tls" {{ old('mail_encryption', $settings['mail_encryption']) === 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ old('mail_encryption', $settings['mail_encryption']) === 'ssl' ? 'selected' : '' }}>SSL</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                        <input type="email" name="mail_username" value="{{ old('mail_username', $settings['mail_username']) }}"
                            placeholder="your-email@gmail.com" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <input type="password" name="mail_password" value="{{ old('mail_password', $settings['mail_password']) }}"
                            placeholder="••••••••" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">For Gmail, use an <strong>App Password</strong> (not your regular password)</p>
                    </div>
                </div>
            </div>

            {{-- From Address Section --}}
            <div class="space-y-4 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg border border-gray-200 dark:border-gray-600">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Sender Information</p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Email Address</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address']) }}"
                        placeholder="noreply@example.com" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" required>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Must match your SMTP account email or be authorized by the mail provider</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}"
                        placeholder="{{ config('app.name') }}" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" required>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" name="test" value="1" class="flex-1 py-2.5 text-sm font-semibold text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-700 rounded-lg hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors">
                    Test Connection
                </button>
                <button type="submit" class="flex-1 py-2.5 text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-lg transition-colors">
                    Save Settings
                </button>
            </div>
        </form>

        {{-- Help Section --}}
        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Gmail Setup Guide</h3>
            <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-400 list-decimal list-inside">
                <li>Enable 2-Step Verification in your Google Account</li>
                <li>Create an <strong>App Password</strong> at <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-brand-500 hover:underline">myaccount.google.com/apppasswords</a></li>
                <li>Use the generated 16-character password above (copy without spaces)</li>
                <li>Host: <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">smtp.gmail.com</code></li>
                <li>Port: <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">587</code> (TLS)</li>
            </ol>
        </div>
    </div>
</div>

<script>
document.querySelector('[name="mail_mailer"]').addEventListener('change', function() {
    document.getElementById('smtpSection').style.display = this.value === 'smtp' ? 'block' : 'none';
});
</script>
@endsection
