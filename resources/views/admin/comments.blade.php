@extends('layouts.admin')
@section('title', 'Comments')

@section('content')
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50">
        <h3 class="font-bold text-gray-900">All Comments <span class="text-gray-400 font-normal">({{ $comments->total() }})</span></h3>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($comments as $comment)
        <div class="px-5 py-4 flex gap-4 hover:bg-gray-50 transition-colors">
            <div class="w-9 h-9 rounded-full bg-brand-400 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr($comment->displayName(), 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span class="text-sm font-semibold text-gray-800">{{ $comment->displayName() }}</span>
                    <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                    @if($comment->parent_id)
                    <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">Reply</span>
                    @endif
                </div>
                <p class="text-sm text-gray-700">{{ $comment->body }}</p>
                @if($comment->post)
                <a href="/posts/{{ $comment->post->slug }}" class="text-xs text-brand-600 hover:underline mt-1 block line-clamp-1" target="_blank">
                    → {{ $comment->post->title }}
                </a>
                @endif
            </div>
            <form method="POST" action="/admin/comments/{{ $comment->id }}" onsubmit="return confirm('Delete this comment?')" class="flex-shrink-0">
                @csrf @method('DELETE')
                <button class="text-xs text-red-400 hover:text-red-600 whitespace-nowrap">Delete</button>
            </form>
        </div>
        @empty
        <div class="text-center py-16 text-gray-400">
            <p class="text-4xl mb-3">💬</p>
            <p>No comments yet.</p>
        </div>
        @endforelse
    </div>
    <div class="px-5 py-3 border-t border-gray-50">{{ $comments->links() }}</div>
</div>
@endsection
