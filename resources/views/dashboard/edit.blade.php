@extends('layouts.app')
@section('title', 'Edit: ' . $post->title)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8"
    x-data="articleEditor(
        @js($post->title),
        @js($post->excerpt ?? ''),
        @js($bodyText),
        @js($post->seo_title ?? ''),
        @js($post->seo_desc ?? ''),
        @js($currentTags),
        @js($post->thumbnail_url ?? '')
    )">

    {{-- Main editor --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
            <input type="text" x-model="title" @input.debounce.800ms="aiSuggestSeo()"
                placeholder="लेखको शीर्षक..."
                class="w-full text-2xl font-bold border-0 outline-none text-gray-900 placeholder-gray-300 font-nepali">
            <div class="border-t border-gray-100 pt-4">
                <input type="text" x-model="excerpt" placeholder="छोटो विवरण..."
                    class="w-full text-sm text-gray-600 border-0 outline-none placeholder-gray-300 font-nepali">
            </div>
            <div class="border-t border-gray-100 pt-4">
                <textarea id="body-editor" x-model="body" rows="22" placeholder="यहाँ लेख लेख्नुहोस्..."
                    class="w-full text-sm text-gray-700 leading-relaxed border-0 outline-none placeholder-gray-300 resize-none font-nepali">{{ $bodyText }}</textarea>
            </div>
        </div>

        {{-- SEO --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4 text-sm flex items-center gap-2">🔍 SEO & मेटाडेटा</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">SEO Title <span class="text-gray-300">(max 60)</span></label>
                    <input x-model="seoTitle" type="text" maxlength="60"
                        placeholder="Google खोजमा देखिने शीर्षक..."
                        class="mt-1 w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="mt-0.5 text-xs text-gray-400" x-text="(seoTitle||'').length + '/60 chars'"></p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Meta Description <span class="text-gray-300">(max 155)</span></label>
                    <textarea x-model="seoDesc" rows="2" maxlength="155"
                        placeholder="Google खोजमा देखिने विवरण..."
                        class="mt-1 w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                    <p class="mt-0.5 text-xs text-gray-400" x-text="(seoDesc||'').length + '/155 chars'"></p>
                </div>
                <div class="border border-gray-200 rounded-lg p-3 bg-gray-50" x-show="title || seoTitle">
                    <p class="text-blue-600 text-sm font-medium line-clamp-1" x-text="seoTitle || title"></p>
                    <p class="text-green-700 text-xs mt-0.5">nkhoj.com/posts/{{ $post->slug }}</p>
                    <p class="text-gray-600 text-xs mt-1 line-clamp-2" x-text="seoDesc || excerpt || 'Meta description...'"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-4">अपडेट गर्नुहोस्</h3>
            <form method="POST" action="/dashboard/posts/{{ $post->id }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <input type="hidden" name="title"     :value="title">
                <input type="hidden" name="excerpt"   :value="excerpt">
                <input type="hidden" name="body"      :value="body">
                <input type="hidden" name="seo_title" :value="seoTitle">
                <input type="hidden" name="seo_desc"  :value="seoDesc">
                <input type="hidden" name="tags"      :value="tags">

                <div class="space-y-3">
                    {{-- Thumbnail --}}
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Thumbnail</label>
                        <div class="mt-1 space-y-2">
                            <div x-show="thumbPreview || thumbnailUrl" class="rounded-lg overflow-hidden bg-gray-100 mb-2" style="aspect-ratio:16/9">
                                <img :src="thumbPreview || thumbnailUrl" class="w-full h-full object-cover">
                            </div>
                            <input type="file" name="thumbnail" accept="image/*"
                                @change="previewThumb($event)"
                                class="w-full text-xs text-gray-500 border border-gray-200 rounded-lg px-2 py-1.5 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100">
                            <input type="text" name="thumbnail_url" x-model="thumbnailUrl"
                                placeholder="https://... (वा URL)"
                                class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Category</label>
                        <select name="category_id" class="mt-1 w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $post->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name_ne ?? $cat->name_en }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tags --}}
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tags</label>
                        <input type="text" x-model="tags"
                            placeholder="Nepal, Politics, Budget..."
                            class="mt-1 w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <div class="mt-1 flex flex-wrap gap-1">
                            @foreach($allTags->take(10) as $tag)
                            <button type="button" @click="addTag('{{ $tag->name_en }}')"
                                class="text-xs px-2 py-0.5 bg-gray-100 hover:bg-brand-50 hover:text-brand-600 text-gray-500 rounded-full transition-colors">
                                +{{ $tag->name_en }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</label>
                        <select name="status" class="mt-1 w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="draft"     {{ $post->status === 'draft'     ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ $post->status === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="archived"  {{ $post->status === 'archived'  ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors">
                        Save Changes
                    </button>
                    <a href="/posts/{{ $post->slug }}" class="block text-center text-sm text-gray-400 hover:text-gray-600">← View Post</a>
                    <a href="/dashboard" class="block text-center text-sm text-gray-400 hover:text-gray-600">Cancel</a>
                </div>
            </form>
        </div>

        {{-- AI Tools --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-1 flex items-center gap-2">🤖 AI Assistant</h3>
            <p class="text-xs text-gray-400 mb-3">Powered by OmniRoute · Claude</p>
            <div class="space-y-2">
                <button @click="aiSuggestSeo()" :disabled="!title || aiLoading"
                    class="w-full py-2 text-sm font-medium text-brand-600 bg-brand-50 hover:bg-brand-100 rounded-lg transition-colors disabled:opacity-50">
                    ✨ SEO + Meta
                </button>
                <button @click="aiTags()" :disabled="!title || aiLoading"
                    class="w-full py-2 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors disabled:opacity-50">
                    🏷️ Suggest Tags
                </button>
                <button @click="aiImprove()" :disabled="!body || aiLoading"
                    class="w-full py-2 text-sm font-medium text-orange-600 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors disabled:opacity-50">
                    ✏️ Improve
                </button>
            </div>
            <div x-show="aiLoading" class="mt-3 text-xs text-center text-gray-400 animate-pulse">AI thinking...</div>
            <div x-show="aiResult" x-cloak class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                <p class="text-xs font-medium text-gray-500 mb-1.5">AI Suggestion:</p>
                <div class="text-sm text-gray-800 whitespace-pre-wrap max-h-48 overflow-y-auto" x-text="aiResult"></div>
                <button @click="applyResult()" class="mt-2 text-xs font-semibold text-brand-600 hover:text-brand-700">Apply →</button>
            </div>
            <div x-show="aiError" x-cloak class="mt-3 text-xs text-red-500 p-2 bg-red-50 rounded-lg" x-text="aiError"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function articleEditor(initTitle='', initExcerpt='', initBody='', initSeoTitle='', initSeoDesc='', initTags='', initThumb='') {
    return {
        title: initTitle, excerpt: initExcerpt, body: initBody,
        seoTitle: initSeoTitle, seoDesc: initSeoDesc,
        tags: initTags,
        thumbnailUrl: initThumb, thumbPreview: '',
        aiResult: '', aiError: '', aiLoading: false, lastAction: '',

        previewThumb(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = ev => { this.thumbPreview = ev.target.result; this.thumbnailUrl = ''; };
            reader.readAsDataURL(file);
        },

        addTag(name) {
            const parts = this.tags.split(',').map(t => t.trim()).filter(Boolean);
            if (!parts.includes(name)) parts.push(name);
            this.tags = parts.join(', ');
        },

        async callAI(action, extra = {}) {
            this.aiLoading = true; this.aiResult = ''; this.aiError = '';
            this.lastAction = action;
            try {
                const res = await fetch('/api/ai/author', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ action, topic: this.title, content: this.body, ...extra })
                });
                const data = await res.json();
                if (data.error) { this.aiError = data.error; return; }
                this.aiResult = typeof data.result === 'string' ? data.result : JSON.stringify(data.result, null, 2);
            } catch(e) { this.aiError = 'AI unavailable'; }
            finally { this.aiLoading = false; }
        },

        aiSuggestSeo() { this.callAI('seo'); },
        aiTags()       { this.callAI('tags'); },
        aiImprove()    { this.callAI('improve'); },

        applyResult() {
            if (this.lastAction === 'seo') {
                try {
                    const p = JSON.parse(this.aiResult);
                    if (p.title) this.seoTitle = p.title;
                    if (p.meta)  this.seoDesc  = p.meta;
                } catch { this.seoTitle = this.aiResult.substring(0, 60); }
            } else if (this.lastAction === 'improve') {
                this.body = this.aiResult;
                const ed = window.__richEditors && window.__richEditors['body-editor'];
                if (ed) ed.value = this.body;
            } else if (this.lastAction === 'tags') {
                const suggested = this.aiResult.split(/[,\n]/).map(t => t.trim().replace(/^#/, '')).filter(Boolean).join(', ');
                this.tags = suggested;
            }
            this.aiResult = '';
        },
    }
}
</script>
@include('partials.tinymce', ['editorId' => 'body-editor', 'height' => 480])
@endpush
@endsection
