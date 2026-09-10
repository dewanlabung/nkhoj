@extends('layouts.app')
@section('title', 'Activity Log — ' . $page->name)
@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
        <a href="/pages/{{ $page->slug }}/dashboard" class="p-2 -ml-2 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1">
            <h1 class="font-bold text-gray-900 text-lg">Activity Log</h1>
            <p class="text-xs text-gray-400">All management actions on {{ $page->name }}</p>
        </div>
    </div>

    <div class="max-w-xl mx-auto px-4 py-6">
        @if($logs->count())
        <div class="space-y-2">
            @foreach($logs as $log)
            <div class="bg-white rounded-2xl px-4 py-3 shadow-sm flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                    @php $icons = [
                        'archive'=>'📦','unarchive'=>'📤','block_user'=>'🚫','unblock_user'=>'✅',
                        'create_story'=>'📸','update_settings'=>'⚙️','create_post'=>'📝',
                        'delete_post'=>'🗑️','invite_admin'=>'👥','remove_admin'=>'👤',
                        'default'=>'📋',
                    ]; @endphp
                    <span class="text-sm">{{ $icons[$log->action] ?? $icons['default'] }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800">{{ $log->description }}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <p class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</p>
                        @if($log->user)
                        <span class="text-gray-300">·</span>
                        <p class="text-xs text-gray-500">{{ $log->user->name }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
        @else
        <div class="text-center py-16">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-gray-500 font-medium">No activity yet</p>
        </div>
        @endif
    </div>
</div>
@endsection
