@extends('layouts.admin')
@section('title', 'Edit Page')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
        <a href="/admin/pages" class="hover:text-brand-600">Pages</a>
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Edit: {{ $page->title }}</span>
    </div>

    <form method="POST" action="/admin/pages/{{ $page->id }}" class="space-y-5">
        @csrf @method('PUT')
        @if($errors->any())
        <div class="px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        {{-- Main content --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Title *</label>
                    <input type="text" name="title" required value="{{ old('title', $page->title) }}"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Slug</label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">/pages/</span>
                        <input type="text" name="slug" value="{{ old('slug', $page->slug) }}"
                            class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Content</label>
                    <textarea id="page-content-editor" name="content" rows="12"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-y">{{ old('content', $page->content) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Settings --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h4 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">Page Settings</h4>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Status</label>
                    <select name="status" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="active" {{ old('status', $page->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $page->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Page Type</label>
                    <select name="page_type" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="custom" {{ old('page_type', $page->page_type) === 'custom' ? 'selected' : '' }}>Custom</option>
                        <option value="default" {{ old('page_type', $page->page_type) === 'default' ? 'selected' : '' }}>Default</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Menu Position</label>
                    <select name="menu_position" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="" {{ !$page->menu_position ? 'selected' : '' }}>None</option>
                        <option value="top_menu" {{ old('menu_position', $page->menu_position) === 'top_menu' ? 'selected' : '' }}>Top Menu</option>
                        <option value="main_menu" {{ old('menu_position', $page->menu_position) === 'main_menu' ? 'selected' : '' }}>Main Menu</option>
                        <option value="footer" {{ old('menu_position', $page->menu_position) === 'footer' ? 'selected' : '' }}>Footer</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Language</label>
                    <select name="language" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="en" {{ old('language', $page->language) === 'en' ? 'selected' : '' }}>English</option>
                        <option value="ne" {{ old('language', $page->language) === 'ne' ? 'selected' : '' }}>Nepali</option>
                        <option value="ar" {{ old('language', $page->language) === 'ar' ? 'selected' : '' }}>Arabic</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- SEO --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h4 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">SEO</h4>
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Meta Description</label>
                    <textarea name="meta_description" rows="2"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none">{{ old('meta_description', $page->meta_description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <a href="/admin/pages" class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Update Page</button>
        </div>
    </form>
</div>
@push('scripts')
@include('partials.tinymce', ['editorId' => 'page-content-editor', 'height' => 420])
@endpush
@endsection
