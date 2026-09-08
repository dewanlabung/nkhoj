@extends('layouts.app')

@section('title', 'Pages')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pages</h1>
                <p class="text-gray-500 text-sm">Discover pages and connect with creators</p>
            </div>
            <a href="{{ auth()->check() ? '/pages/create' : '/pages/start' }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition">
                + Create Page
            </a>
        </div>

        {{-- My Pages (if logged in and has pages) --}}
        @auth
            @if($myPages->count())
                <div class="mb-8">
                    <h2 class="font-bold text-gray-900 mb-3">Your Pages</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($myPages as $myPage)
                            <a href="/pages/{{ $myPage->slug }}"
                               class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group">
                                {{-- Mini cover --}}
                                <div class="h-16 {{ $myPage->cover_url ? '' : 'bg-gradient-to-br from-blue-400 to-blue-600' }}">
                                    @if($myPage->cover_url)
                                        <img src="{{ $myPage->cover_url }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="px-4 pb-4 -mt-6">
                                    <div class="w-12 h-12 rounded-xl border-2 border-white overflow-hidden bg-gray-200 mb-2 shadow">
                                        <img src="{{ $myPage->avatar }}" class="w-full h-full object-cover">
                                    </div>
                                    <p class="font-bold text-gray-900 text-sm group-hover:text-blue-600 transition line-clamp-1">{{ $myPage->name }}</p>
                                    <p class="text-gray-500 text-xs">{{ number_format($myPage->followers_count) }} followers</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endauth

        {{-- Suggested Pages --}}
        <div>
            <h2 class="font-bold text-gray-900 mb-3">
                @auth Suggested for you @else Discover Pages @endauth
            </h2>

            @if($pages->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($pages as $page)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                            {{-- Cover --}}
                            <a href="/pages/{{ $page->slug }}">
                                <div class="h-24 {{ $page->cover_url ? '' : 'bg-gradient-to-br from-gray-300 to-gray-400' }}">
                                    @if($page->cover_url)
                                        <img src="{{ $page->cover_url }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                            </a>
                            <div class="px-4 pb-4 -mt-8">
                                <a href="/pages/{{ $page->slug }}">
                                    <div class="w-14 h-14 rounded-full border-3 border-white overflow-hidden bg-gray-200 mb-2 shadow">
                                        <img src="{{ $page->avatar }}" class="w-full h-full object-cover">
                                    </div>
                                    <p class="font-bold text-gray-900 text-sm hover:text-blue-600 transition line-clamp-1">
                                        {{ $page->name }}
                                        @if($page->is_verified)
                                            <svg class="w-3.5 h-3.5 text-blue-500 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </p>
                                    @if($page->first_category)
                                        <p class="text-gray-500 text-xs">{{ $page->first_category }}</p>
                                    @endif
                                    <p class="text-gray-400 text-xs">{{ number_format($page->followers_count) }} {{ Str::plural('follower', $page->followers_count) }}</p>
                                </a>
                                @auth
                                    <form method="POST" action="/pages/{{ $page->slug }}/follow" class="mt-3">
                                        @csrf
                                        <button type="submit"
                                                class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-sm py-2 rounded-xl transition">
                                            Follow
                                        </button>
                                    </form>
                                @else
                                    <a href="/login" class="mt-3 block w-full text-center bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-sm py-2 rounded-xl transition">
                                        Follow
                                    </a>
                                @endauth
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">{{ $pages->links() }}</div>
            @else
                <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                    <p class="text-gray-500 text-lg font-medium mb-2">No pages yet</p>
                    <p class="text-gray-400 text-sm mb-6">Be the first to create a page on Dewanlabung!</p>
                    <a href="{{ auth()->check() ? '/pages/create' : '/pages/start' }}"
                       class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-xl transition">
                        Create the first page
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
