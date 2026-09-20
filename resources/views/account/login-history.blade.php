@extends('layouts.account')
@section('title', 'Login History')

@section('main')
<div class="space-y-5">

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <div class="mb-5">
            <h1 class="font-bold text-gray-900 dark:text-white mb-0.5">Login History</h1>
            <p class="text-sm text-gray-400">Recent sign-in activity on your account. Only the last 50 events are shown.</p>
        </div>

        @if($histories->isEmpty())
        <div class="text-center py-12">
            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <p class="text-sm text-gray-400">No login history yet.</p>
        </div>
        @else

        {{-- Summary strip --}}
        <div class="flex gap-4 mb-5 flex-wrap">
            <div class="flex items-center gap-2 px-3 py-2 bg-green-50 dark:bg-green-900/20 rounded-xl">
                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                <span class="text-xs font-semibold text-green-700 dark:text-green-400">{{ $histories->where('success', true)->count() }} successful</span>
            </div>
            @if($histories->where('success', false)->count() > 0)
            <div class="flex items-center gap-2 px-3 py-2 bg-red-50 dark:bg-red-900/20 rounded-xl">
                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                <span class="text-xs font-semibold text-red-700 dark:text-red-400">{{ $histories->where('success', false)->count() }} failed</span>
            </div>
            @endif
        </div>

        <div class="space-y-2">
            @foreach($histories as $h)
            <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">

                {{-- Device icon --}}
                <div class="flex-shrink-0 w-10 h-10 rounded-xl {{ $h->success ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} flex items-center justify-center">
                    @if($h->device_type === 'mobile')
                        <svg class="w-5 h-5 {{ $h->success ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @elseif($h->device_type === 'tablet')
                        <svg class="w-5 h-5 {{ $h->success ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @else
                        <svg class="w-5 h-5 {{ $h->success ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $h->browser !== 'Unknown' ? $h->browser : 'Unknown browser' }}
                            @if($h->platform !== 'Unknown') · {{ $h->platform }} @endif
                        </span>
                        @if($h->provider && $h->provider !== 'email')
                        <span class="text-xs px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full capitalize">{{ $h->provider }}</span>
                        @endif
                        @if(!$h->success)
                        <span class="text-xs px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-full">Failed</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 mt-0.5 flex-wrap">
                        @if($h->ip_address)
                        <span class="text-xs text-gray-400 font-mono">{{ $h->ip_address }}</span>
                        @endif
                        @if($h->city || $h->country)
                        <span class="text-xs text-gray-400">
                            {{ implode(', ', array_filter([$h->city, $h->country])) }}
                        </span>
                        @endif
                        <span class="text-xs text-gray-400">{{ $h->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <div class="flex-shrink-0 text-xs text-gray-400 whitespace-nowrap hidden sm:block">
                    {{ $h->created_at->format('d M Y, H:i') }}
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Security tip --}}
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">See something unfamiliar?</p>
            <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">If you don't recognise a sign-in, <a href="/account/security" class="underline font-medium">change your password</a> and <a href="/account/sessions" class="underline font-medium">revoke active sessions</a> immediately.</p>
        </div>
    </div>

</div>
@endsection
