@extends('layouts.app')

@section('title', 'Moderation — ' . $page->name)

@section('content')
<div class="min-h-screen bg-gray-50">

    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
        <a href="/pages/{{ $page->slug }}/dashboard" class="p-2 -ml-2 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1">
            <h1 class="font-bold text-gray-900 text-lg">Moderation Queue</h1>
            <p class="text-xs text-gray-400">{{ $reports->total() }} total reports</p>
        </div>
    </div>

    <div class="max-w-xl mx-auto px-4 py-6 space-y-3">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        @forelse($reports as $report)
        @php
            $statusColors = ['pending'=>'amber','reviewed'=>'green','dismissed'=>'gray'];
            $sc = $statusColors[$report->status] ?? 'gray';
            $reasonLabels = ['spam'=>'Spam','inappropriate'=>'Inappropriate content','harassment'=>'Harassment','fake'=>'Fake / impersonation','other'=>'Other'];
            $typeName = class_basename($report->reportable_type ?? '');
        @endphp
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 flex items-center gap-3 border-b border-gray-100">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">{{ $typeName }}</span>
                        <span class="px-2 py-0.5 bg-{{ $sc }}-100 text-{{ $sc }}-700 text-xs font-semibold rounded-full capitalize">{{ $report->status }}</span>
                    </div>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $reasonLabels[$report->reason] ?? $report->reason }}</p>
                    @if($report->details)
                    <p class="text-xs text-gray-500 mt-0.5">{{ $report->details }}</p>
                    @endif
                </div>
                <div class="text-xs text-gray-400 flex-shrink-0 text-right">
                    <p>{{ $report->reporter?->name ?? 'Unknown' }}</p>
                    <p>{{ $report->created_at->diffForHumans() }}</p>
                </div>
            </div>

            {{-- Reportable preview --}}
            @if($report->reportable)
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 text-sm text-gray-700">
                @if($typeName === 'PagePost')
                    <p class="text-xs font-semibold text-gray-400 mb-1">Post content</p>
                    <p class="line-clamp-3">{{ $report->reportable->body ?? '[No text]' }}</p>
                @elseif($typeName === 'PageReview')
                    <p class="text-xs font-semibold text-gray-400 mb-1">Review ({{ $report->reportable->rating }}★)</p>
                    <p class="line-clamp-3">{{ $report->reportable->body ?? '[No text]' }}</p>
                @else
                    <p class="text-xs text-gray-400">Page reported</p>
                @endif
            </div>
            @endif

            @if($report->status === 'pending')
            <div class="px-4 py-3 flex gap-2">
                <form method="POST" action="/pages/{{ $page->slug }}/moderation/{{ $report->id }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="action" value="dismiss">
                    <button type="submit" class="w-full py-2 border border-gray-300 text-gray-600 text-sm font-semibold rounded-xl hover:bg-gray-50 transition">
                        Dismiss
                    </button>
                </form>
                @if($report->reportable && $typeName !== 'SocialPage')
                <form method="POST" action="/pages/{{ $page->slug }}/moderation/{{ $report->id }}"
                      onsubmit="return confirm('Delete this content? This cannot be undone.')" class="flex-1">
                    @csrf
                    <input type="hidden" name="action" value="delete_content">
                    <button type="submit" class="w-full py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition">
                        Delete Content
                    </button>
                </form>
                @endif
            </div>
            @else
            <div class="px-4 py-2.5 flex items-center gap-1.5 text-xs text-gray-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Reviewed by {{ $report->reviewer?->name ?? 'admin' }} · {{ $report->reviewed_at?->diffForHumans() }}
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-16 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="font-semibold">No reports yet</p>
            <p class="text-sm mt-1">Your page is clean!</p>
        </div>
        @endforelse

        {{ $reports->links() }}
    </div>
</div>
@endsection
