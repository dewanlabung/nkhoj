@extends('layouts.app')
@section('title', 'Subscribe to Newsletter')

@section('content')
<div class="max-w-2xl mx-auto py-12">
    {{-- Success Message --}}
    @if(session('success'))
    <div class="mb-8 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-green-800 dark:text-green-200">Successfully subscribed!</p>
                <p class="text-sm text-green-700 dark:text-green-300 mt-1">Check your email for confirmation. You'll receive our latest stories and updates.</p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-brand-500 to-brand-600 dark:from-brand-600 dark:to-brand-700 px-8 py-12 text-center">
            <h1 class="text-4xl font-bold text-white mb-3">Stay Updated</h1>
            <p class="text-brand-100 text-lg max-w-md mx-auto">
                Get the latest नेपाली news, stories, and insights delivered to your inbox weekly.
            </p>
        </div>

        <div class="p-8">
            {{-- Newsletter Info Cards --}}
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white">Breaking News</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Get notified instantly when important stories break</p>
                </div>

                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white">Curated Selection</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Handpicked stories based on your interests</p>
                </div>

                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.172l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white">No Spam</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Unsubscribe anytime. We respect your privacy.</p>
                </div>
            </div>

            {{-- Subscription Form --}}
            <form action="/newsletter/subscribe" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Your Name (optional)
                    </label>
                    <input type="text" id="name" name="name" maxlength="100"
                        placeholder="Enter your name"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                        value="{{ old('name') }}">
                    @error('name')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required maxlength="150"
                        placeholder="you@example.com"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                        value="{{ old('email') }}">
                    @error('email')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Privacy Notice --}}
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-sm text-gray-600 dark:text-gray-300">
                    <p>By subscribing, you agree to our <a href="/privacy-policy" class="text-brand-500 hover:text-brand-600 font-medium">Privacy Policy</a> and <a href="/terms-of-service" class="text-brand-500 hover:text-brand-600 font-medium">Terms of Service</a>. We'll send you updates 2-3 times per week.</p>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="w-full px-6 py-4 bg-brand-500 hover:bg-brand-600 text-white font-bold text-lg rounded-lg transition-colors shadow-md hover:shadow-lg">
                    Subscribe Now
                </button>

                <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                    Already subscribed? <a href="/newsletter/manage" class="text-brand-500 hover:text-brand-600 font-medium">Manage preferences</a>
                </p>
            </form>
        </div>
    </div>

    {{-- Recent Stories Preview --}}
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">What You'll Get</h2>
        <div class="grid md:grid-cols-2 gap-6">
            @php
            $recentPosts = \App\Models\Blog\Post::published()
                ->latest('published_at')
                ->limit(2)
                ->get();
            @endphp

            @forelse($recentPosts as $post)
            <a href="/posts/{{ $post->slug }}" class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-shadow">
                <div class="relative h-40 bg-gray-100 dark:bg-gray-700 overflow-hidden">
                    @if($post->thumbnail_url)
                    <img src="{{ $post->thumbnail_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                    <div class="w-full h-full bg-gradient-to-br from-brand-400 to-brand-600"></div>
                    @endif
                </div>
                <div class="p-4">
                    <p class="text-xs text-brand-600 dark:text-brand-400 font-semibold uppercase">{{ $post->category->name_en }}</p>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-2 line-clamp-2 group-hover:text-brand-500 transition-colors">{{ $post->title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2">{{ strip_tags($post->excerpt) ?? substr(strip_tags($post->content), 0, 100) }}</p>
                    <div class="flex items-center gap-2 mt-3 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $post->published_at->format('M d, Y') }}</span>
                        <span>·</span>
                        <span>{{ $post->author->name }}</span>
                    </div>
                </div>
            </a>
            @empty
            <p class="text-gray-500 dark:text-gray-400">Check back soon for featured stories</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
