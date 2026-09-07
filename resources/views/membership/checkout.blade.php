@extends('layouts.app')
@section('title', 'Checkout — ' . $plan->name)

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Back --}}
    <a href="/membership" class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 mb-6">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to plans
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">

        {{-- Plan summary --}}
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">You're subscribing to</p>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-black text-gray-900 dark:text-white">{{ $plan->name }}</h2>
                <p class="text-xl font-black text-brand-600">
                    @if($plan->price === 0) Free
                    @else ${{ number_format($plan->price / 100, 2) }} <span class="text-sm font-normal text-gray-400">/ {{ $plan->billing_cycle }}</span>
                    @endif
                </p>
            </div>
            @if($plan->description)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $plan->description }}</p>
            @endif
        </div>

        <div class="px-6 py-6">

            @if($plan->price === 0)
            {{-- Free plan: just activate --}}
            <form method="POST" action="/membership/{{ $plan->id }}/process">
                @csrf
                <input type="hidden" name="method" value="free">
                <button class="w-full py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl text-base">
                    Activate Free Plan
                </button>
            </form>

            @else
            {{-- Payment method selector --}}
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Choose payment method</h3>

            <form method="POST" action="/membership/{{ $plan->id }}/process" id="checkout-form">
                @csrf
                <input type="hidden" name="method" id="selected-method" value="">

                <div class="space-y-3 mb-6" id="method-list">

                    @if($stripeEnabled)
                    <label class="method-option flex items-center gap-4 p-4 rounded-xl border-2 border-gray-100 dark:border-gray-600 cursor-pointer hover:border-brand-400 transition-colors" data-method="stripe">
                        <input type="radio" name="_method_radio" value="stripe" class="sr-only">
                        <div class="w-10 h-10 flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/20 rounded-lg flex-shrink-0">
                            <svg class="w-6 h-6 text-indigo-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 4c1.655 0 3 1.345 3 3s-1.345 3-3 3-3-1.345-3-3 1.345-3 3-3zm0 12c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white text-sm">Credit / Debit Card</p>
                            <p class="text-xs text-gray-400">Secure payment via Stripe</p>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-500 flex items-center justify-center method-radio-indicator">
                            <div class="w-2.5 h-2.5 rounded-full bg-brand-500 hidden method-radio-dot"></div>
                        </div>
                    </label>
                    @endif

                    @if($paypalEmail)
                    <label class="method-option flex items-center gap-4 p-4 rounded-xl border-2 border-gray-100 dark:border-gray-600 cursor-pointer hover:border-brand-400 transition-colors" data-method="paypal">
                        <input type="radio" name="_method_radio" value="paypal" class="sr-only">
                        <div class="w-10 h-10 flex items-center justify-center bg-blue-50 dark:bg-blue-900/20 rounded-lg flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600" viewBox="0 0 24 24" fill="currentColor"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c1.379 4.327-.375 7.504-4.137 8.635 1.88-.093 3.393-.95 4.374-2.45.857-1.312 1.037-2.985.37-5.644z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white text-sm">PayPal</p>
                            <p class="text-xs text-gray-400">Pay via PayPal — manual confirmation</p>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-500 flex items-center justify-center method-radio-indicator">
                            <div class="w-2.5 h-2.5 rounded-full bg-brand-500 hidden method-radio-dot"></div>
                        </div>
                    </label>
                    @endif

                    @if($bankDetails)
                    <label class="method-option flex items-center gap-4 p-4 rounded-xl border-2 border-gray-100 dark:border-gray-600 cursor-pointer hover:border-brand-400 transition-colors" data-method="bank">
                        <input type="radio" name="_method_radio" value="bank" class="sr-only">
                        <div class="w-10 h-10 flex items-center justify-center bg-green-50 dark:bg-green-900/20 rounded-lg flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white text-sm">Bank Transfer</p>
                            <p class="text-xs text-gray-400">Direct bank deposit — manual confirmation</p>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-500 flex items-center justify-center method-radio-indicator">
                            <div class="w-2.5 h-2.5 rounded-full bg-brand-500 hidden method-radio-dot"></div>
                        </div>
                    </label>
                    @endif

                </div>

                {{-- PayPal instructions (shown when paypal selected) --}}
                @if($paypalEmail)
                <div id="paypal-details" class="hidden mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-700">
                    <p class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">PayPal Payment Instructions</p>
                    <p class="text-sm text-blue-700 dark:text-blue-400 mb-3">
                        Send <strong>${{ number_format($plan->price / 100, 2) }}</strong> to:
                    </p>
                    <div class="bg-white dark:bg-gray-700 rounded-lg px-4 py-3 font-mono text-sm text-gray-800 dark:text-gray-200 mb-3 break-all">
                        {{ $paypalEmail }}
                    </div>
                    @if($paypalMe)
                    <a href="{{ $paypalMe }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Open PayPal.me link ↗
                    </a>
                    @endif
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">Include your email <strong>{{ auth()->user()->email }}</strong> in the payment note. After payment, click the button below and we will activate your account within 24 hours.</p>
                </div>
                @endif

                {{-- Bank instructions (shown when bank selected) --}}
                @if($bankDetails)
                <div id="bank-details" class="hidden mb-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-100 dark:border-green-700">
                    <p class="text-sm font-semibold text-green-800 dark:text-green-300 mb-3">Bank Transfer Details</p>
                    <dl class="space-y-2 text-sm">
                        @foreach($bankDetails as $label => $value)
                        @if($value)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400 font-medium">{{ $label }}</dt>
                            <dd class="text-gray-900 dark:text-white font-mono font-semibold">{{ $value }}</dd>
                        </div>
                        @endif
                        @endforeach
                    </dl>
                    <p class="text-xs text-green-700 dark:text-green-400 mt-3">Amount: <strong>${{ number_format($plan->price / 100, 2) }}</strong>. Use your email <strong>{{ auth()->user()->email }}</strong> as the reference. We will confirm within 24 hours.</p>
                </div>
                @endif

                <button type="submit" id="checkout-btn" disabled
                    class="w-full py-3.5 bg-brand-500 hover:bg-brand-600 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold rounded-xl text-base transition-colors">
                    Continue
                </button>
            </form>
            @endif

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const options = document.querySelectorAll('.method-option');
    const hiddenMethod = document.getElementById('selected-method');
    const btn = document.getElementById('checkout-btn');
    const paypalDetails = document.getElementById('paypal-details');
    const bankDetails = document.getElementById('bank-details');

    options.forEach(opt => {
        opt.addEventListener('click', function () {
            const method = this.dataset.method;
            hiddenMethod.value = method;

            options.forEach(o => {
                o.classList.remove('border-brand-400', 'bg-brand-50', 'dark:bg-brand-900/10');
                o.classList.add('border-gray-100', 'dark:border-gray-600');
                o.querySelector('.method-radio-dot').classList.add('hidden');
            });

            this.classList.remove('border-gray-100', 'dark:border-gray-600');
            this.classList.add('border-brand-400', 'bg-brand-50', 'dark:bg-brand-900/10');
            this.querySelector('.method-radio-dot').classList.remove('hidden');

            if (paypalDetails) paypalDetails.classList.toggle('hidden', method !== 'paypal');
            if (bankDetails) bankDetails.classList.toggle('hidden', method !== 'bank');

            btn.disabled = false;
            btn.textContent = method === 'stripe' ? 'Pay with Card →' :
                              method === 'paypal' ? 'I\'ve Paid via PayPal — Notify Admin' :
                              method === 'bank'   ? 'I\'ve Made the Bank Transfer — Notify Admin' : 'Continue';
        });
    });
});
</script>
@endsection
