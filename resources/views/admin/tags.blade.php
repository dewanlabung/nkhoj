@extends('layouts.admin')
@section('title', 'Tags')

@section('content')
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
    <h3 class="font-bold text-gray-900 mb-4">All Tags <span class="text-gray-400 font-normal">({{ $tags->count() }})</span></h3>
    <div class="flex flex-wrap gap-2">
        @foreach($tags as $tag)
        <div class="flex items-center gap-1 bg-gray-100 rounded-full pl-3 pr-1 py-1">
            <span class="text-sm text-gray-700">#{{ $tag->name_en }}</span>
            <span class="text-xs text-gray-400 mx-1">{{ $tag->posts_count }}</span>
            <form method="POST" action="/admin/tags/{{ $tag->id }}" onsubmit="return confirm('Delete tag?')">
                @csrf @method('DELETE')
                <button class="w-5 h-5 flex items-center justify-center rounded-full hover:bg-red-100 text-gray-400 hover:text-red-500 text-xs leading-none">×</button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endsection
