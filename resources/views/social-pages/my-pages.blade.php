@extends('layouts.app')

@section('title', 'My Pages')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-6">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Pages</h1>
                <p class="text-gray-500 text-sm">Manage your pages</p>
            </div>
            <a href="/pages/create"
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition">
                + Create Page
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($pages->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($pages as $page)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <a href="/pages/{{ $page->slug }}">
                            <div class="h-24 {{ $page->cover_url ? '' : 'bg-gradient-to-br from-blue-400 to-indigo-600' }}">
                                @if($page->cover_url)
                                    <img src="{{ $page->cover_url }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                        </a>
                        <div class="px-4 pb-4 -mt-6">
                            <div class="w-14 h-14 rounded-full border-2 border-white overflow-hidden bg-gray-200 mb-2 shadow">
                                <img src="{{ $page->avatar }}" class="w-full h-full object-cover">
                            </div>
                            <a href="/pages/{{ $page->slug }}" class="font-bold text-gray-900 hover:text-blue-600 transition text-sm line-clamp-1">
                                {{ $page->name }}
                                @if($page->is_verified)
                                    <svg class="w-3.5 h-3.5 text-blue-500 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </a>
                            @if($page->first_category)
                                <p class="text-gray-500 text-xs">{{ $page->first_category }}</p>
                            @endif
                            <p class="text-gray-400 text-xs mb-3">{{ number_format($page->followers_count) }} followers</p>
                            <div class="flex gap-2">
                                <a href="/pages/{{ $page->slug }}/dashboard"
                                   class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-2 rounded-lg transition">
                                    Dashboard
                                </a>
                                <a href="/pages/{{ $page->slug }}/settings"
                                   class="text-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3 rounded-lg transition">
                                    Settings
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-2xl p-16 text-center shadow-sm">
                <div class="w-20 h-20 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21l1.65-3.8a9 9 0 1113.7 0L21 21M12 13v-2m0-4h.01"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">You don't have any Pages yet</h3>
                <p class="text-gray-500 text-sm mb-6">Create a Page to showcase your brand, content, or organization.</p>
                <a href="/pages/create" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-xl transition">
                    Create your first Page
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
