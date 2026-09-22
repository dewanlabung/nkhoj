@if($heroStrip->count())
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
    @foreach($heroStrip as $i => $hero)
    <a href="/posts/{{ $hero->slug }}" class="group relative rounded-xl overflow-hidden block {{ $i === 0 ? 'sm:col-span-1' : '' }}" style="min-height:200px;">
        @if($hero->thumbnail_url)
        <img src="{{ $hero->thumbnail_url }}" alt="{{ $hero->title }}"
            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
        <div class="absolute inset-0 bg-gradient-to-br
            {{ $i === 0 ? 'from-brand-500 to-indigo-700' : ($i === 1 ? 'from-teal-500 to-cyan-700' : 'from-orange-500 to-pink-700') }}">
        </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
        <div class="relative flex flex-col justify-end h-full p-4" style="min-height:200px;">
            @if($hero->is_featured)
            <span class="text-xs font-bold text-brand-300 uppercase tracking-widest mb-1">Featured</span>
            @endif
            <span class="text-xs text-white/60 font-nepali mb-1">{{ $hero->category->name_ne ?? $hero->category->name_en }}</span>
            <h2 class="text-sm font-bold text-white line-clamp-2 leading-snug font-nepali group-hover:text-brand-200 transition-colors">{{ $hero->title }}</h2>
            <div class="flex items-center gap-2 mt-2 text-xs text-white/50">
                <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-xs">{{ strtoupper(substr($hero->author->name,0,1)) }}</div>
                <span>{{ $hero->author->name }}</span>
                <span>·</span>
                <span>{{ $hero->published_at->diffForHumans() }}</span>
            </div>
        </div>
    </a>
    @endforeach
</div>
@endif
