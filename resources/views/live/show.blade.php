@extends('layouts.app')
@section('title', $liveStream->title . ' — नखोज Live')
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4" x-data="liveChat({{ $liveStream->id }}, {{ $liveStream->isLive() ? 'true' : 'false' }})">

    {{-- Video embed --}}
    <div class="lg:col-span-2">
        <div class="bg-black rounded-2xl overflow-hidden aspect-video flex items-center justify-center">
            @if($liveStream->getEmbedHtml())
            <iframe src="{{ $liveStream->getEmbedHtml() }}" class="w-full h-full" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            @else
            <div class="text-center text-white">
                <div class="text-5xl mb-3">🔴</div>
                <p class="font-semibold">{{ $liveStream->title }}</p>
                <p class="text-sm text-gray-400 mt-1">No embed URL — streamer is live elsewhere</p>
            </div>
            @endif
        </div>

        <div class="mt-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        @if($liveStream->isLive())
                        <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span> LIVE
                        </span>
                        @else
                        <span class="bg-gray-200 text-gray-600 text-xs font-bold px-2 py-0.5 rounded-full">ENDED</span>
                        @endif
                        <span class="text-xs text-gray-400" x-text="viewers + ' viewers'"></span>
                    </div>
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ $liveStream->title }}</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ $liveStream->user->name }} · {{ $liveStream->started_at->diffForHumans() }}</p>
                    @if($liveStream->description)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $liveStream->description }}</p>
                    @endif
                </div>
                @if(auth()->id() === $liveStream->user_id && $liveStream->isLive())
                <button @click="endStream()" class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-semibold rounded-xl transition-colors">
                    End Stream
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Live chat --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 flex flex-col" style="height: 520px;">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
            <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
            <span class="text-sm font-bold text-gray-900 dark:text-white">Live Chat</span>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-2" id="chat-messages">
            <template x-for="msg in chatMessages" :key="msg.id">
                <div class="flex gap-2">
                    <div class="w-6 h-6 rounded-full bg-brand-400 flex items-center justify-center text-white text-xs font-bold flex-shrink-0" x-text="msg.name[0]"></div>
                    <div>
                        <span class="text-xs font-semibold text-brand-600 dark:text-brand-400" x-text="msg.name"></span>
                        <span class="text-xs text-gray-700 dark:text-gray-300 ml-1" x-text="msg.body"></span>
                    </div>
                </div>
            </template>
        </div>

        <div class="p-3 border-t border-gray-100 dark:border-gray-700">
            <div class="flex gap-2">
                <input x-model="chatInput" @keydown.enter="sendChat()" type="text" maxlength="500"
                    placeholder="{{ auth()->check() ? 'Say something...' : 'Login to chat' }}"
                    {{ auth()->check() ? '' : 'disabled' }}
                    class="flex-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-400 disabled:opacity-50">
                <button @click="sendChat()" :disabled="!chatInput.trim()" {{ auth()->check() ? '' : 'disabled' }}
                    class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-sm rounded-lg disabled:opacity-40 transition-colors">
                    ↩
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function liveChat(streamId, isLive) {
    return {
        chatMessages: @json($messages->map(fn($m) => ['id' => $m->id, 'name' => $m->displayName(), 'body' => $m->body])),
        chatInput: '',
        viewers: {{ $liveStream->viewer_count }},
        isLive,
        lastId: {{ $messages->last()?->id ?? 0 }},
        pollInterval: null,

        init() {
            if (this.isLive) {
                this.pollInterval = setInterval(() => this.poll(), 3000);
            }
            this.$nextTick(() => this.scrollToBottom());
        },

        async poll() {
            try {
                const r = await fetch(`/live/${streamId}/poll?since=${this.lastId}`);
                const data = await r.json();
                if (data.messages.length) {
                    this.chatMessages.push(...data.messages);
                    this.lastId = data.messages.at(-1).id;
                    this.$nextTick(() => this.scrollToBottom());
                }
                this.viewers = data.viewers;
                this.isLive  = data.is_live;
                if (!this.isLive && this.pollInterval) clearInterval(this.pollInterval);
            } catch {}
        },

        async sendChat() {
            const body = this.chatInput.trim();
            if (!body) return;
            this.chatInput = '';
            try {
                await fetch(`/live/${streamId}/chat`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                    body: JSON.stringify({body})
                });
            } catch {}
        },

        async endStream() {
            if (!confirm('End your live stream?')) return;
            await fetch(`/live/${streamId}/end`, {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}});
            window.location = '/live';
        },

        scrollToBottom() {
            const el = document.getElementById('chat-messages');
            if (el) el.scrollTop = el.scrollHeight;
        }
    };
}
</script>
@endsection
