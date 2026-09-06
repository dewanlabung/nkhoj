@extends('layouts.admin')
@section('title', 'Media Library')

@push('head')
<style>
.media-card:hover .media-overlay { opacity: 1; }
.media-overlay { opacity: 0; transition: opacity .15s; }
</style>
@endpush

@section('content')

{{-- Upload zone --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 mb-5"
    x-data="mediaUpload()" @dragover.prevent="dragging=true" @dragleave="dragging=false" @drop.prevent="handleDrop($event)">
    <div class="border-2 border-dashed rounded-xl p-6 text-center transition-colors"
        :class="dragging ? 'border-brand-400 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-600'">
        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
        </svg>
        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Drag files here or <label for="file-input" class="text-brand-600 hover:underline cursor-pointer">browse</label></p>
        <p class="text-xs text-gray-400">Images, Videos, Audio, PDFs, Documents — max 50MB each</p>
        <form id="upload-form" method="POST" action="/admin/media/upload" enctype="multipart/form-data">
            @csrf
            <input id="file-input" type="file" name="files[]" multiple
                accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar"
                class="hidden"
                x-ref="fileInput"
                @change="submitUpload()">
        </form>
        <div x-show="uploading" class="mt-3">
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-2">
                <div class="bg-brand-500 h-1.5 rounded-full transition-all" :style="`width:${progress}%`"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1" x-text="`Uploading... ${progress}%`"></p>
        </div>
    </div>
</div>

{{-- Stats + filter tabs --}}
<div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-1 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-1">
        @foreach(['all' => 'All', 'image' => 'Images', 'video' => 'Videos', 'audio' => 'Audio', 'document' => 'Documents'] as $filterKey => $filterLabel)
        <button onclick="filterMedia('{{ $filterKey }}')"
            id="filter-{{ $filterKey }}"
            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $filterKey === 'all' ? 'bg-brand-500 text-white' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            {{ $filterLabel }}
            <span class="ml-0.5 opacity-60" id="count-{{ $filterKey }}"></span>
        </button>
        @endforeach
    </div>
    <div class="text-sm text-gray-400 dark:text-gray-500">
        <span id="visible-count">{{ count($files) }}</span> files · {{ $totalSize }}
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-2.5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm rounded-xl">
    {{ session('success') }}
</div>
@endif

{{-- File grid --}}
@if(count($files) > 0)
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3" id="media-grid">
    @foreach($files as $file)
    @php
        $isImg   = in_array($file['ext'], ['jpg','jpeg','png','gif','webp','svg','avif']);
        $isVideo = in_array($file['ext'], ['mp4','webm','mov','avi','mkv','m4v']);
        $isAudio = in_array($file['ext'], ['mp3','wav','ogg','m4a','flac','aac']);
        $isPdf   = $file['ext'] === 'pdf';
        $isDoc   = in_array($file['ext'], ['doc','docx','xls','xlsx','ppt','pptx','txt']);
        $isZip   = in_array($file['ext'], ['zip','rar','7z']);
        $mediaType = $isImg ? 'image' : ($isVideo ? 'video' : ($isAudio ? 'audio' : 'document'));
    @endphp
    <div class="media-card relative bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden group"
        data-type="{{ $mediaType }}" x-data="{ copied: false }">

        {{-- Thumbnail --}}
        <div class="aspect-square bg-gray-100 dark:bg-gray-700 overflow-hidden relative flex items-center justify-center">
            @if($isImg)
            <img src="/uploads/{{ $file['name'] }}" alt="{{ $file['name'] }}"
                class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105">
            @elseif($isVideo)
            <div class="relative w-full h-full bg-gray-800 flex items-center justify-center">
                <video src="/uploads/{{ $file['name'] }}" class="w-full h-full object-cover opacity-70" preload="metadata" muted></video>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                </div>
                <span class="absolute bottom-1 right-1 bg-black/60 text-white text-[9px] px-1.5 py-0.5 rounded font-medium uppercase">{{ strtoupper($file['ext']) }}</span>
            </div>
            @elseif($isAudio)
            <div class="flex flex-col items-center justify-center gap-2 text-purple-500 dark:text-purple-400 h-full w-full bg-purple-50 dark:bg-purple-900/20">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                <span class="text-xs font-bold uppercase tracking-wide opacity-60">{{ strtoupper($file['ext']) }}</span>
            </div>
            @elseif($isPdf)
            <div class="flex flex-col items-center justify-center gap-2 text-red-500 h-full w-full bg-red-50 dark:bg-red-900/20">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span class="text-xs font-bold uppercase tracking-wide opacity-60">PDF</span>
            </div>
            @elseif($isZip)
            <div class="flex flex-col items-center justify-center gap-2 text-yellow-600 h-full w-full bg-yellow-50 dark:bg-yellow-900/20">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span class="text-xs font-bold uppercase tracking-wide opacity-60">{{ strtoupper($file['ext']) }}</span>
            </div>
            @else
            <div class="flex flex-col items-center justify-center gap-2 text-blue-500 h-full w-full bg-blue-50 dark:bg-blue-900/20">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="text-xs font-bold uppercase tracking-wide opacity-60">{{ strtoupper($file['ext']) }}</span>
            </div>
            @endif

            {{-- Hover overlay --}}
            <div class="media-overlay absolute inset-0 bg-black/50 flex items-center justify-center gap-2">
                <button @click="navigator.clipboard.writeText('{{ config('app.url') }}/uploads/{{ $file['name'] }}'); copied=true; setTimeout(()=>copied=false,2000)"
                    class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-white"
                    :title="copied ? 'Copied!' : 'Copy URL'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </button>
                @if($isImg)
                <a href="/uploads/{{ $file['name'] }}" target="_blank"
                    class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-white">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                @endif
                <form method="POST" action="/admin/media/{{ $file['name'] }}" onsubmit="return confirm('Delete this file?')">
                    @csrf @method('DELETE')
                    <button class="w-8 h-8 rounded-full bg-red-500/80 hover:bg-red-600 flex items-center justify-center text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- File info --}}
        <div class="p-2">
            <p class="text-[10px] text-gray-600 dark:text-gray-400 truncate" title="{{ $file['name'] }}">{{ $file['name'] }}</p>
            <p class="text-[9px] text-gray-400 dark:text-gray-500">{{ $file['size'] }}</p>
            <p class="text-[9px] text-green-500 font-medium" x-show="copied">Copied!</p>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-16 text-center text-gray-400 dark:text-gray-500">
    <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    <p class="font-medium">No media files yet</p>
    <p class="text-sm mt-1">Upload images, videos, audio, and documents above.</p>
</div>
@endif

@push('scripts')
<script>
function mediaUpload() {
    return {
        dragging: false,
        uploading: false,
        progress: 0,
        handleDrop(e) {
            this.dragging = false;
            const files = e.dataTransfer.files;
            if (!files.length) return;
            const input = this.$refs.fileInput;
            const dt = new DataTransfer();
            Array.from(files).forEach(f => dt.items.add(f));
            input.files = dt.files;
            this.submitUpload();
        },
        submitUpload() {
            const form = document.getElementById('upload-form');
            const formData = new FormData(form);
            this.uploading = true; this.progress = 0;
            const xhr = new XMLHttpRequest();
            xhr.upload.onprogress = e => { if (e.lengthComputable) this.progress = Math.round(e.loaded/e.total*100); };
            xhr.onload = () => { this.uploading = false; if (xhr.status < 400) location.reload(); };
            xhr.onerror = () => { this.uploading = false; alert('Upload failed.'); };
            xhr.open('POST', '/admin/media/upload');
            xhr.send(formData);
        }
    }
}

// Filter tabs
function filterMedia(type) {
    const cards = document.querySelectorAll('.media-card');
    let visible = 0;
    cards.forEach(c => {
        const show = type === 'all' || c.dataset.type === type;
        c.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('visible-count').textContent = visible;
    document.querySelectorAll('[id^="filter-"]').forEach(btn => {
        const active = btn.id === 'filter-' + type;
        btn.className = btn.className.replace(/bg-brand-500 text-white|text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/g, '');
        btn.className += active ? ' bg-brand-500 text-white' : ' text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700';
    });
}

// Count badges
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.media-card');
    const counts = { all: 0, image: 0, video: 0, audio: 0, document: 0 };
    cards.forEach(c => { counts.all++; counts[c.dataset.type] = (counts[c.dataset.type] || 0) + 1; });
    Object.entries(counts).forEach(([k, v]) => {
        const el = document.getElementById('count-' + k);
        if (el) el.textContent = '(' + v + ')';
    });
});
</script>
@endpush

@endsection
