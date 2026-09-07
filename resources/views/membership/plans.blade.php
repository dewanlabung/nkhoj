@extends('layouts.app')
@section('title', 'Membership Plans')

@section('content')
<div class="max-w-5xl mx-auto">

    {{-- Hero --}}
    <div class="text-center mb-10">
        <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-3">Unlock Pro Access</h1>
        <p class="text-gray-500 dark:text-gray-400 max-w-xl mx-auto">Get unlimited access to premium articles, recipes, questions, and exclusive content.</p>
    </div>

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm text-center">
        ⚠️ {{ session('error') }}
    </div>
    @endif
    @if(request('cancelled'))
    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-xl text-sm text-center">
        Payment was cancelled. No charge was made.
    </div>
    @endif

    {{-- Active subscription banner --}}
    @if($activeSub)
    <div class="mb-8 p-5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-2xl flex items-center justify-between flex-wrap gap-3">
        <div>
            <p class="font-bold text-green-800 dark:text-green-300">✅ You have an active <span class="text-green-700">{{ $activeSub->plan->name }}</span> membership</p>
            <p class="text-sm text-green-600 dark:text-green-400 mt-0.5">
                @if($activeSub->ends_at)
                    Renews {{ $activeSub->ends_at->format('M d, Y') }}
                @else
                    Lifetime access — never expires
                @endif
            </p>
        </div>
        <form method="POST" action="/membership/cancel" onsubmit="return confirm('Cancel your subscription?')">
            @csrf
            <button class="text-sm text-red-600 hover:underline border border-red-200 px-4 py-2 rounded-lg">Cancel Plan</button>
        </form>
    </div>
    @endif

    {{-- Plan cards --}}
    @if($plans->isEmpty())
    <div class="text-center py-20 text-gray-400">
        <p class="text-4xl mb-3">🏷️</p>
        <p>No membership plans available yet.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-{{ min($plans->count(), 3) }} gap-6">
        @foreach($plans as $plan)
        @php
            $isPopular = $loop->iteration === 2 || ($plans->count() === 1);
            $isCurrent = $activeSub && $activeSub->plan_id === $plan->id;
        @endphp
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl border {{ $isPopular ? 'border-brand-400 shadow-lg shadow-brand-100/50 dark:shadow-none' : 'border-gray-100 dark:border-gray-700' }} p-7 flex flex-col">
            @if($isPopular)
            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                <span class="bg-brand-500 text-white text-xs font-bold px-4 py-1 rounded-full shadow">Most Popular</span>
            </div>
            @endif

            <div class="mb-5">
                <h3 class="text-lg font-black text-gray-900 dark:text-white mb-1">{{ $plan->name }}</h3>
                @if($plan->description)
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                @endif
            </div>

            <div class="mb-6">
                @if($plan->price === 0)
                <p class="text-3xl font-black text-gray-900 dark:text-white">Free</p>
                @else
                <p class="text-3xl font-black text-gray-900 dark:text-white">
                    ${{ number_format($plan->price / 100, 0) }}
                    <span class="text-base font-medium text-gray-400">/ {{ $plan->billing_cycle }}</span>
                </p>
                @endif
            </div>

            @if($plan->features)
            <ul class="space-y-2.5 mb-8 flex-1">
                @foreach($plan->features as $feature)
                <li class="flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    {{ $feature }}
                </li>
                @endforeach
            </ul>
            @else
            <div class="flex-1"></div>
            @endif

            @if($isCurrent)
            <div class="w-full py-3 text-center bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 font-semibold rounded-xl text-sm border border-green-200 dark:border-green-700">
                ✅ Current Plan
            </div>
            @elseif(auth()->check())
            <a href="/membership/{{ $plan->id }}/checkout"
                class="block w-full py-3 text-center font-semibold rounded-xl text-sm transition-colors
                    {{ $isPopular ? 'bg-brand-500 hover:bg-brand-600 text-white' : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-white' }}">
                {{ $plan->price === 0 ? 'Get Started Free' : 'Subscribe Now' }}
            </a>
            @else
            <a href="/login?next=/membership"
                class="block w-full py-3 text-center font-semibold rounded-xl text-sm transition-colors
                    {{ $isPopular ? 'bg-brand-500 hover:bg-brand-600 text-white' : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-white' }}">
                Sign In to Subscribe
            </a>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Trust badges --}}
    <div class="mt-10 flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400 dark:text-gray-500">
        <span class="flex items-center gap-1.5">🔒 Stripe · PayPal · Bank Transfer</span>
        <span class="flex items-center gap-1.5">↩️ Cancel anytime</span>
        <span class="flex items-center gap-1.5">📧 Instant access after payment</span>
    </div>
</div>
@endsection
