@extends('layouts.app')
@section('title', 'Reels — नखोज')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">🎬 Reels</h1>
    @auth
    <a href="/reels/create" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl transition-colors">+ Upload Reel</a>
    @endauth
</div>

<div class="max-w-sm mx-auto space-y-0" id="reels-feed">
    @forelse($reels as $reel)
    <div class="reel-item relative bg-black rounded-2xl overflow-hidden mb-4" style="height: 80vh; max-height: 680px;"
         x-data="reelCard({{ $reel->id }}, {{ $reel->likes_count }}, {{ auth()->check() && $reel->isLikedBy(auth()->user()) ? 'true' : 'false' }})">

        <video src="{{ $reel->video_url }}" class="w-full h-full object-cover"
               playsinline loop preload="metadata"
               x-ref="video"
               @click="togglePlay()">
        </video>

        {{-- Play/pause overlay --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div x-show="paused" class="w-16 h-16 bg-black/50 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            </div>
        </div>

        {{-- Right side actions --}}
        <div class="absolute right-3 bottom-24 flex flex-col items-center gap-5">
            {{-- Like --}}
            <button @click="toggleLike()" class="flex flex-col items-center gap-1">
                <div class="w-10 h-10 rounded-full bg-black/40 flex items-center justify-center"
                     :class="liked ? 'text-red-500' : 'text-white'">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </div>
                <span class="text-white text-xs font-semibold" x-text="likes"></span>
            </button>
            {{-- Comments --}}
            <button @click="showComments = !showComments" class="flex flex-col items-center gap-1">
                <div class="w-10 h-10 rounded-full bg-black/40 flex items-center justify-center text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <span class="text-white text-xs font-semibold">{{ $reel->comments_count }}</span>
            </button>
        </div>

        {{-- Bottom info --}}
        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/80 to-transparent">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-brand-400 flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr($reel->user->name, 0, 1)) }}
                </div>
                <span class="text-white text-sm font-semibold">{{ $reel->user->name }}</span>
            </div>
            @if($reel->description)
            <p class="text-white text-sm opacity-90 line-clamp-2">{{ $reel->description }}</p>
            @endif
        </div>

        {{-- Comments panel --}}
        <div x-show="showComments" x-cloak class="absolute inset-x-0 bottom-0 bg-white dark:bg-gray-900 rounded-t-2xl p-4 max-h-96 overflow-y-auto">
            <h3 class="font-bold text-gray-900 dark:text-white mb-3">Comments</h3>
            <div class="space-y-2 mb-3" id="reel-comments-{{ $reel->id }}">
                <template x-for="c in comments" :key="c.id">
                    <div class="flex gap-2">
                        <div class="w-6 h-6 rounded-full bg-brand-300 flex items-center justify-center text-white text-xs font-bold flex-shrink-0" x-text="c.name[0]"></div>
                        <div>
                            <span class="text-xs font-semibold text-gray-900 dark:text-white" x-text="c.name"></span>
                            <span class="text-xs text-gray-700 dark:text-gray-300 ml-1" x-text="c.body"></span>
                        </div>
                    </div>
                </template>
            </div>
            @auth
            <div class="flex gap-2">
                <input x-model="commentInput" @keydown.enter="sendComment()" type="text" placeholder="Add a comment..." class="flex-1 px-3 py-1.5 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-brand-500">
                <button @click="sendComment()" class="px-3 py-1.5 bg-brand-500 text-white text-xs rounded-lg">Post</button>
            </div>
            @endauth
        </div>
    </div>

    <script>
    function reelCard(reelId, initialLikes, initialLiked) {
        return {
            liked: initialLiked,
            likes: initialLikes,
            paused: true,
            showComments: false,
            comments: [],
            commentInput: '',

            init() {
                this.fetchComments();
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(e => {
                        if (e.isIntersecting) { this.$refs.video.play(); this.paused = false; }
                        else                   { this.$refs.video.pause(); this.paused = true; }
                    });
                }, { threshold: 0.7 });
                observer.observe(this.$el);
            },

            togglePlay() {
                if (this.$refs.video.paused) { this.$refs.video.play(); this.paused = false; }
                else                          { this.$refs.video.pause(); this.paused = true; }
            },

            async toggleLike() {
                @guest return; @endguest
                this.liked = !this.liked;
                this.likes += this.liked ? 1 : -1;
                await fetch(`/reels/${reelId}/like`, {method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}});
            },

            async fetchComments() {
                try {
                    const r = await fetch(`/reels/${reelId}/comments`);
                    this.comments = await r.json();
                } catch {}
            },

            async sendComment() {
                const body = this.commentInput.trim();
                if (!body) return;
                this.commentInput = '';
                try {
                    const r = await fetch(`/reels/${reelId}/comment`, {
                        method: 'POST',
                        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                        body: JSON.stringify({body})
                    });
                    const c = await r.json();
                    this.comments.unshift(c);
                } catch {}
            }
        };
    }
    </script>
    @empty
    <div class="text-center py-16">
        <div class="text-5xl mb-3">🎬</div>
        <p class="text-gray-500">No reels yet.</p>
        @auth
        <a href="/reels/create" class="mt-4 inline-block px-5 py-2 bg-brand-500 text-white text-sm font-semibold rounded-xl">Upload the first reel</a>
        @endauth
    </div>
    @endforelse
</div>
{{ $reels->links() }}
@endsection
