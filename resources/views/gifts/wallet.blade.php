@extends('layouts.app')
@section('title', 'My Wallet — नखोज')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Balance card --}}
    <div class="bg-gradient-to-br from-brand-500 to-brand-700 rounded-2xl p-6 text-white shadow-lg">
        <p class="text-sm font-semibold opacity-80 mb-1">Coin Balance</p>
        <div class="flex items-end gap-3">
            <span class="text-5xl font-bold">{{ number_format($wallet->balance) }}</span>
            <span class="text-2xl mb-1">🪙</span>
        </div>
        <p class="text-xs opacity-70 mt-2">Use coins to send virtual gifts to creators you love</p>
    </div>

    {{-- Buy coins --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5">
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Buy Coins</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach([100 => 'Starter', 500 => 'Popular', 1000 => 'Pro', 2500 => 'Elite'] as $amount => $label)
            <form method="POST" action="/gifts/buy-coins">
                @csrf
                <input type="hidden" name="amount" value="{{ $amount }}">
                <button type="submit" class="w-full flex flex-col items-center gap-1 py-4 border-2 border-gray-200 dark:border-gray-600 hover:border-brand-400 rounded-xl transition-colors group">
                    <span class="text-2xl">🪙</span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand-500">{{ number_format($amount) }}</span>
                    <span class="text-xs text-gray-400">{{ $label }}</span>
                </button>
            </form>
            @endforeach
        </div>
        <p class="text-xs text-gray-400 mt-3 text-center">Demo mode — coins are awarded instantly for free</p>
    </div>

    {{-- Gift types reference --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5">
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Gift Types</h2>
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-3 text-center">
            @foreach(\App\Models\VirtualGift::$types as $key => $type)
            <div class="py-3 rounded-xl bg-gray-50 dark:bg-gray-700">
                <div class="text-3xl mb-1">{{ $type['emoji'] }}</div>
                <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $type['label'] }}</div>
                <div class="text-xs text-brand-500 font-bold">{{ $type['coins'] }} 🪙</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Gift history --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5">
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Gift History</h2>

        @if($sent->count() || $received->count())
        <div class="space-y-2" x-data="{tab:'received'}">
            <div class="flex gap-2 mb-4">
                <button @click="tab='received'" :class="tab==='received' ? 'bg-brand-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                        class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-colors">
                    Received ({{ $received->count() }})
                </button>
                <button @click="tab='sent'" :class="tab==='sent' ? 'bg-brand-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                        class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-colors">
                    Sent ({{ $sent->count() }})
                </button>
            </div>

            <div x-show="tab==='received'" class="space-y-2">
                @forelse($received as $gift)
                <div class="flex items-center gap-3 py-2">
                    <span class="text-2xl">{{ \App\Models\VirtualGift::$types[$gift->gift_type]['emoji'] ?? '🎁' }}</span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $gift->sender->name }}</p>
                        <p class="text-xs text-gray-400">{{ $gift->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-sm font-bold text-brand-500">+{{ $gift->coins_spent }} 🪙</span>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">No gifts received yet.</p>
                @endforelse
            </div>

            <div x-show="tab==='sent'" x-cloak class="space-y-2">
                @forelse($sent as $gift)
                <div class="flex items-center gap-3 py-2">
                    <span class="text-2xl">{{ \App\Models\VirtualGift::$types[$gift->gift_type]['emoji'] ?? '🎁' }}</span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">To {{ $gift->recipient->name }}</p>
                        <p class="text-xs text-gray-400">{{ $gift->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-sm font-bold text-red-500">-{{ $gift->coins_spent }} 🪙</span>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">No gifts sent yet.</p>
                @endforelse
            </div>
        </div>
        @else
        <p class="text-sm text-gray-400 text-center py-8">No gift activity yet. Send a gift to your favourite creator!</p>
        @endif
    </div>
</div>
@endsection
