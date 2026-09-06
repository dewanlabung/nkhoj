@extends('layouts.admin')
@section('title', 'Polls')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Create poll --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5"
         x-data="{ optCount: 3 }">
        <h3 class="font-bold text-gray-900 dark:text-white mb-4">Create Poll</h3>
        <form method="POST" action="/admin/polls" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Question *</label>
                <textarea name="question" rows="2" required placeholder="What do you think about...?"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Options</label>
                <div class="space-y-2">
                    <template x-for="i in optCount" :key="i">
                        <input type="text" :name="'options[' + (i-1) + ']'"
                            :placeholder="'Option ' + i"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </template>
                </div>
                <button type="button" @click="optCount++"
                    class="mt-2 text-xs text-brand-600 hover:underline">+ Add option</button>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Expires At <span class="font-normal text-gray-400">(optional)</span></label>
                <input type="datetime-local" name="expires_at"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                <input type="checkbox" name="allow_multiple" value="1" class="rounded border-gray-300">
                Allow multiple choice
            </label>

            <button class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                Create Poll
            </button>
        </form>
    </div>

    {{-- Polls list --}}
    <div class="lg:col-span-2 space-y-4">
        @forelse($polls as $poll)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between gap-4 mb-3">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $poll->question }}</p>
                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-400 flex-wrap">
                        <span>By {{ $poll->author?->name }}</span>
                        <span>{{ $poll->created_at->diffForHumans() }}</span>
                        @if($poll->expires_at)
                        <span class="{{ $poll->isExpired() ? 'text-red-500' : 'text-green-500' }}">
                            {{ $poll->isExpired() ? 'Expired' : 'Expires ' . $poll->expires_at->diffForHumans() }}
                        </span>
                        @endif
                        @if($poll->post)
                        <a href="/posts/{{ $poll->post->slug }}" class="text-brand-600 hover:underline" target="_blank">
                            → {{ Str::limit($poll->post->title, 30) }}
                        </a>
                        @endif
                        <span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full font-mono">
                            [poll:{{ $poll->id }}]
                        </span>
                    </div>
                </div>
                <form method="POST" action="/admin/polls/{{ $poll->id }}" onsubmit="return confirm('Delete poll?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-400 hover:text-red-600 flex-shrink-0">Delete</button>
                </form>
            </div>

            {{-- Options with vote bars --}}
            @php $total = $poll->totalVotes(); @endphp
            <div class="space-y-2">
                @foreach($poll->options as $opt)
                @php $pct = $total > 0 ? round(($opt->votes_count / $total) * 100) : 0; @endphp
                <div class="relative">
                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300 mb-1">
                        <span>{{ $opt->text }}</span>
                        <span class="font-semibold">{{ $opt->votes_count }} <span class="font-normal text-gray-400">({{ $pct }}%)</span></span>
                    </div>
                    <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-brand-500 rounded-full transition-all" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-3">{{ $total }} total votes</p>
        </div>
        @empty
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-16 text-center text-gray-400">
            <p class="text-4xl mb-3">📊</p>
            <p>No polls yet. Create one!</p>
        </div>
        @endforelse
        <div>{{ $polls->links() }}</div>
    </div>
</div>
@endsection
