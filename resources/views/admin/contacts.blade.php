@extends('layouts.admin')
@section('title', 'Contact Messages')

@section('content')
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
        <div>
            <h3 class="font-bold text-gray-900">Contact Messages</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ $contacts->total() }} messages total</p>
        </div>
        @if($unread > 0)
        <span class="text-xs font-bold bg-red-50 text-red-600 px-3 py-1 rounded-full">{{ $unread }} unread</span>
        @endif
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($contacts as $msg)
        <div class="px-5 py-4 hover:bg-gray-50 transition-colors {{ is_null($msg->read_at) ? 'bg-blue-50/30' : '' }}" x-data="{ open: false }">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr($msg->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-sm text-gray-900">{{ $msg->name }}</span>
                            @if(is_null($msg->read_at))
                            <span class="text-[10px] font-semibold bg-blue-500 text-white px-2 py-0.5 rounded-full">NEW</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-gray-500">{{ $msg->email }}</p>
                        <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $msg->subject }}</p>
                        <p class="text-sm text-gray-500 line-clamp-2 mt-0.5" x-show="!open">{{ $msg->body }}</p>
                        <p class="text-sm text-gray-700 mt-1 whitespace-pre-line" x-show="open">{{ $msg->body }}</p>
                        <button @click="open = !open; @if(is_null($msg->read_at)) fetch('/admin/contacts/{{ $msg->id }}/read',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}) @endif"
                            class="text-xs text-brand-600 hover:underline mt-1" x-text="open ? 'Show less' : 'Read full message'"></button>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="mailto:{{ $msg->email }}" class="text-xs px-3 py-1.5 bg-brand-50 text-brand-600 rounded-lg hover:bg-brand-100 font-medium">Reply</a>
                    <form method="POST" action="/admin/contacts/{{ $msg->id }}" onsubmit="return confirm('Delete this message?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-400 hover:text-red-600">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-16 text-gray-400">
            <p class="text-4xl mb-3">✉️</p>
            <p>No contact messages yet.</p>
        </div>
        @endforelse
    </div>
    <div class="px-5 py-3 border-t border-gray-50">{{ $contacts->links() }}</div>
</div>
@endsection
