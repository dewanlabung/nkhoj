@extends('layouts.app')
@section('title', $question->title)

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/easymde/2.18.0/easymde.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/easymde/2.18.0/easymde.min.css">
@endpush

@section('content')
@php $sort = request('sort', 'voted'); @endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- ── Main Column ── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Closed notice --}}
        @if($question->status === 'closed')
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-2xl px-5 py-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">This question is closed</p>
                <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">Reason: {{ $question->closed_reason }}
                    @if($question->duplicate_of)
                        · <a href="/questions/{{ $question->duplicate_of }}" class="underline">See original</a>
                    @endif
                </p>
                @auth @if(in_array(auth()->user()->role, ['admin','editor']))
                <form method="POST" action="/questions/{{ $question->id }}/reopen" class="mt-2">
                    @csrf
                    <button class="text-xs px-3 py-1 bg-amber-600 text-white rounded-lg hover:bg-amber-700">Reopen</button>
                </form>
                @endif @endauth
            </div>
        </div>
        @endif

        {{-- Question Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden"
            x-data="{
                votes: {{ $question->votes }},
                userVote: {{ $userQuestionVote ?? 'null' }},
                following: {{ $isFollowing ? 'true' : 'false' }},
                bookmarked: {{ $isBookmarked ? 'true' : 'false' }},
                hasFlagged: {{ $hasFlagged ? 'true' : 'false' }},
                showFlag: false,
                showClose: false,
                loading: false,
                async vote(dir) {
                    if (this.loading) return;
                    this.loading = true;
                    const res = await fetch('/questions/{{ $question->id }}/vote', {
                        method: 'POST',
                        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                        body: JSON.stringify({vote: dir})
                    });
                    const d = await res.json();
                    this.votes = d.votes; this.userVote = d.userVote; this.loading = false;
                },
                async toggleFollow() {
                    const res = await fetch('/questions/{{ $question->id }}/follow', {method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}});
                    const d = await res.json(); this.following = d.following;
                },
                async toggleBookmark() {
                    const res = await fetch('/questions/{{ $question->id }}/bookmark', {method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}});
                    const d = await res.json(); this.bookmarked = d.bookmarked;
                },
                async submitFlag(form) {
                    const data = new FormData(form);
                    const res = await fetch('/questions/{{ $question->id }}/flag', {method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},body:data});
                    if (res.ok) { this.hasFlagged = true; this.showFlag = false; }
                }
            }">

            {{-- Breadcrumb --}}
            <div class="px-6 pt-5 flex items-center gap-2 text-xs text-gray-400 mb-3">
                <a href="/questions" class="hover:text-brand-600">Questions</a>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="truncate">{{ Str::limit($question->title, 55) }}</span>
            </div>

            <div class="flex gap-4 px-6 pb-6">

                {{-- Vote column --}}
                <div class="flex flex-col items-center gap-1 flex-shrink-0 pt-1">
                    <button @click="vote('up')" :disabled="loading"
                        :class="userVote === 1 ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-500 ring-1 ring-orange-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-orange-100 hover:text-orange-500'"
                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                    </button>
                    <span :class="userVote === 1 ? 'text-orange-500' : (userVote === -1 ? 'text-blue-500' : 'text-gray-700 dark:text-gray-300')"
                        class="text-lg font-bold" x-text="votes >= 1000 ? (votes/1000).toFixed(1)+'k' : votes"></span>
                    <button @click="vote('down')" :disabled="loading"
                        :class="userVote === -1 ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-500 ring-1 ring-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-blue-100 hover:text-blue-500'"
                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    {{-- Bookmark --}}
                    @auth
                    <button @click="toggleBookmark()" title="Bookmark" class="mt-1 w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                        :class="bookmarked ? 'text-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'text-gray-400 hover:text-amber-500 bg-gray-100 dark:bg-gray-700'">
                        <svg class="w-4 h-4" :fill="bookmarked ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                    </button>
                    @endauth
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white leading-snug mb-3">{{ $question->title }}</h1>

                    {{-- Meta --}}
                    <div class="flex flex-wrap items-center gap-2 mb-4 text-xs text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <div class="w-6 h-6 rounded-full bg-brand-500 flex items-center justify-center text-white text-[10px] font-bold">
                                {{ $question->is_anonymous ? '?' : strtoupper(substr($question->user->name ?? 'A', 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">
                                {{ $question->is_anonymous ? 'Anonymous' : ($question->user->name ?? 'Unknown') }}
                            </span>
                            @if(!$question->is_anonymous && $question->user && $question->user->reputation > 0)
                            <span class="px-1.5 py-0.5 bg-brand-50 dark:bg-brand-900/20 text-brand-600 rounded text-[10px] font-semibold">{{ number_format($question->user->reputation) }} rep</span>
                            @endif
                        </div>
                        <span>·</span>
                        <span>Asked {{ $question->created_at->format('M j, Y') }}</span>
                        @if($question->edited_at)
                        <span>· <span class="italic">edited {{ \Carbon\Carbon::parse($question->edited_at)->diffForHumans() }}</span></span>
                        @endif
                        @if($question->category)
                        <span>·</span>
                        <a href="/questions?category={{ $question->category->slug }}"
                            class="text-brand-600 hover:underline">In: {{ $question->category->name_ne ?? $question->category->name_en }}</a>
                        @endif
                    </div>

                    {{-- Tags --}}
                    @if($question->tags->count())
                    <div class="flex flex-wrap gap-1.5 mb-4">
                        @foreach($question->tags as $tag)
                        <a href="/questions?tag={{ $tag->slug }}"
                            class="text-xs px-2.5 py-0.5 bg-gray-100 dark:bg-gray-700 hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-brand-600 text-gray-600 dark:text-gray-300 rounded-full transition-colors">
                            {{ $tag->name_en }}
                        </a>
                        @endforeach
                    </div>
                    @endif

                    @if($question->featured_image)
                    <div class="mb-4 rounded-xl overflow-hidden">
                        <img src="{{ asset('storage/'.$question->featured_image) }}" alt="{{ $question->title }}" class="w-full max-h-72 object-cover">
                    </div>
                    @endif

                    @if($question->content)
                    <div class="prose dark:prose-invert prose-sm max-w-none text-gray-700 dark:text-gray-300 mb-5 qna-content">
                        {!! nl2br(e($question->content)) !!}
                    </div>
                    @endif

                    {{-- Revisions notice --}}
                    @if($question->revisions()->count())
                    <p class="text-xs text-gray-400 mb-3">
                        <svg class="w-3 h-3 inline -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edited {{ $question->revisions()->count() }} {{ Str::plural('time', $question->revisions()->count()) }}
                    </p>
                    @endif

                    {{-- Stats + Actions bar --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                {{ $question->answers_count }} {{ Str::plural('Answer', $question->answers_count) }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                {{ number_format($question->views_count) }} Views
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $question->follows()->count() }} followers
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Copy link --}}
                            <button onclick="copyLink('{{ url()->current() }}')"
                                class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-brand-600 transition-colors text-gray-500">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Copy link
                            </button>

                            {{-- Follow --}}
                            @auth
                            <button @click="toggleFollow()"
                                :class="following ? 'bg-brand-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-brand-50 hover:text-brand-600'"
                                class="flex items-center gap-1 px-2.5 py-1 rounded-lg transition-colors text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                <span x-text="following ? 'Following' : 'Follow'"></span>
                            </button>

                            {{-- Flag --}}
                            <button @click="showFlag = !showFlag"
                                :class="hasFlagged ? 'text-red-500 bg-red-50 dark:bg-red-900/20' : 'text-gray-400 hover:text-red-500 bg-gray-100 dark:bg-gray-700'"
                                class="flex items-center gap-1 px-2.5 py-1 rounded-lg transition-colors text-xs"
                                :title="hasFlagged ? 'Already flagged' : 'Flag this question'">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6H11.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                <span x-text="hasFlagged ? 'Flagged' : 'Flag'"></span>
                            </button>
                            @endauth

                            {{-- Edit (owner/admin) --}}
                            @auth
                            @if(auth()->id() === $question->user_id || in_array(auth()->user()->role, ['admin','editor']))
                            <a href="/questions/{{ $question->id }}/edit"
                                class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-brand-600 transition-colors text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                            @endif
                            {{-- Close (admin/editor) --}}
                            @if(in_array(auth()->user()->role, ['admin','editor']) && $question->status !== 'closed')
                            <button @click="showClose = !showClose"
                                class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-100 transition-colors text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                Close
                            </button>
                            @endif
                            @endauth

                            {{-- Share --}}
                            <span class="flex items-center gap-1">
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors text-[10px] font-bold">f</a>
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($question->title) }}" target="_blank"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-sky-100 text-sky-600 hover:bg-sky-200 transition-colors text-[10px] font-bold">𝕏</a>
                                <a href="https://api.whatsapp.com/send?text={{ urlencode($question->title . ' ' . url()->current()) }}" target="_blank"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition-colors text-[10px] font-bold">W</a>
                            </span>
                        </div>
                    </div>

                    {{-- Flag form --}}
                    <div x-show="showFlag && !hasFlagged" x-cloak class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800">
                        <p class="text-sm font-semibold text-red-700 dark:text-red-400 mb-3">Report this question</p>
                        <form @submit.prevent="submitFlag($el)">
                            @csrf
                            <div class="space-y-2 mb-3">
                                @foreach(['spam' => 'Spam', 'off-topic' => 'Off-topic', 'duplicate' => 'Duplicate', 'inappropriate' => 'Inappropriate'] as $val => $lbl)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="reason" value="{{ $val }}" class="text-red-500" required>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $lbl }}</span>
                                </label>
                                @endforeach
                            </div>
                            <textarea name="note" rows="2" placeholder="Additional notes (optional)" class="w-full text-xs border border-red-200 dark:border-red-700 dark:bg-gray-700 rounded-lg px-3 py-2 mb-2 focus:outline-none focus:ring-2 focus:ring-red-400"></textarea>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700">Submit Report</button>
                        </form>
                    </div>

                    {{-- Close form (admin) --}}
                    @auth @if(in_array(auth()->user()->role, ['admin','editor']))
                    <div x-show="showClose" x-cloak class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Close question</p>
                        <form method="POST" action="/questions/{{ $question->id }}/close" class="space-y-2">
                            @csrf
                            <input type="text" name="reason" placeholder="Reason (e.g. off-topic, duplicate, no longer relevant)" required class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <input type="number" name="duplicate_of" placeholder="Duplicate of question ID (optional)" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <button type="submit" class="px-4 py-2 bg-gray-700 text-white text-xs font-semibold rounded-lg hover:bg-gray-800">Close Question</button>
                        </form>
                    </div>
                    @endif @endauth
                </div>
            </div>
        </div>

        {{-- Answers --}}
        @if($question->answers->count())
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-gray-900 dark:text-white" id="answers-heading">
                    {{ $question->answers->count() }} {{ Str::plural('Answer', $question->answers->count()) }}
                </h2>
                <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
                    @foreach(['voted' => 'Voted', 'oldest' => 'Oldest', 'recent' => 'Recent'] as $key => $label)
                    <a href="?sort={{ $key }}"
                        class="px-3 py-1 text-xs font-medium rounded-lg transition-colors {{ $sort === $key ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                </div>
            </div>

            @php
            $sorted = $question->answers;
            if ($sort === 'voted')  $sorted = $sorted->sortByDesc('votes')->sortByDesc('is_best');
            elseif ($sort === 'oldest') $sorted = $sorted->sortBy('created_at');
            else $sorted = $sorted->sortByDesc('created_at');
            @endphp

            <div class="space-y-4">
                @foreach($sorted as $answer)
                @php $initVote = $userAnswerVotes[$answer->id] ?? null; @endphp
                <div class="bg-white dark:bg-gray-800 rounded-2xl border {{ $answer->is_best ? 'border-green-300 dark:border-green-700' : 'border-gray-100 dark:border-gray-700' }} shadow-sm p-5 flex gap-4 relative"
                    x-data="{
                        votes: {{ $answer->votes }},
                        userVote: {{ $initVote ?? 'null' }},
                        loading: false,
                        copied: false,
                        showComments: {{ $answer->comments->count() > 0 ? 'true' : 'false' }},
                        showCommentForm: false,
                        showEditForm: false,
                        comments: {{ $answer->comments->map(fn($c) => ['id'=>$c->id,'content'=>$c->content,'user'=>$c->is_anonymous?'Anonymous':($c->user->name??'Unknown'),'created_at'=>$c->created_at->diffForHumans()])->toJson() }},
                        commentContent: '',
                        async vote(dir) {
                            if (this.loading) return;
                            this.loading = true;
                            const res = await fetch('/answers/{{ $answer->id }}/vote', {
                                method:'POST',
                                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                                body:JSON.stringify({vote:dir})
                            });
                            const d = await res.json();
                            this.votes = d.votes; this.userVote = d.userVote; this.loading = false;
                        },
                        copyLink() {
                            navigator.clipboard.writeText(window.location.origin + window.location.pathname + '#answer-{{ $answer->id }}');
                            this.copied = true; setTimeout(() => this.copied = false, 2000);
                        },
                        async submitComment(form) {
                            const fd = new FormData(form);
                            const res = await fetch('/answers/{{ $answer->id }}/comments', {
                                method:'POST',
                                headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},
                                body:fd
                            });
                            if (res.ok) {
                                const c = await res.json();
                                this.comments.push(c);
                                this.commentContent = ''; this.showCommentForm = false; this.showComments = true;
                            }
                        }
                    }" id="answer-{{ $answer->id }}">

                    @if($answer->is_best)
                    <div class="absolute top-3 right-3 flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Best Answer
                    </div>
                    @endif

                    {{-- Answer vote --}}
                    <div class="flex flex-col items-center gap-1 flex-shrink-0 pt-1">
                        <button @click="vote('up')" :disabled="loading"
                            :class="userVote === 1 ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-500 ring-1 ring-orange-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-orange-100 hover:text-orange-500'"
                            class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <span :class="userVote === 1 ? 'text-orange-500' : (userVote === -1 ? 'text-blue-500' : 'text-gray-700 dark:text-gray-300')"
                            class="text-sm font-bold" x-text="votes"></span>
                        <button @click="vote('down')" :disabled="loading"
                            :class="userVote === -1 ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-500 ring-1 ring-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-blue-100 hover:text-blue-500'"
                            class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>

                    <div class="flex-1 min-w-0">
                        {{-- Answer content --}}
                        <div x-show="!showEditForm" class="prose dark:prose-invert prose-sm max-w-none text-gray-700 dark:text-gray-300 mb-4 qna-content">
                            {!! nl2br(e($answer->content)) !!}
                        </div>

                        {{-- Edit answer form --}}
                        @auth
                        @if(auth()->id() === $answer->user_id || in_array(auth()->user()->role, ['admin','editor']))
                        <div x-show="showEditForm" x-cloak class="mb-4">
                            <form method="POST" action="/answers/{{ $answer->id }}" class="space-y-2">
                                @csrf @method('PUT')
                                <textarea name="content" rows="6" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-y">{{ $answer->content }}</textarea>
                                <div class="flex gap-2">
                                    <button type="submit" class="px-4 py-2 bg-brand-500 text-white text-xs font-semibold rounded-lg hover:bg-brand-600">Save</button>
                                    <button type="button" @click="showEditForm=false" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-xs rounded-lg hover:bg-gray-200">Cancel</button>
                                </div>
                            </form>
                        </div>
                        @endif
                        @endauth

                        {{-- Edited notice --}}
                        @if($answer->edited_at)
                        <p class="text-xs text-gray-400 italic mb-2">edited {{ \Carbon\Carbon::parse($answer->edited_at)->diffForHumans() }}</p>
                        @endif

                        {{-- Comments --}}
                        <div x-show="showComments" class="mb-3 border-t border-gray-100 dark:border-gray-700 pt-3 space-y-2">
                            <template x-for="c in comments" :key="c.id">
                                <div class="flex gap-2 text-xs">
                                    <div class="w-5 h-5 rounded-full bg-gray-400 flex-shrink-0 flex items-center justify-center text-white text-[9px] font-bold" x-text="c.user.charAt(0).toUpperCase()"></div>
                                    <div>
                                        <span class="text-gray-700 dark:text-gray-300" x-text="c.content"></span>
                                        <span class="text-gray-400 ml-1">— <span x-text="c.user" class="font-medium"></span> · <span x-text="c.created_at"></span></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Comment form --}}
                        @auth
                        <div x-show="showCommentForm" x-cloak class="mb-3">
                            <form @submit.prevent="submitComment($el)" class="flex gap-2">
                                @csrf
                                <input type="text" name="content" x-model="commentContent" placeholder="Add a comment…" minlength="5" maxlength="600" required
                                    class="flex-1 text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <button type="submit" class="px-3 py-2 bg-brand-500 text-white text-xs font-semibold rounded-lg hover:bg-brand-600">Add</button>
                                <button type="button" @click="showCommentForm=false" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 text-xs rounded-lg">Cancel</button>
                            </form>
                        </div>
                        @endauth

                        {{-- Footer actions --}}
                        <div class="flex flex-wrap items-center gap-y-2 justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2 text-xs text-gray-500 min-w-0 flex-shrink">
                                <div class="w-6 h-6 rounded-full bg-gray-400 flex-shrink-0 flex items-center justify-center text-white text-[10px] font-bold">
                                    {{ $answer->is_anonymous ? '?' : strtoupper(substr($answer->user->name ?? 'A', 0, 1)) }}
                                </div>
                                <span class="font-medium truncate max-w-[100px]">{{ $answer->is_anonymous ? 'Anonymous' : ($answer->user->name ?? 'Unknown') }}</span>
                                @if(!$answer->is_anonymous && $answer->user && $answer->user->reputation > 0)
                                <span class="px-1 py-0.5 bg-brand-50 dark:bg-brand-900/20 text-brand-600 rounded text-[10px] font-semibold flex-shrink-0">{{ number_format($answer->user->reputation) }}</span>
                                @endif
                                <span class="flex-shrink-0">·</span>
                                <span class="flex-shrink-0 whitespace-nowrap">{{ $answer->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex items-center gap-1 flex-wrap flex-shrink-0">
                                {{-- Comments toggle --}}
                                <button @click="showComments = !showComments; showCommentForm = false"
                                    class="flex items-center gap-1 text-xs px-2 py-1 rounded-lg text-gray-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <span x-text="comments.length + ' comment' + (comments.length !== 1 ? 's' : '')"></span>
                                </button>
                                {{-- Add comment --}}
                                @auth
                                <button @click="showCommentForm = !showCommentForm; showComments = true"
                                    class="text-xs px-2 py-1 rounded-lg text-gray-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors">
                                    + Comment
                                </button>
                                {{-- Edit answer --}}
                                @if(auth()->id() === $answer->user_id || in_array(auth()->user()->role, ['admin','editor']))
                                <button @click="showEditForm = !showEditForm"
                                    class="text-xs px-2 py-1 rounded-lg text-gray-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors">
                                    Edit
                                </button>
                                @endif
                                @endauth
                                {{-- Copy link --}}
                                <button @click="copyLink()"
                                    class="flex items-center gap-1 text-xs px-2 py-1 rounded-lg text-gray-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/></svg>
                                    <span x-text="copied ? 'Copied!' : 'Share'"></span>
                                </button>
                                {{-- Best answer --}}
                                @auth
                                @if((auth()->id() === $question->user_id || in_array(auth()->user()->role, ['admin','editor'])) && !$answer->is_best)
                                <form method="POST" action="/questions/{{ $question->id }}/best/{{ $answer->id }}">
                                    @csrf
                                    <button class="text-xs px-2.5 py-1 border border-green-300 dark:border-green-700 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors">
                                        ✓ Best
                                    </button>
                                </form>
                                @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Leave an answer --}}
        @if($question->status !== 'closed')
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            @auth
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-brand-500">
                <button onclick="document.getElementById('answer-form').classList.toggle('hidden')"
                    class="w-full text-center text-white font-semibold text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Leave An Answer
                </button>
            </div>
            <div id="answer-form" class="p-6">
                @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-400 text-sm">
                    {{ session('success') }}
                </div>
                @endif
                <form method="POST" action="/questions/{{ $question->id }}/answers">
                    @csrf
                    <textarea name="content" id="answer-editor" rows="6" required minlength="10"
                        placeholder="Write a helpful, detailed answer… (Markdown supported)"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-y mb-3">{{ old('content') }}</textarea>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_anonymous" value="1" class="w-4 h-4 rounded border-gray-300 text-brand-500">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Post anonymously</span>
                        </label>
                        <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors">
                            Post Answer
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-3">Sign in to post an answer</p>
                <a href="/login" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors inline-block">Sign In to Answer</a>
            </div>
            @endauth
        </div>
        @else
        <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700 rounded-2xl p-6 text-center">
            <p class="text-amber-700 dark:text-amber-400 text-sm font-medium">This question is closed. New answers are not accepted.</p>
        </div>
        @endif
    </div>

    {{-- ── Right Sidebar ── --}}
    <div class="space-y-5">

        {{-- Stats --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="text-center p-3 bg-brand-50 dark:bg-brand-900/20 rounded-xl">
                    <p class="text-xl font-bold text-brand-600">{{ $totalQuestions }}</p>
                    <p class="text-[11px] text-gray-500">Questions</p>
                </div>
                <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                    <p class="text-xl font-bold text-green-600">{{ $totalAnswers }}</p>
                    <p class="text-[11px] text-gray-500">Answers</p>
                </div>
                <div class="text-center p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                    <p class="text-xl font-bold text-amber-600">{{ $bestAnswers }}</p>
                    <p class="text-[11px] text-gray-500">Best Answers</p>
                </div>
                <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                    <p class="text-xl font-bold text-purple-600">{{ $totalUsers }}</p>
                    <p class="text-[11px] text-gray-500">Users</p>
                </div>
            </div>
            <a href="/ask-question"
                class="w-full flex items-center justify-center gap-2 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Ask a Question
            </a>
        </div>

        {{-- Popular / Related tabs --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5"
            x-data="{ sideTab: 'related' }">
            <div class="flex gap-1 bg-gray-100 dark:bg-gray-700 p-1 rounded-xl mb-4">
                <button @click="sideTab = 'related'"
                    :class="sideTab === 'related' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500'"
                    class="flex-1 py-1 text-xs font-medium rounded-lg transition-colors">Related</button>
                <button @click="sideTab = 'popular'"
                    :class="sideTab === 'popular' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500'"
                    class="flex-1 py-1 text-xs font-medium rounded-lg transition-colors">Popular</button>
            </div>

            <div x-show="sideTab === 'related'" class="space-y-3">
                @forelse($relatedQuestions as $rq)
                <a href="/questions/{{ $rq->slug }}" class="flex items-start gap-2 group">
                    <div class="w-6 h-6 rounded flex-shrink-0 bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center">
                        <svg class="w-3 h-3 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-brand-600 leading-snug">{{ Str::limit($rq->title, 65) }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $rq->answers_count }} answers · {{ $rq->votes }} votes</p>
                    </div>
                </a>
                @empty
                <p class="text-xs text-gray-400">No related questions yet.</p>
                @endforelse
            </div>

            <div x-show="sideTab === 'popular'" class="space-y-3">
                @foreach($popularQuestions as $pq)
                <a href="/questions/{{ $pq->slug }}" class="flex items-start gap-2 group">
                    <div class="w-6 h-6 rounded flex-shrink-0 bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-brand-600 leading-snug">{{ Str::limit($pq->title, 65) }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $pq->answers_count }} answers</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Top contributors --}}
        @if($topMembers->count())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-3">Top Contributors</h3>
            <div class="space-y-2.5">
                @foreach($topMembers as $i => $member)
                <div class="flex items-center gap-2.5">
                    <span class="text-xs font-bold text-gray-300 w-4">{{ $i + 1 }}</span>
                    <div class="w-7 h-7 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ $member->name }}</p>
                        <p class="text-[10px] text-gray-400">{{ $member->questions_count }}q · {{ $member->answers_count }}a · {{ number_format($member->reputation ?? 0) }} rep</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        const btn = event.currentTarget;
        const orig = btn.innerHTML;
        btn.textContent = 'Copied!';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}

// Syntax highlight code blocks in qna-content
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.qna-content pre code, .qna-content code').forEach(el => {
        hljs.highlightElement(el);
    });
});
</script>
@endsection
