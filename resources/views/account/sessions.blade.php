@extends('layouts.account')
@section('title', 'Active Sessions')

@section('main')
<div class="space-y-5">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="font-bold text-gray-900 dark:text-white mb-0.5">Active Sessions</h1>
                <p class="text-sm text-gray-400">Devices and browsers currently signed into your account.</p>
            </div>
            @if($sessions->where('session_id', '!=', $currentSid)->count() > 0)
            <form method="POST" action="/account/sessions">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Sign out all other sessions?')"
                    class="text-sm text-red-600 hover:text-red-700 font-medium">Revoke all others</button>
            </form>
            @endif
        </div>

        @if(session('success'))<div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-xl text-sm">{{ session('success') }}</div>@endif

        @if($sessions->isEmpty())
        <p class="text-sm text-gray-400 text-center py-6">No session records found. Sessions are tracked on next login after running <code class="text-xs bg-gray-100 dark:bg-gray-700 px-1 rounded">php artisan migrate</code>.</p>
        @else
        <div class="space-y-3">
            @foreach($sessions as $session)
            @php $isCurrent = $session->session_id === $currentSid; @endphp
            <div class="flex items-center gap-4 p-4 rounded-xl {{ $isCurrent ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                {{-- Device icon --}}
                <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-100 dark:border-gray-600 flex items-center justify-center flex-shrink-0">
                    @if($session->device_type === 'mobile')
                    <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @elseif($session->device_type === 'tablet')
                    <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @else
                    <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white capitalize">{{ $session->device_type ?? 'Unknown' }}</p>
                        @if($isCurrent)
                        <span class="px-2 py-0.5 bg-green-100 dark:bg-green-800/50 text-green-700 dark:text-green-300 text-xs font-semibold rounded-full">Current</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $session->ip }} · {{ $session->last_active_at?->diffForHumans() ?? 'Recently' }}</p>
                    @if($session->user_agent)
                    <p class="text-xs text-gray-300 dark:text-gray-600 truncate mt-0.5">{{ Str::limit($session->user_agent, 80) }}</p>
                    @endif
                </div>
                <form method="POST" action="/account/sessions/{{ $session->id }}" class="flex-shrink-0">
                    @csrf @method('DELETE')
                    <button type="submit" onclick="return confirm('{{ $isCurrent ? 'This will sign you out.' : 'Revoke this session?' }}')"
                        class="text-xs {{ $isCurrent ? 'text-orange-500 hover:text-orange-700' : 'text-red-500 hover:text-red-700' }} font-medium">
                        {{ $isCurrent ? 'Sign out' : 'Revoke' }}
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
