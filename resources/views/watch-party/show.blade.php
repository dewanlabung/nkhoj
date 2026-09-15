@extends('layouts.app')
@section('title', $party->title . ' — Watch Party')
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4"
     x-data="watchParty({{ $party->id }}, {{ auth()->id() ?? 'null' }}, {{ $party->host_user_id }})">

    {{-- Video + controls --}}
    <div class="lg:col-span-2 space-y-3">
        <div class="bg-black rounded-2xl overflow-hidden aspect-video">
            @if($embedUrl = $party->getEmbedUrl())
            <iframe :src="embedSrc" class="w-full h-full" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            @else
            <div class="w-full h-full flex items-center justify-center text-white text-center">
                <div>
                    <div class="text-5xl mb-3">🎬</div>
                    <p>{{ $party->video_url }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4">
            <div class="flex items-start justify-between gap-3 flex-wrap">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        @if($party->status === 'active')
                        <span class="bg-green-500 text-white text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span> ACTIVE
                        </span>
                        @else
                        <span class="bg-gray-200 text-gray-600 text-xs font-bold px-2 py-0.5 rounded-full">ENDED</span>
                        @endif
                        <span class="text-xs text-gray-400" x-text="members + ' watching'"></span>
                    </div>
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ $party->title }}</h1>
                    <p class="text-sm text-gray-500 mt-1">Host: {{ $party->host->name }}</p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-3 py-1.5 rounded-lg font-mono font-bold">
                        Code: {{ $party->join_code }}
                    </span>
                    @if(auth()->id() !== $party->host_user_id && $party->status === 'active')
                    <form method="POST" action="/watch-party/{{ $party->id }}/join" x-show="!isMember">
                        @csrf
                        <button type="submit" class="px-4 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                            Join Party
                        </button>
                    </form>
                    @endif
                    @if(auth()->id() === $party->host_user_id && $party->status === 'active')
                    <button @click="endParty()" class="px-4 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-semibold rounded-lg transition-colors">
                        End Party
                    </button>
                    @endif
                </div>
            </div>

            @if(auth()->id() === $party->host_user_id && $party->status === 'active')
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center gap-3 flex-wrap">
                <span class="text-xs text-gray-500 font-semibold">Sync controls (host only):</span>
                <button @click="syncPlay()" class="px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-700 text-xs font-semibold rounded-lg transition-colors">▶ Play</button>
                <button @click="syncPause()" class="px-3 py-1.5 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-semibold rounded-lg transition-colors">⏸ Pause</button>
                <div class="flex items-center gap-2">
                    <input type="number" x-model="seekSeconds" min="0" placeholder="seconds"
                           class="w-24 px-2 py-1 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none">
                    <button @click="syncSeek()" class="px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-semibold rounded-lg transition-colors">⏩ Seek</button>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Party chat --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 flex flex-col" style="height: 520px;">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
            <span class="text-sm font-bold text-gray-900 dark:text-white">🍿 Party Chat</span>
            <span class="ml-auto text-xs text-gray-400" x-text="members + ' watching'"></span>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-2" id="party-messages">
            <template x-for="msg in messages" :key="msg.id">
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
            @auth
            <div class="flex gap-2">
                <input x-model="chatInput" @keydown.enter="sendChat()" type="text" maxlength="500"
                       placeholder="Say something..."
                       class="flex-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400">
                <button @click="sendChat()" :disabled="!chatInput.trim()"
                        class="px-3 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm rounded-lg disabled:opacity-40 transition-colors">
                    ↩
                </button>
            </div>
            @else
            <p class="text-center text-xs text-gray-400"><a href="/login" class="text-brand-500 font-semibold">Login</a> to chat</p>
            @endauth
        </div>
    </div>
</div>

<script>
function watchParty(partyId, myUserId, hostId) {
    return {
        messages: @json($messages->map(fn($m) => ['id' => $m->id, 'name' => $m->user->name, 'body' => $m->body])),
        chatInput: '',
        members: {{ $party->members->count() }},
        isMember: {{ $party->isMember(auth()->user() ?? new \App\Models\User) ? 'true' : 'false' }},
        embedSrc: '{{ $embedUrl ?? '' }}',
        seekSeconds: 0,
        lastId: {{ $messages->last()?->id ?? 0 }},
        pollInterval: null,

        init() {
            if ('{{ $party->status }}' === 'active') {
                this.pollInterval = setInterval(() => this.poll(), 4000);
            }
            this.$nextTick(() => this.scrollToBottom());
        },

        async poll() {
            try {
                const r = await fetch(`/watch-party/${partyId}/poll?since=${this.lastId}`);
                const data = await r.json();
                if (data.messages?.length) {
                    this.messages.push(...data.messages);
                    this.lastId = data.messages.at(-1).id;
                    this.$nextTick(() => this.scrollToBottom());
                }
                this.members = data.members ?? this.members;
                if (data.status === 'ended' && this.pollInterval) clearInterval(this.pollInterval);
            } catch {}
        },

        async sendChat() {
            const body = this.chatInput.trim();
            if (!body) return;
            this.chatInput = '';
            try {
                await fetch(`/watch-party/${partyId}/chat`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                    body: JSON.stringify({body})
                });
            } catch {}
        },

        async syncPlay() {
            await this.sendSync('playing');
        },
        async syncPause() {
            await this.sendSync('paused');
        },
        async syncSeek() {
            await this.sendSync(null, parseInt(this.seekSeconds) || 0);
        },
        async sendSync(status, seconds) {
            try {
                const payload = {};
                if (status) payload.status = status;
                if (seconds !== undefined) payload.playback_seconds = seconds;
                await fetch(`/watch-party/${partyId}/sync`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                    body: JSON.stringify(payload)
                });
            } catch {}
        },
        async endParty() {
            if (!confirm('End the watch party?')) return;
            await fetch(`/watch-party/${partyId}/end`, {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}});
            window.location = '/watch-party';
        },
        scrollToBottom() {
            const el = document.getElementById('party-messages');
            if (el) el.scrollTop = el.scrollHeight;
        }
    };
}
</script>
@endsection
