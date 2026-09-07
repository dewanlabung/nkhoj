@extends('layouts.app')
@section('title', 'Welcome to Pro!')

@section('content')
<div class="max-w-lg mx-auto text-center py-16">
    <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    </div>

    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-2">
        {{ $isFree ? 'You\'re in!' : 'Payment Successful!' }}
    </h1>

    <p class="text-gray-500 dark:text-gray-400 mb-2">
        @if($plan)
            You now have <strong class="text-gray-700 dark:text-gray-300">{{ $plan->name }}</strong> access.
        @else
            Your Pro membership is now active.
        @endif
    </p>
    <p class="text-sm text-gray-400 dark:text-gray-500 mb-8">Enjoy unlimited access to all premium content.</p>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="/" class="px-6 py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl transition-colors text-sm">
            Explore Content
        </a>
        <a href="/account/settings" class="px-6 py-3 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm">
            My Account
        </a>
    </div>
</div>
@endsection
