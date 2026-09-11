@extends('layouts.admin')
@section('title', 'Content Reports')

@section('content')
<div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <h2 class="text-xl font-bold text-gray-900">Content Reports</h2>
    <div class="flex gap-2 text-sm">
        @foreach(['pending' => 'Pending', 'reviewed' => 'Reviewed', 'dismissed' => 'Dismissed'] as $s => $label)
        <a href="?status={{ $s }}"
           class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ $status === $s ? 'bg-brand-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            {{ $label }}
            @if($counts[$s] > 0)
            <span class="ml-1 {{ $status === $s ? 'bg-white/20' : 'bg-gray-100' }} text-xs rounded-full px-1.5">{{ $counts[$s] }}</span>
            @endif
        </a>
        @endforeach
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="divide-y divide-gray-50">
        @forelse($reports as $report)
        <div class="p-5 hover:bg-gray-50 transition-colors">
            <div class="flex items-start gap-4">
                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-sm font-bold flex-shrink-0">
                    ⚑
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="font-semibold text-sm text-gray-800">{{ $report->reporter?->name ?? 'Unknown user' }}</span>
                        <span class="text-xs text-gray-400">{{ $report->created_at->diffForHumans() }}</span>
                        <span class="text-xs bg-gray-100 text-gray-600 rounded-full px-2 py-0.5">{{ $report->reportable_type === \App\Models\Post::class ? 'Post' : 'Comment' }} #{{ $report->reportable_id }}</span>
                        <span class="text-xs bg-red-50 text-red-600 rounded-full px-2 py-0.5 font-medium">{{ \App\Models\ContentReport::reasons()[$report->reason] ?? $report->reason }}</span>
                    </div>
                    @if($report->details)
                    <p class="text-sm text-gray-600 mb-2">{{ $report->details }}</p>
                    @endif
                    @if($report->admin_note)
                    <p class="text-xs text-gray-400 italic">Admin note: {{ $report->admin_note }}</p>
                    @endif
                </div>
                @if($report->status === 'pending')
                <div class="flex-shrink-0" x-data="{ open: false }">
                    <button @click="open = !open" class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 hover:bg-gray-50 text-gray-600">
                        Review
                    </button>
                    <div x-show="open" x-cloak class="mt-2 p-3 bg-gray-50 rounded-xl border border-gray-100 w-64">
                        <form method="POST" action="/admin/reports/{{ $report->id }}">
                            @csrf @method('PATCH')
                            <textarea name="admin_note" placeholder="Admin note (optional)" rows="2"
                                class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 mb-2 resize-none"></textarea>
                            <div class="flex gap-2">
                                <button name="action" value="reviewed"
                                    class="flex-1 text-xs bg-green-600 text-white rounded-lg py-1.5 font-medium hover:bg-green-700">
                                    Take action
                                </button>
                                <button name="action" value="dismissed"
                                    class="flex-1 text-xs bg-gray-200 text-gray-700 rounded-lg py-1.5 font-medium hover:bg-gray-300">
                                    Dismiss
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @else
                <span class="text-xs rounded-full px-2 py-1 {{ $report->status === 'reviewed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ ucfirst($report->status) }}
                </span>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-16 text-gray-400">
            <p class="text-4xl mb-3">✅</p>
            <p class="text-sm">No {{ $status }} reports.</p>
        </div>
        @endforelse
    </div>
    @if($reports->hasPages())
    <div class="px-5 py-4 border-t border-gray-50">
        {{ $reports->appends(['status' => $status])->links() }}
    </div>
    @endif
</div>
@endsection
