@extends('layouts.app')
@section('title', 'Stories — ' . $page->name)
@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
        <a href="/pages/{{ $page->slug }}/dashboard" class="p-2 -ml-2 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1">
            <h1 class="font-bold text-gray-900 text-lg">Stories</h1>
            <p class="text-xs text-gray-400">Stories expire after 24 hours</p>
        </div>
    </div>

    <div class="max-w-xl mx-auto px-4 py-6 space-y-4">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Add story --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100"><p class="font-bold text-gray-900">Add a story</p></div>
            <form method="POST" action="/pages/{{ $page->slug }}/stories" enctype="multipart/form-data" class="p-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Photo (optional)</label>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition cursor-pointer" id="story-drop">
                        <input type="file" name="image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewStory(this)">
                        <img id="story-preview" src="" class="hidden max-h-40 mx-auto rounded-lg mb-2 object-contain">
                        <p class="text-sm text-gray-400" id="story-label">Tap to upload image</p>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Caption</label>
                    <input type="text" name="caption" maxlength="255" placeholder="What's happening?"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Background color (if no image)</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach(['#1877f2','#e74c3c','#2ecc71','#f39c12','#9b59b6','#1abc9c','#e91e8c','#333333'] as $color)
                        <label class="cursor-pointer">
                            <input type="radio" name="bg_color" value="{{ $color }}" class="sr-only peer">
                            <span class="block w-8 h-8 rounded-full border-4 border-transparent peer-checked:border-gray-400 transition"
                                style="background:{{ $color }}"></span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-3 rounded-xl transition">
                    Post Story
                </button>
            </form>
        </div>

        {{-- Existing stories --}}
        @if($stories->count())
        <div class="space-y-3">
            @foreach($stories as $story)
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden flex items-center gap-4 p-4">
                {{-- Preview --}}
                <div class="w-14 h-14 rounded-xl overflow-hidden flex-shrink-0"
                    style="{{ $story->image_url ? '' : 'background:'.($story->bg_color ?? '#1877f2') }}">
                    @if($story->image_url)
                    <img src="{{ $story->image_url }}" class="w-full h-full object-cover">
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    @if($story->caption) <p class="text-sm font-medium text-gray-900 truncate">{{ $story->caption }}</p> @endif
                    @if($story->expires_at->isPast())
                        <p class="text-xs text-red-500 font-medium">Expired</p>
                    @else
                        <p class="text-xs text-gray-400">Expires {{ $story->expires_at->diffForHumans() }}</p>
                    @endif
                </div>
                <form action="/pages/{{ $page->slug }}/stories/{{ $story->id }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-gray-400 hover:text-red-500 p-2 transition" onclick="return confirm('Delete story?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        <div class="mt-2">{{ $stories->links() }}</div>
        @else
        <div class="text-center py-12">
            <p class="text-gray-500">No stories yet. Post your first one above!</p>
        </div>
        @endif
    </div>
</div>
<script>
function previewStory(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('story-preview').src = e.target.result;
            document.getElementById('story-preview').classList.remove('hidden');
            document.getElementById('story-label').textContent = input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
