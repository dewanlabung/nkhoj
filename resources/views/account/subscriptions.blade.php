@extends('layouts.account')
@section('title', 'Subscriptions')

@section('main')
<div class="space-y-5">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
            <h2 class="font-bold text-gray-900 dark:text-white">Membership & Subscriptions</h2>
            <p class="text-sm text-gray-400 mt-0.5">Your current and past memberships</p>
        </div>

        @if($subs->isEmpty())
        <div class="text-center py-14 px-6">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">No subscriptions yet</p>
            <a href="/membership" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl">
                Browse Plans
            </a>
        </div>
        @else
        <div class="divide-y divide-gray-50 dark:divide-gray-700">
            @foreach($subs as $sub)
            <div class="px-6 py-5 flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl {{ $sub->status === 'active' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-100 dark:bg-gray-700' }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 {{ $sub->status === 'active' ? 'text-green-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            @if($sub->status === 'active')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @endif
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $sub->plan->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            @if($sub->status === 'active')
                                Active
                                @if($sub->ends_at) · Renews {{ $sub->ends_at->format('M d, Y') }}
                                @else · Lifetime access @endif
                            @elseif($sub->status === 'pending_manual')
                                Pending manual payment confirmation
                            @elseif($sub->status === 'cancelled')
                                Cancelled{{ $sub->ends_at ? ' · Access until ' . $sub->ends_at->format('M d, Y') : '' }}
                            @else
                                {{ ucfirst($sub->status) }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold
                        {{ $sub->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' :
                           ($sub->status === 'pending_manual' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300' :
                           'bg-gray-100 dark:bg-gray-700 text-gray-500') }}">
                        {{ $sub->status === 'pending_manual' ? 'Pending' : ucfirst($sub->status) }}
                    </span>
                    @if($sub->status === 'active' && $sub->plan->price > 0)
                    <form method="POST" action="/membership/cancel" onsubmit="return confirm('Cancel your subscription?')">
                        @csrf
                        <button class="text-xs text-red-500 hover:text-red-700">Cancel</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="bg-brand-50 dark:bg-brand-900/20 rounded-2xl border border-brand-100 dark:border-brand-800 p-5 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <p class="font-semibold text-brand-800 dark:text-brand-300 text-sm">Upgrade your membership</p>
            <p class="text-xs text-brand-600 dark:text-brand-400 mt-0.5">Get access to premium articles, recipes and more</p>
        </div>
        <a href="/membership" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl flex-shrink-0">
            View Plans
        </a>
    </div>
</div>
@endsection
