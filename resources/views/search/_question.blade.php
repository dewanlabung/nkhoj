<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 hover:shadow-md transition-shadow">
    <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-sm flex-shrink-0 mt-0.5">?</div>
        <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-gray-900 hover:text-brand-600 transition-colors text-sm font-nepali line-clamp-2">
                <a href="/questions/{{ $question->slug ?? $question->id }}">{{ $question->title }}</a>
            </h3>
            @if($question->content)
            <p class="text-xs text-gray-500 line-clamp-1 mt-0.5 font-nepali">{{ strip_tags($question->content) }}</p>
            @endif
            <div class="flex items-center gap-2 text-xs text-gray-400 mt-1.5">
                <span>{{ $question->user->name ?? 'Anonymous' }}</span>
                <span>·</span>
                <span>{{ $question->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
