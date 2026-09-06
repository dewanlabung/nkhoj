@extends('layouts.admin')
@section('title', 'Add Feed')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Add Feed</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
        <a href="/admin" class="hover:text-brand-500">Home</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">›</span>
        <a href="/admin/rss-feeds" class="hover:text-brand-500">RSS Feeds</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">›</span>
        Add Feed
    </p>
</div>

<form method="POST" action="/admin/rss-feeds" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Settings --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-5">Settings</h3>
                <div class="space-y-4">

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Language <span class="text-red-400">*</span></label>
                        <select name="language" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="en">English</option>
                            <option value="ne">Nepali</option>
                            <option value="ar">Arabic</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Category <span class="text-red-400">*</span></label>
                        <select name="category_id" class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="">Select a category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name_en }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Number of Posts to Import <span class="text-red-400">*</span></label>
                        <input type="number" name="post_count" value="1" min="1" max="100"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    @foreach([
                        ['auto_update',       'Auto Update',                  true],
                        ['show_read_more',     'Show Read More Button',        true],
                        ['add_as_draft',       'Add Posts as Draft',           false],
                        ['generate_keywords',  'Generate Keywords from Title', false],
                    ] as [$name, $label, $default])
                    <div class="flex items-center justify-between py-1">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                        <div class="flex items-center gap-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="{{ $name }}" value="0">
                                <input type="checkbox" name="{{ $name }}" value="1" class="sr-only peer" {{ $default ? 'checked' : '' }}>
                                <div class="w-9 h-5 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                            </label>
                            <span class="text-xs text-gray-400">Yes</span>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>

        {{-- Right: General --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-5">General</h3>
                <div class="space-y-4">

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Feed Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required placeholder="Feed Name"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Feed URL <span class="text-red-400">*</span></label>
                        <input type="url" name="url" required placeholder="https://example.com/feed.xml"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Images <span class="text-red-400">*</span></label>
                        <select name="images_source"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="original">Show Images from Original Source</option>
                            <option value="remote">Use Remote URL</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">⚠ Some images may be protected by copyright. Before downloading and hosting images on your own server, please make sure you have the appropriate usage rights. To reduce potential legal risks, using images via their remote URL is often a safer approach.</p>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Read More Button Text</label>
                        <input type="text" name="read_more_text" value="Read More" placeholder="Read More"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Default Image</label>
                        <div class="border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-xl p-8 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-brand-400 transition-colors">
                            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-sm text-gray-400">Click to upload default image</p>
                            <input type="file" name="default_image" accept="image/*" class="hidden">
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <div class="flex justify-end mt-6">
        <button type="submit"
            class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            Add Feed
        </button>
    </div>
</form>
@endsection
