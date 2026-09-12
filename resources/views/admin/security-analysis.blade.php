@extends('layouts.admin')
@section('title', 'Security Analysis')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Security Analysis</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Failed login attempts, top attacker IPs, and IP ban management</p>
        </div>
        <a href="/admin/security" class="text-sm text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1">
            ← Back to Security Settings
        </a>
    </div>

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-xl text-sm">
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- Left: Threats + Top IPs --}}
        <div class="lg:col-span-3 space-y-5">

            {{-- Top Attacking IPs --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Top Attacking IPs (Last 7 Days)
                </h2>
                @if($topAttackerIps->isEmpty())
                    <p class="text-sm text-gray-400">No failed login attempts in the last 7 days.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-400 border-b border-gray-100 dark:border-gray-700">
                                <th class="pb-2 font-semibold">IP Address</th>
                                <th class="pb-2 font-semibold">Failed Attempts</th>
                                <th class="pb-2 font-semibold">Status</th>
                                <th class="pb-2 font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            @foreach($topAttackerIps as $row)
                            <tr>
                                <td class="py-2.5 font-mono text-gray-700 dark:text-gray-300">{{ $row->ip_address }}</td>
                                <td class="py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $row->attempts >= 10 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                                        {{ $row->attempts }}
                                    </span>
                                </td>
                                <td class="py-2.5">
                                    @if(in_array($row->ip_address, $bannedIps))
                                        <span class="text-xs text-red-600 dark:text-red-400 font-medium">Banned</span>
                                    @else
                                        <span class="text-xs text-gray-400">Active</span>
                                    @endif
                                </td>
                                <td class="py-2.5">
                                    @if(!in_array($row->ip_address, $bannedIps))
                                    <form method="POST" action="/admin/security/ip-ban" class="inline">
                                        @csrf
                                        <input type="hidden" name="ip" value="{{ $row->ip_address }}">
                                        <input type="hidden" name="reason" value="Auto-ban: repeated failed logins">
                                        <button type="submit" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 font-medium">Ban IP</button>
                                    </form>
                                    @else
                                    <form method="POST" action="/admin/security/ip-unban" class="inline">
                                        @csrf
                                        <input type="hidden" name="ip" value="{{ $row->ip_address }}">
                                        <button type="submit" class="text-xs text-green-600 hover:text-green-800 dark:text-green-400 font-medium">Unban</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Recent Failed Logins --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Recent Failed Logins (Last 7 Days)
                </h2>
                @if($recentThreats->isEmpty())
                    <p class="text-sm text-gray-400">No failed login attempts in the last 7 days.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-400 border-b border-gray-100 dark:border-gray-700">
                                <th class="pb-2 font-semibold">IP Address</th>
                                <th class="pb-2 font-semibold">Time</th>
                                <th class="pb-2 font-semibold">User Agent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            @foreach($recentThreats as $threat)
                            <tr>
                                <td class="py-2 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $threat->ip_address }}</td>
                                <td class="py-2 text-xs text-gray-500 whitespace-nowrap">{{ $threat->created_at->diffForHumans() }}</td>
                                <td class="py-2 text-xs text-gray-400 truncate max-w-xs" title="{{ $threat->user_agent }}">{{ Str::limit($threat->user_agent ?? '—', 60) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        {{-- Right: IP Ban Management --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Add Ban --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    Ban an IP Address
                </h2>
                <form method="POST" action="/admin/security/ip-ban" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">IP Address or CIDR Range</label>
                        <input type="text" name="ip" placeholder="e.g. 192.168.1.1 or 10.0.0.0/8"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500 font-mono">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Reason (optional)</label>
                        <input type="text" name="reason" placeholder="e.g. Repeated login attacks"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg px-4 py-2.5 transition-colors">
                        Ban IP
                    </button>
                </form>
            </div>

            {{-- Currently Banned --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4">
                    Banned IPs <span class="text-sm font-normal text-gray-400">({{ count($bannedIps) }})</span>
                </h2>
                @if(empty($bannedIps))
                    <p class="text-sm text-gray-400">No IPs are currently banned.</p>
                @else
                <div class="space-y-2">
                    @foreach($bannedIps as $ip)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700 last:border-0">
                        <span class="font-mono text-sm text-gray-700 dark:text-gray-300">{{ $ip }}</span>
                        <form method="POST" action="/admin/security/ip-unban" class="inline">
                            @csrf
                            <input type="hidden" name="ip" value="{{ $ip }}">
                            <button type="submit" class="text-xs text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">Remove</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
