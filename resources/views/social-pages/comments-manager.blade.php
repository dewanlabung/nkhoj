@extends('layouts.app')
@section('title', 'Comments Manager — ' . $page->name)
@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
        <a href="/pages/{{ $page->slug }}/dashboard" class="p-2 -ml-2 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1">
            <h1 class="font-bold text-gray-900 text-lg">Comments Manager</h1>
            <p class="text-xs text-gray-400">All comments across posts on {{ $page->name }}</p>
        </div>
    </div>

    <div class="max-w-xl mx-auto px-4 py-6">
        @if($comments->count())
        <div class="space-y-3">
            @foreach($comments as $comment)
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                {{-- Post context --}}
                <div class="mb-2">
                    <a href="/pages/{{ $page->slug }}#post-{{ $comment->page_post_id }}"
                        class="text-xs font-semibold text-blue-600 hover:underline line-clamp-1">
                        📝 {{ $comment->post->body ?? $comment->post->event_title ?? '(post #'.$comment->page_post_id.')' }}
                    </a>
                </div>
                {{-- Comment --}}
                <div class="flex items-start gap-3">
                    <img src="{{ $comment->author->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($comment->author->name ?? 'U').'&size=32&background=e5e7eb&color=6b7280' }}"
                        class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <div class="bg-gray-100 rounded-2xl px-3 py-2">
                            <p class="text-xs font-bold text-gray-900">{{ $comment->author->name ?? 'User' }}</p>
                            <p class="text-sm text-gray-800 mt-0.5">{{ $comment->body }}</p>
                        </div>
                        <div class="flex items-center gap-3 mt-1 ml-2">
                            <p class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
                            {{-- Delete --}}
                            <form action="/pages/{{ $page->slug }}/posts/{{ $comment->page_post_id }}/comments/{{ $comment->id }}"
                                method="POST" class="inline" onsubmit="return confirm('Delete comment?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline font-semibold">Delete</button>
                            </form>
                            {{-- Block author --}}
                            <form action="/pages/{{ $page->slug }}/block/{{ $comment->author?->id }}" method="POST" class="inline"
                                onsubmit="return confirm('Block this user from the page?')">
                                @csrf
                                <button type="submit" class="text-xs text-gray-500 hover:text-red-500 hover:underline">Block user</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $comments->links() }}</div>
        @else
        <div class="text-center py-16">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <p class="text-gray-500 font-medium">No comments yet</p>
        </div>
        @endif
    </div>
</div>
@endsection
