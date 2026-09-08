@extends('layouts.app')

@section('title', 'Create a Page')

@section('content')
<div class="min-h-screen bg-white">
    {{-- Desktop: two-column (like Facebook desktop) --}}
    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-3">Pages</h1>
            <p class="text-gray-500 text-lg">Connect with your community and grow your presence</p>
        </div>

        {{-- Cards row --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
            <div class="border border-gray-200 rounded-2xl p-8 hover:shadow-md transition text-center">
                <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Business or Brand</h3>
                <p class="text-gray-500 text-sm">Showcase your products and services and reach more customers</p>
            </div>
            <div class="border border-gray-200 rounded-2xl p-8 hover:shadow-md transition text-center">
                <div class="w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Community or Public Figure</h3>
                <p class="text-gray-500 text-sm">Connect and share with people in your community, team, or group</p>
            </div>
        </div>

        {{-- Info banner --}}
        <div class="bg-gray-900 rounded-2xl p-8 text-white mb-10">
            <h2 class="text-2xl font-bold mb-6">Create your Page</h2>
            <div class="space-y-5">
                <div class="flex gap-4 items-start">
                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <p class="text-gray-300 leading-relaxed">A Page is a space where people can publicly connect with your business, personal brand or organization.</p>
                </div>
                <div class="flex gap-4 items-start">
                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-300 leading-relaxed">You can showcase your articles, engage your audience, and grow your readership.</p>
                </div>
                <div class="flex gap-4 items-start">
                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-300 leading-relaxed">Thousands of people discover and connect with Pages every day on Dewanlabung.</p>
                </div>
            </div>
        </div>

        <div class="text-center">
            @auth
                <a href="/pages/create"
                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-lg font-semibold px-12 py-4 rounded-xl transition">
                    Get started
                </a>
            @else
                <a href="/login?redirect=/pages/create"
                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-lg font-semibold px-12 py-4 rounded-xl transition">
                    Get started
                </a>
            @endauth
            <p class="text-gray-400 text-sm mt-3">By creating a Page, you agree to our <a href="#" class="text-blue-500 hover:underline">Pages terms</a>.</p>
        </div>
    </div>
</div>
@endsection
