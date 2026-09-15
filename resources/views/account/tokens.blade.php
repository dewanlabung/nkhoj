@extends('layouts.account')
@section('title', 'API Tokens')

@section('main')
<div class="space-y-5">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h1 class="font-bold text-gray-900 dark:text-white mb-1">API Tokens</h1>
        <p class="text-sm text-gray-400 mb-6">Personal access tokens let you or your app authenticate with the Nkhoj API. Treat them like passwords — never share them publicly.</p>

        @if(session('success'))<div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-xl text-sm">{{ session('success') }}</div>@endif

        @if(session('new_token'))
        <div class="mb-5 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-xl p-4">
            <p class="text-xs font-bold text-yellow-700 dark:text-yellow-300 mb-2">Copy your new token now — it will not be shown again.</p>
            <div class="flex items-center gap-2">
                <code class="flex-1 text-xs font-mono bg-white dark:bg-gray-800 border border-yellow-200 dark:border-yellow-700 rounded-lg px-3 py-2 break-all text-gray-800 dark:text-gray-200 select-all">{{ session('new_token') }}</code>
                <button onclick="navigator.clipboard.writeText('{{ session('new_token') }}')" class="px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-semibold rounded-lg">Copy</button>
            </div>
        </div>
        @endif

        {{-- Create token --}}
        <form method="POST" action="/account/tokens" class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 mb-6 space-y-3">
            @csrf
            <div class="flex gap-3 flex-wrap">
                <input type="text" name="name" placeholder="Token name (e.g. iOS App, My Script)" required maxlength="80"
                    class="flex-1 min-w-0 text-sm border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400">
                <div class="flex gap-2 flex-wrap text-sm">
                    @foreach(['read','write','notifications'] as $scope)
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="scopes[]" value="{{ $scope }}" {{ $scope === 'read' ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-brand-600 border-gray-300 focus:ring-brand-500">
                        <span class="text-gray-700 dark:text-gray-300 capitalize">{{ $scope }}</span>
                    </label>
                    @endforeach
                </div>
                <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg">Create Token</button>
            </div>
        </form>

        {{-- Token list --}}
        @if($tokens->isEmpty())
        <p class="text-sm text-gray-400 text-center py-6">No tokens yet.</p>
        @else
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($tokens as $token)
            <div class="py-3 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $token->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Scopes: <span class="font-mono">{{ implode(', ', $token->abilities) }}</span>
                        · Created {{ $token->created_at->diffForHumans() }}
                        @if($token->last_used_at) · Last used {{ $token->last_used_at->diffForHumans() }} @endif
                    </p>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <a href="/account/tokens/{{ $token->id }}/activity" class="text-xs text-brand-600 hover:underline">Activity</a>
                    <form method="POST" action="/account/tokens/{{ $token->id }}">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('Revoke this token?')" class="text-xs text-red-500 hover:text-red-700">Revoke</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- API quick reference --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-3">Quick Reference</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Use your token in the <code class="font-mono bg-gray-100 dark:bg-gray-700 px-1 rounded">Authorization</code> header:</p>
        <pre class="text-xs font-mono bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 overflow-x-auto text-gray-700 dark:text-gray-300">curl {{ url('/api/v1/auth/me') }} \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"</pre>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                <p class="font-semibold text-gray-700 dark:text-gray-300 mb-1">read</p>
                <p class="text-gray-400">Read your profile, posts, bookmarks</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                <p class="font-semibold text-gray-700 dark:text-gray-300 mb-1">write</p>
                <p class="text-gray-400">Create and update content</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                <p class="font-semibold text-gray-700 dark:text-gray-300 mb-1">notifications</p>
                <p class="text-gray-400">Register device push tokens</p>
            </div>
        </div>
    </div>
</div>
@endsection
