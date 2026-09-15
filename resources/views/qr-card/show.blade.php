@extends('layouts.app')
@section('title', $user->name . ' — QR Card')
@section('content')
<div class="max-w-sm mx-auto">
    {{-- Card --}}
    <div id="qr-card" class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
        {{-- Top band --}}
        <div class="bg-gradient-to-br from-brand-500 to-brand-700 px-6 pt-8 pb-14 text-white text-center">
            <div class="w-20 h-20 rounded-full bg-white/20 flex items-center justify-center text-4xl font-black mx-auto mb-3">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h1 class="text-xl font-black">{{ $user->name }}</h1>
            <p class="text-white/70 text-sm mt-0.5">@{{ $user->username }}</p>
        </div>

        {{-- QR code --}}
        <div class="relative -mt-10 flex justify-center">
            <div class="bg-white rounded-2xl shadow-lg p-3 border border-gray-100">
                {!! $qrSvg !!}
            </div>
        </div>

        <div class="px-6 py-5 text-center">
            <p class="text-xs text-gray-400 mb-1">Scan to visit profile</p>
            <p class="text-xs font-mono text-gray-500 break-all">{{ $profileUrl }}</p>
        </div>

        <div class="px-6 pb-6 text-center">
            <p class="text-xs text-gray-300">नखोज · nkhoj.com</p>
        </div>
    </div>

    {{-- Actions --}}
    <div class="mt-5 flex gap-3 justify-center">
        <a href="/profile/{{ $user->username }}" class="px-5 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            ← Back to Profile
        </a>
        <button onclick="window.print()" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl transition-colors">
            🖨 Print / Save
        </button>
    </div>

    <p class="text-center text-xs text-gray-400 mt-3">Tip: use your browser's "Save as PDF" option to save the QR card.</p>
</div>

<style>
@media print {
    nav, header, footer, .no-print { display: none !important; }
    #qr-card { box-shadow: none; border: 1px solid #e5e7eb; margin: 0 auto; }
}
</style>
@endsection
