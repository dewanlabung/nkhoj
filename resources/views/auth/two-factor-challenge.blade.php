@extends('layouts.app')
@section('title', 'Two-Factor Authentication — nkhoj')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <span class="text-4xl font-black text-brand-600">nkhoj</span>
            <p class="text-gray-500 mt-2 font-nepali">दुई-चरण प्रमाणीकरण</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
            </div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white text-center mb-2">Authenticator Code</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">Please enter the 6-digit code from your authenticator app.</p>

            @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm rounded-lg px-4 py-3 mb-4">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="/two-factor-challenge">
                @csrf
                <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6" autofocus
                    class="w-full text-center text-2xl font-mono tracking-widest border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-4 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-400 mb-4"
                    placeholder="000000">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition-colors">
                    Verify
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
