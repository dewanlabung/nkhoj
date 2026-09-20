@extends('layouts.app')
@section('title', 'Verify Your Email')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Verify your email</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                We sent a verification
                @if($method === 'link_only')
                    link
                @elseif($method === 'otp_only')
                    code
                @else
                    code and link
                @endif
                to<br>
                <strong class="text-gray-700 dark:text-gray-300">{{ $maskedEmail }}</strong>
            </p>
        </div>

        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-xl text-sm text-center">
            {{ session('success') }}
        </div>
        @endif

        @if($method !== 'link_only')
        {{-- OTP form --}}
        <form method="POST" action="/verify-email/otp" x-data="otpInput()" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 text-center">Enter your 6-digit code</label>

                <div class="flex gap-2 justify-center" @paste.window="handlePaste($event)">
                    @for($i = 0; $i < 6; $i++)
                    <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]"
                        x-ref="d{{ $i }}"
                        @input="onInput({{ $i }}, $event)"
                        @keydown="onKeydown({{ $i }}, $event)"
                        class="w-11 h-14 text-center text-2xl font-bold border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:border-brand-500 dark:focus:border-brand-400 transition-colors
                        @error('code') border-red-400 dark:border-red-500 @enderror">
                    @endfor
                </div>

                <input type="hidden" name="code" x-bind:value="digits.join('')">

                @error('code')
                <p class="mt-3 text-xs text-red-500 text-center">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" x-bind:disabled="digits.join('').length < 6"
                class="w-full py-3 bg-brand-600 hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-colors">
                Verify & Continue
            </button>
        </form>
        @endif

        @if($method === 'link_only')
        <div class="text-center py-4">
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-sm text-blue-700 dark:text-blue-300">
                Check your inbox and click the verification link to activate your account.
            </div>
        </div>
        @elseif($method === 'both')
        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-xs text-blue-600 dark:text-blue-400 text-center">
            A verification link was also sent to your email if you prefer to click that instead.
        </div>
        @endif

        {{-- Resend --}}
        <div class="mt-5 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">Didn't get it?</p>
            <form method="POST" action="/verify-email/resend" class="inline">
                @csrf
                <button type="submit" class="text-sm text-brand-600 hover:text-brand-700 font-medium mt-0.5">
                    Resend
                </button>
            </form>
        </div>

        <div class="mt-4 text-center">
            <a href="/login" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">Back to sign in</a>
        </div>

    </div>
</div>

@if($method !== 'link_only')
<script>
function otpInput() {
    return {
        digits: Array(6).fill(''),
        onInput(i, e) {
            const val = e.target.value.replace(/\D/g, '').slice(-1);
            this.digits[i] = val;
            e.target.value = val;
            if (val && i < 5) this.$refs['d' + (i + 1)].focus();
        },
        onKeydown(i, e) {
            if (e.key === 'Backspace' && !this.digits[i] && i > 0) this.$refs['d' + (i - 1)].focus();
            if (e.key === 'ArrowLeft' && i > 0) this.$refs['d' + (i - 1)].focus();
            if (e.key === 'ArrowRight' && i < 5) this.$refs['d' + (i + 1)].focus();
        },
        handlePaste(e) {
            const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
            if (!text) return;
            [...text].forEach((ch, i) => {
                this.digits[i] = ch;
                if (this.$refs['d' + i]) this.$refs['d' + i].value = ch;
            });
            const next = Math.min(text.length, 5);
            if (this.$refs['d' + next]) this.$refs['d' + next].focus();
        }
    }
}
</script>
@endif
@endsection
