<a href="/questions/{{ $question->slug ?? $question->id }}"
   class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition block group">
    <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-600 font-bold text-sm flex-shrink-0 mt-0.5">?</div>
    <div class="flex-1 min-w-0">
        <h3 class="font-semibold text-sm text-gray-900 group-hover:text-brand-600 transition line-clamp-2 font-nepali">
            {{ $question->title }}
        </h3>
        @if($question->content)
        <p class="text-xs text-gray-400 line-clamp-1 mt-0.5 font-nepali">{{ strip_tags($question->content) }}</p>
        @endif
        <div class="flex items-center gap-1.5 text-xs text-gray-400 mt-1">
            <span>{{ $question->user->name ?? 'Anonymous' }}</span>
            <span>·</span>
            <span>{{ $question->created_at->diffForHumans() }}</span>
        </div>
    </div>
</a>
