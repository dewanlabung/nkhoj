@extends('layouts.app')
@section('title', 'Payment Pending')

@section('content')
<div class="max-w-lg mx-auto text-center py-12">
    <div class="w-20 h-20 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-3">Payment Pending</h1>
    <p class="text-gray-500 dark:text-gray-400 mb-6">
        Thank you! We have received your payment notification.<br>
        Your account will be activated within <strong class="text-gray-700 dark:text-gray-200">24 hours</strong> once we verify your payment.
    </p>
    <p class="text-sm text-gray-400 mb-8">
        You will receive a confirmation at <strong class="text-gray-600 dark:text-gray-300">{{ auth()->user()->email }}</strong> once your subscription is active.
    </p>
    <a href="/" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl text-sm">
        Return to homepage
    </a>
</div>
@endsection
