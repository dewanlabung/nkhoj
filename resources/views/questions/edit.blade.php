@extends('layouts.app')
@section('title', 'Edit Question')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit Question</h1>
                <p class="text-sm text-gray-400 mt-0.5">Update your question details</p>
            </div>
            <a href="/questions/{{ $question->slug }}" class="text-sm text-gray-400 hover:text-gray-600">← Back</a>
        </div>

        @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="/questions/{{ $question->id }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">
                    Question Title <span class="text-red-400">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title', $question->title) }}" required
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Category</label>
                <select name="category_id"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">— None —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $question->category_id) == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name_ne ?? $cat->name_en }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Question Details</label>
                <textarea name="content" rows="7"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-y">{{ old('content', $question->content) }}</textarea>
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Tags</label>
                <input type="text" name="tags" value="{{ old('tags', $selectedTags) }}"
                    placeholder="comma separated"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                @if($tags->count())
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach($tags->take(20) as $tag)
                    <button type="button" onclick="addTag('{{ $tag->name_en }}')"
                        class="text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-brand-50 hover:text-brand-600 text-gray-600 dark:text-gray-300 rounded-full transition-colors">
                        +{{ $tag->name_en }}
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">
                    Replace Image <span class="font-normal text-gray-400">(optional)</span>
                </label>
                @if($question->featured_image)
                <img src="{{ asset('storage/'.$question->featured_image) }}" class="w-32 h-20 object-cover rounded-lg mb-2">
                @endif
                <input type="file" name="featured_image" accept="image/*"
                    class="w-full text-sm text-gray-500 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100">
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_anonymous" value="1" {{ old('is_anonymous', $question->is_anonymous) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                <span class="text-sm text-gray-700 dark:text-gray-300">Post anonymously</span>
            </label>

            <div class="flex gap-3 pt-2">
                <a href="/questions/{{ $question->slug }}"
                    class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</a>
                <button type="submit"
                    class="flex-1 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function addTag(name) {
    const input = document.querySelector('input[name="tags"]');
    const parts = input.value.split(',').map(t => t.trim()).filter(Boolean);
    if (!parts.includes(name)) parts.push(name);
    input.value = parts.join(', ');
}
</script>
@endsection
