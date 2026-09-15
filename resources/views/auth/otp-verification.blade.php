@extends('layouts.app')
@section('title', 'Email Verification')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-brand-100 dark:bg-brand-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Verify Your Email</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">We've sent a 6-digit code to your email</p>
            </div>

            <form id="otpForm" class="space-y-6">
                @csrf
                <div id="step1" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                        <input type="email" name="email" id="emailInput" placeholder="you@example.com" required
                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                    </div>
                    <button type="button" onclick="sendOtp()" class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-lg transition-colors">
                        Send OTP Code
                    </button>
                </div>

                <div id="step2" class="space-y-4 hidden">
                    <input type="hidden" name="email" id="emailField">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">OTP Code</label>
                        <input type="text" name="code" id="codeInput" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required
                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-center text-2xl letter-spacing tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-brand-500 transition font-mono">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center">Enter the 6-digit code sent to your email</p>
                    <button type="submit" class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-lg transition-colors">
                        Verify Code
                    </button>
                    <button type="button" onclick="resendOtp()" class="w-full py-2 text-sm text-brand-600 dark:text-brand-400 hover:text-brand-700 font-medium">
                        Didn't receive? Resend Code
                    </button>
                </div>

                <div id="messageDiv" class="text-sm text-center font-medium hidden"></div>
            </form>
        </div>
    </div>
</div>

<script>
function sendOtp() {
    const email = document.getElementById('emailInput').value.trim();
    if (!email) {
        showMessage('Please enter your email address', 'error');
        return;
    }

    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.remove('hidden');
    document.getElementById('emailField').value = email;

    fetch('/otp/send', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value},
        body: JSON.stringify({email: email})
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            showMessage(data.message, 'error');
            document.getElementById('step1').classList.remove('hidden');
            document.getElementById('step2').classList.add('hidden');
        } else {
            showMessage('Code sent! Check your email.', 'success');
        }
    })
    .catch(e => {
        showMessage('Failed to send OTP', 'error');
        document.getElementById('step1').classList.remove('hidden');
        document.getElementById('step2').classList.add('hidden');
    });
}

function resendOtp() {
    const email = document.getElementById('emailField').value;
    fetch('/otp/resend', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value},
        body: JSON.stringify({email: email})
    })
    .then(r => r.json())
    .then(data => showMessage(data.message, data.success ? 'success' : 'error'))
    .catch(() => showMessage('Failed to resend', 'error'));
}

document.getElementById('otpForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('emailField').value;
    const code = document.getElementById('codeInput').value;

    const res = await fetch('/otp/verify', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value},
        body: JSON.stringify({email, code})
    });

    const data = await res.json();
    if (data.success) {
        showMessage('✓ Email verified! Redirecting...', 'success');
        setTimeout(() => window.location.href = '/', 2000);
    } else {
        showMessage(data.message, 'error');
    }
});

function showMessage(msg, type) {
    const div = document.getElementById('messageDiv');
    div.textContent = msg;
    div.className = `text-sm text-center font-medium ${type === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}`;
    div.classList.remove('hidden');
}
</script>
@endsection
