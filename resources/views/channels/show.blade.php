@extends('layouts.app')
@section('title', $channel->name . ' — नखोज')
@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Channel header --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 mb-5">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 font-black text-2xl flex-shrink-0">
                {{ strtoupper(substr($channel->name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $channel->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">by {{ $channel->owner->name }} · {{ number_format($channel->subscriber_count) }} subscribers</p>
                @if($channel->description)
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $channel->description }}</p>
                @endif
            </div>
            @auth
            <form method="POST" action="/channels/{{ $channel->slug }}/subscribe">
                @csrf
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold rounded-xl transition-colors {{ $subscribed ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-red-50 hover:text-red-600' : 'bg-brand-500 hover:bg-brand-600 text-white' }}">
                    {{ $subscribed ? 'Unsubscribe' : 'Subscribe' }}
                </button>
            </form>
            @endauth
        </div>
    </div>

    {{-- Broadcast form (owner only) --}}
    @if(auth()->id() === $channel->user_id)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-brand-200 dark:border-brand-800 p-4 mb-5">
        <h2 class="text-sm font-bold text-brand-600 dark:text-brand-400 mb-3 flex items-center gap-2">📤 Broadcast a message</h2>
        <form method="POST" action="/channels/{{ $channel->slug }}/broadcast" class="space-y-3">
            @csrf
            <textarea name="body" required maxlength="5000" rows="3"
                      class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400 resize-none"
                      placeholder="Write your message to subscribers..."></textarea>
            @if(session('success'))
            <p class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</p>
            @endif
            <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl transition-colors">
                Send to {{ number_format($channel->subscriber_count) }} subscribers
            </button>
        </form>
    </div>
    @endif

    {{-- Messages feed --}}
    @if($messages->count())
    <div class="space-y-3">
        @foreach($messages as $msg)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4"
             x-data="{reacted: false, count: {{ $msg->reactions_count }}}">
            <p class="text-gray-800 dark:text-gray-200 text-sm whitespace-pre-line">{{ $msg->body }}</p>
            <div class="flex items-center justify-between mt-3">
                <span class="text-xs text-gray-400">{{ $msg->created_at->diffForHumans() }}</span>
                @auth
                <button @click="fetch('/channel-messages/{{ $msg->id }}/react', {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({emoji:'👍'})}).then(r=>r.json()).then(d=>{count=d.count;reacted=!reacted})"
                        :class="reacted ? 'text-brand-500' : 'text-gray-400'"
                        class="flex items-center gap-1 text-sm hover:text-brand-500 transition-colors">
                    👍 <span x-text="count"></span>
                </button>
                @else
                <span class="text-xs text-gray-400">👍 {{ $msg->reactions_count }}</span>
                @endauth
            </div>
        </div>
        @endforeach
    </div>
    {{ $messages->links() }}
    @else
    <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
        <div class="text-3xl mb-2">📭</div>
        <p class="text-gray-400 text-sm">No messages yet.</p>
    </div>
    @endif
</div>
@endsection
