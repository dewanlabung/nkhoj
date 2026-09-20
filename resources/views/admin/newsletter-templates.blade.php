@extends('layouts.admin')
@section('title', 'Newsletter Templates')

@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Newsletter Templates</h1>
            <p class="text-sm text-gray-600 mt-1">Create and manage email templates for your newsletter</p>
        </div>
        <a href="/admin/newsletter?tab=templates" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ isset($template) ? '/admin/newsletter/templates/' . $template->id : '/admin/newsletter/templates' }}" class="space-y-5">
            @csrf
            @if(isset($template))
            @method('PUT')
            @endif

            <div>
                <label for="name" class="text-sm font-semibold text-gray-700 block mb-2">
                    Template Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" required maxlength="150" placeholder="e.g., Weekly Digest"
                    value="{{ old('name', $template->name ?? '') }}"
                    class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="subject" class="text-sm font-semibold text-gray-700 block mb-2">
                    Email Subject <span class="text-red-500">*</span>
                </label>
                <input type="text" id="subject" name="subject" required maxlength="255" placeholder="e.g., This week's नेपाली stories"
                    value="{{ old('subject', $template->subject ?? '') }}"
                    class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                @error('subject')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="html_content" class="text-sm font-semibold text-gray-700 block mb-2">
                    HTML Content <span class="text-red-500">*</span>
                </label>
                <textarea id="html_content" name="html_content" required rows="12" placeholder="Paste your HTML email template here..."
                    class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono text-sm">{{ old('html_content', $template->html_content ?? '') }}</textarea>
                <p class="text-xs text-gray-500 mt-2">💡 Tip: Use HTML for rich formatting. Include placeholders like {subscriber_name}, {unsubscribe_link}</p>
                @error('html_content')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="text_content" class="text-sm font-semibold text-gray-700 block mb-2">
                    Plain Text Version (optional)
                </label>
                <textarea id="text_content" name="text_content" rows="8" placeholder="Plain text version for email clients that don't support HTML..."
                    class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono text-sm">{{ old('text_content', $template->text_content ?? '') }}</textarea>
                @error('text_content')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                    <input type="checkbox" name="is_default" value="true" {{ old('is_default', $template->is_default ?? false) ? 'checked' : '' }}
                        class="w-4 h-4 text-brand-500 rounded">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Set as default template</p>
                        <p class="text-xs text-gray-500">Use this template by default when sending newsletters</p>
                    </div>
                </label>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="/admin/newsletter?tab=templates" class="flex-1 px-4 py-3 border border-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors text-center">
                    Cancel
                </a>
                <button type="submit" class="flex-1 px-4 py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-lg transition-colors">
                    {{ isset($template) ? '✅ Update Template' : '➕ Create Template' }}
                </button>
            </div>
        </form>
    </div>

    {{-- Template Preview --}}
    <div class="mt-8">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Preview</h2>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="text-xs text-gray-500 mb-3">Subject: {{ old('subject', $template->subject ?? '(subject here)') }}</div>
            <div class="border-t pt-4">
                <p class="text-xs text-gray-500 mb-2">HTML Preview:</p>
                <iframe srcdoc="{{ htmlspecialchars(old('html_content', $template->html_content ?? '<p>Content will appear here</p>')) }}"
                    class="w-full h-64 border border-gray-200 rounded-lg"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection
