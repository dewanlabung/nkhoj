@extends('layouts.account')
@section('title', 'Sessions')

@section('main')
<div class="space-y-5">

    {{-- Active Sessions --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h1 class="font-bold text-gray-900 dark:text-white">Sessions</h1>
                <p class="text-sm text-gray-400 mt-0.5 max-w-lg">If necessary, you may sign out of other browser sessions across all your devices. If you feel your account has been compromised, update your password immediately.</p>
            </div>
            @if($sessions->where('session_id', '!=', $currentSid)->count() > 0)
            <form method="POST" action="/account/sessions" class="flex-shrink-0 ml-4">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Sign out all other sessions?')"
                    class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 font-medium whitespace-nowrap">
                    Revoke all others
                </button>
            </form>
            @endif
        </div>

        @if(session('success'))<div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-xl text-sm">{{ session('success') }}</div>@endif

        @if($sessions->isEmpty())
        <p class="text-sm text-gray-400 py-4">No active session records found.</p>
        @else
        <div class="space-y-2">
            @foreach($sessions as $session)
            @php $isCurrent = $session->session_id === $currentSid; @endphp
            <div class="flex items-center gap-4 p-4 rounded-xl {{ $isCurrent ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700' : 'bg-gray-50 dark:bg-gray-700/40' }}">
                {{-- Device icon --}}
                <div class="w-11 h-11 rounded-xl {{ $isCurrent ? 'bg-green-100 dark:bg-green-800/40' : 'bg-white dark:bg-gray-700' }} border border-gray-100 dark:border-gray-600 flex items-center justify-center flex-shrink-0">
                    @if($session->device_type === 'mobile')
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @elseif($session->device_type === 'tablet')
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @else
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $session->parsedPlatform() }} · {{ $session->parsedBrowser() }}
                        </p>
                        @if($isCurrent)
                        <span class="px-2 py-0.5 bg-green-100 dark:bg-green-800/50 text-green-700 dark:text-green-300 text-xs font-semibold rounded-full">This device</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $session->ip }}
                        @if($session->city || $session->country)
                        · {{ implode(', ', array_filter([$session->city, $session->country])) }}
                        @endif
                        @if(!$isCurrent)
                        · {{ $session->last_active_at?->diffForHumans() ?? 'Recently' }}
                        @endif
                    </p>
                </div>

                <form method="POST" action="/account/sessions/{{ $session->id }}" class="flex-shrink-0">
                    @csrf @method('DELETE')
                    <button type="submit" onclick="return confirm('{{ $isCurrent ? 'This will sign you out.' : 'Revoke this session?' }}')"
                        class="text-xs px-3 py-1.5 rounded-lg border {{ $isCurrent ? 'border-orange-200 dark:border-orange-700 text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20' : 'border-red-200 dark:border-red-700 text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20' }} transition-colors font-medium">
                        {{ $isCurrent ? 'Sign out' : 'Revoke' }}
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Sign-in History --}}
    @if($loginHistories->count())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-bold text-gray-900 dark:text-white">Sign-in History</h2>
                <p class="text-sm text-gray-400 mt-0.5">Recent sign-in activity on your account.</p>
            </div>
            <div class="flex items-center gap-2">
                @php $failed = $loginHistories->where('success', false)->count(); @endphp
                @if($failed > 0)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-xs font-semibold rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>{{ $failed }} failed
                </span>
                @endif
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 text-xs font-semibold rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>{{ $loginHistories->where('success', true)->count() }} successful
                </span>
            </div>
        </div>

        <div class="space-y-px">
            @foreach($loginHistories as $h)
            <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 dark:border-gray-700/60 last:border-0">
                <div class="w-8 h-8 rounded-lg {{ $h->success ? 'bg-gray-100 dark:bg-gray-700' : 'bg-red-50 dark:bg-red-900/20' }} flex items-center justify-center flex-shrink-0 text-sm">
                    @if($h->device_type === 'mobile') 📱 @elseif($h->device_type === 'tablet') 📟 @else 💻 @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800 dark:text-gray-200 truncate">
                        {{ $h->platform !== 'Unknown' ? $h->platform . ' · ' : '' }}{{ $h->browser !== 'Unknown' ? $h->browser : 'Browser' }}
                        @if($h->provider && $h->provider !== 'email')
                        <span class="text-xs text-blue-500 ml-1">via {{ ucfirst($h->provider) }}</span>
                        @endif
                    </p>
                    <p class="text-xs text-gray-400 truncate">
                        {{ $h->ip_address }}
                        @if($h->city || $h->country) · {{ implode(', ', array_filter([$h->city, $h->country])) }} @endif
                    </p>
                </div>
                <div class="text-right flex-shrink-0 ml-2">
                    @if(!$h->success)
                    <span class="block text-xs font-semibold text-red-500">Failed</span>
                    @endif
                    <span class="block text-xs text-gray-400">{{ $h->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Security tip --}}
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">See something unfamiliar?</p>
            <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">If you don't recognise a sign-in, <a href="/account/security" class="underline font-medium">change your password</a> and revoke any unknown sessions above.</p>
        </div>
    </div>

</div>
@endsection
