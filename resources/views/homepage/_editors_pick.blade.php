@if($editorsPick->count())
<div class="mb-7">
    <div class="flex items-center gap-2 mb-3">
        <span class="text-xs font-bold text-brand-600 uppercase tracking-widest">सम्पादकको छनोट</span>
        <div class="flex-1 h-px bg-gray-100 dark:bg-gray-700"></div>
        <span class="text-xs text-gray-400">Editor's Pick</span>
    </div>
    <div class="grid grid-cols-3 gap-3">
        @foreach($editorsPick as $pick)
        <a href="/posts/{{ $pick->slug }}" class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow overflow-hidden block">
            <div class="relative" style="aspect-ratio:16/9; overflow:hidden;">
                @if($pick->thumbnail_url)
                <img src="{{ $pick->thumbnail_url }}" loading="lazy" alt="{{ $pick->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                <div class="w-full h-full bg-gradient-to-br from-brand-100 to-indigo-100 flex items-center justify-center">
                    <span class="text-3xl font-black text-brand-200">{{ strtoupper(substr($pick->title,0,1)) }}</span>
                </div>
                @endif
                <div class="absolute top-2 left-2">
                    <span class="text-xs px-2 py-0.5 bg-brand-500 text-white rounded-full font-semibold">{{ $pick->category->name_ne ?? $pick->category->name_en }}</span>
                </div>
            </div>
            <div class="p-3">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-brand-600 transition-colors line-clamp-2 font-nepali leading-snug">{{ $pick->title }}</h3>
                <div class="flex items-center gap-1.5 mt-1.5 text-xs text-gray-400">
                    <div class="w-4 h-4 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold text-xs">{{ strtoupper(substr($pick->author->name,0,1)) }}</div>
                    <span>{{ $pick->author->name }}</span>
                    <span>·</span>
                    <span>{{ number_format($pick->view_count) }} views</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif
