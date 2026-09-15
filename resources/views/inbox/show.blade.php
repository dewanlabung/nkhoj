@extends('layouts.app')
@section('title', 'Chat with ' . $other->name . ' — नखोज')
@section('content')
<div class="max-w-lg mx-auto flex flex-col" style="height: calc(100vh - 120px);"
     x-data="inboxChat({{ $conversation->id }})">

    {{-- Header --}}
    <div class="flex items-center gap-3 bg-white dark:bg-gray-800 rounded-t-2xl border border-gray-100 dark:border-gray-700 px-4 py-3 mb-0 flex-shrink-0">
        <a href="/inbox" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 mr-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="w-9 h-9 rounded-full bg-brand-400 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
            {{ strtoupper(substr($other->name, 0, 1)) }}
        </div>
        <div class="flex-1">
            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $other->name }}</p>
        </div>
        {{-- Gift button --}}
        <a href="#" @click.prevent="giftPanel = !giftPanel" class="text-brand-400 hover:text-brand-600 transition-colors" title="Send gift">
            🎁
        </a>
    </div>

    {{-- Gift panel --}}
    <div x-show="giftPanel" x-cloak class="bg-white dark:bg-gray-800 border-x border-gray-100 dark:border-gray-700 px-4 py-3 flex-shrink-0">
        <p class="text-xs font-bold text-gray-500 mb-2">Send a gift (balance: <span class="text-brand-500" x-text="coinBalance">{{ $wallet->balance }}</span> 🪙)</p>
        <div class="flex gap-2 flex-wrap">
            @foreach(\App\Models\VirtualGift::$types as $key => $type)
            <form method="POST" action="/gifts/send/{{ $other->id }}">
                @csrf
                <input type="hidden" name="gift_type" value="{{ $key }}">
                <button type="submit" class="flex flex-col items-center px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-xl hover:border-brand-400 transition-colors text-center">
                    <span class="text-xl">{{ $type['emoji'] }}</span>
                    <span class="text-xs text-gray-500">{{ $type['coins'] }}🪙</span>
                </button>
            </form>
            @endforeach
        </div>
    </div>

    {{-- Messages --}}
    <div class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 border-x border-gray-100 dark:border-gray-700 px-4 py-3 space-y-2" id="message-list">
        <template x-for="msg in messages" :key="msg.id">
            <div :class="msg.mine ? 'flex justify-end' : 'flex justify-start'">
                <div :class="[
                    'max-w-xs px-3 py-2 rounded-2xl text-sm',
                    msg.mine
                        ? 'bg-brand-500 text-white rounded-br-sm'
                        : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-100 dark:border-gray-700 rounded-bl-sm'
                ]">
                    <template x-if="msg.view_once && !msg.mine && !msg.viewed">
                        <span class="italic opacity-70">👁 Tap to view (view once)</span>
                    </template>
                    <template x-if="!(msg.view_once && !msg.mine && !msg.viewed)">
                        <span x-text="msg.body"></span>
                    </template>
                    <div class="text-xs opacity-50 mt-0.5 text-right" x-text="msg.time"></div>
                    <div x-show="msg.expires_label" class="text-xs opacity-60 flex items-center gap-1">
                        <span>⏱</span><span x-text="msg.expires_label"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Input --}}
    <div class="bg-white dark:bg-gray-800 rounded-b-2xl border border-gray-100 dark:border-gray-700 border-t-0 px-4 py-3 flex-shrink-0 space-y-2">
        {{-- Disappearing options --}}
        <div class="flex items-center gap-2 text-xs text-gray-500" x-show="showOptions" x-cloak>
            <select x-model="expiresIn" class="border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1 text-xs bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:outline-none">
                <option value="">No expiry</option>
                <option value="300">5 min</option>
                <option value="3600">1 hour</option>
                <option value="86400">24 hours</option>
                <option value="604800">7 days</option>
            </select>
            <label class="flex items-center gap-1 cursor-pointer">
                <input type="checkbox" x-model="viewOnce" class="rounded">
                <span>View once</span>
            </label>
        </div>
        <div class="flex gap-2">
            <button @click="showOptions = !showOptions" class="text-gray-400 hover:text-brand-500 transition-colors flex-shrink-0" title="Message options">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/></svg>
            </button>
            <input x-model="chatInput" @keydown.enter="sendMessage()" type="text" maxlength="1000"
                   placeholder="Type a message..."
                   class="flex-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-400">
            <button @click="sendMessage()" :disabled="!chatInput.trim()"
                    class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm rounded-xl disabled:opacity-40 transition-colors">
                ↩
            </button>
        </div>
    </div>
</div>

<script>
function inboxChat(convId) {
    const myId = {{ auth()->id() ?? 'null' }};
    return {
        messages: @json($messages->map(fn($m) => [
            'id' => $m->id,
            'body' => $m->body,
            'mine' => $m->sender_id === auth()->id(),
            'view_once' => (bool)$m->is_view_once,
            'viewed' => (bool)$m->viewed_at,
            'time' => $m->created_at->format('H:i'),
            'expires_label' => $m->expires_at ? 'Expires ' . $m->expires_at->diffForHumans() : null,
        ])),
        chatInput: '',
        expiresIn: '',
        viewOnce: false,
        showOptions: false,
        giftPanel: false,
        coinBalance: {{ $wallet->balance }},
        lastId: {{ $messages->last()?->id ?? 0 }},
        pollInterval: null,

        init() {
            this.pollInterval = setInterval(() => this.poll(), 5000);
            this.$nextTick(() => this.scrollToBottom());
        },

        async poll() {
            try {
                const r = await fetch(`/inbox/${convId}/poll?since=${this.lastId}`);
                const data = await r.json();
                if (data.messages?.length) {
                    this.messages.push(...data.messages.map(m => ({...m, mine: m.sender_id === myId})));
                    this.lastId = data.messages.at(-1).id;
                    this.$nextTick(() => this.scrollToBottom());
                }
            } catch {}
        },

        async sendMessage() {
            const body = this.chatInput.trim();
            if (!body) return;
            this.chatInput = '';
            try {
                const payload = {body};
                if (this.expiresIn) payload.expires_in = parseInt(this.expiresIn);
                if (this.viewOnce) payload.is_view_once = true;
                await fetch(`/inbox/${convId}/send`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                    body: JSON.stringify(payload)
                });
                this.expiresIn = '';
                this.viewOnce = false;
                this.showOptions = false;
                await this.poll();
            } catch {}
        },

        scrollToBottom() {
            const el = document.getElementById('message-list');
            if (el) el.scrollTop = el.scrollHeight;
        }
    };
}
</script>
@endsection
