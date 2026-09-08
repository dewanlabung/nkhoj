@extends('layouts.app')

@section('title', 'Page Settings — ' . $page->name)

@section('content')
<div class="min-h-screen bg-gray-50">

    {{-- Mobile header --}}
    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
        <a href="/pages/{{ $page->slug }}/dashboard" class="p-2 -ml-2 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="font-bold text-gray-900 text-lg">Settings & privacy</h1>
    </div>

    <form method="POST" action="/pages/{{ $page->slug }}/settings" enctype="multipart/form-data" class="max-w-xl mx-auto px-4 py-6 space-y-4">
        @csrf
        @method('PUT')

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- Cover photo --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="font-bold text-gray-900">Cover photo</p>
            </div>
            <div class="p-4">
                <div class="relative h-28 rounded-xl overflow-hidden bg-gray-200 mb-3">
                    @if($page->cover_url)
                        <img src="{{ $page->cover_url }}" class="w-full h-full object-cover" id="cover-preview">
                    @else
                        <img src="" class="w-full h-full object-cover hidden" id="cover-preview">
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                </div>
                <label class="cursor-pointer flex items-center gap-2 text-blue-600 text-sm font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Upload cover photo
                    <input type="file" name="cover" accept="image/*" class="hidden" onchange="previewImage(this, 'cover-preview')">
                </label>
            </div>
        </div>

        {{-- Profile photo + Page name --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="font-bold text-gray-900">Page details</p>
            </div>
            <div class="p-4 space-y-4">
                {{-- Avatar --}}
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-200">
                            <img src="{{ $page->avatar }}" alt="" class="w-full h-full object-cover" id="avatar-preview">
                        </div>
                    </div>
                    <label class="cursor-pointer flex items-center gap-2 text-blue-600 text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Change profile photo
                        <input type="file" name="avatar" accept="image/*" class="hidden" onchange="previewImage(this, 'avatar-preview')">
                    </label>
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Page name</label>
                    <input type="text" name="name" value="{{ old('name', $page->name) }}" required maxlength="150"
                           class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500">
                </div>

                {{-- Bio --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bio</label>
                    <textarea name="bio" rows="3" maxlength="500"
                              class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 resize-none">{{ old('bio', $page->bio) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Contact info --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="font-bold text-gray-900">Contact info</p>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Website</label>
                    <input type="url" name="website" value="{{ old('website', $page->website) }}" placeholder="https://..."
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Email</label>
                    <input type="email" name="email" value="{{ old('email', $page->email) }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $page->phone) }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Location</label>
                    <input type="text" name="location" value="{{ old('location', $page->location) }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
                </div>
            </div>
        </div>

        {{-- Save --}}
        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition">
            Save changes
        </button>
    </form>
</div>

<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById(previewId);
            img.src = e.target.result;
            img.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
