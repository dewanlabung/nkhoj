@extends('layouts.app')
@section('title', 'Set Up Two-Factor Authentication — nkhoj')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-8">
    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Set Up Two-Factor Authentication</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Scan the QR code with Google Authenticator or any TOTP app, then enter the 6-digit code to confirm.</p>

            {{-- QR Code --}}
            <div class="flex justify-center mb-6 bg-white rounded-xl p-4 border border-gray-100 dark:border-gray-600">
                {!! $qrSvg !!}
            </div>

            {{-- Manual key --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg px-4 py-3 mb-6">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Manual entry key:</p>
                <code class="text-sm font-mono text-gray-800 dark:text-gray-200 break-all">{{ $secret }}</code>
            </div>

            @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm rounded-lg px-4 py-3 mb-4">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="/two-factor/confirm">
                @csrf
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Authenticator Code</label>
                <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6" autofocus
                    class="w-full text-center text-2xl font-mono tracking-widest border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 mb-4"
                    placeholder="000000">
                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl transition-colors">
                    Enable Two-Factor Authentication
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
