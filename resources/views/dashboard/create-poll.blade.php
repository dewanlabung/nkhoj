@extends('layouts.app')
@section('title', 'Add Poll')

@section('content')
<div x-data="pollForm()" x-init="init()" class="max-w-3xl mx-auto">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="/dashboard/posts" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="/dashboard" class="hover:text-brand-500">Home</a>
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="/dashboard/posts" class="hover:text-brand-500">Posts</a>
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-700 dark:text-gray-300 font-medium">Add Poll</span>
        </nav>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Add Poll</h1>

    <form method="POST" action="/dashboard/posts" enctype="multipart/form-data" id="poll-form"
          @submit="prepareSubmit">
        @csrf
        <input type="hidden" name="post_format" value="poll">
        <input type="hidden" name="status" x-ref="statusInput" value="draft">
        <input type="hidden" name="tags" x-ref="tagsHidden">
        <input type="hidden" name="poll_questions" x-ref="questionsHidden">

        <div class="space-y-5">

            {{-- ── Image ────────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4">Image</h2>

                <div class="flex justify-center mb-4">
                    <label class="relative cursor-pointer group">
                        <div class="w-48 h-36 border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-xl flex items-center justify-center overflow-hidden bg-gray-50 dark:bg-gray-700 hover:border-brand-400 transition-colors">
                            <img x-show="thumbPreview" :src="thumbPreview" class="w-full h-full object-cover rounded-xl">
                            <div x-show="!thumbPreview" class="flex flex-col items-center gap-2 text-gray-400">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-xs">Click to upload</span>
                            </div>
                            <div x-show="thumbPreview" class="absolute top-2 right-2 w-7 h-7 bg-white/80 rounded-full flex items-center justify-center shadow">
                                <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </div>
                        </div>
                        <input type="file" name="thumbnail" accept="image/*" class="hidden" @change="previewThumb">
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">External Image URL</label>
                    <input type="url" name="thumbnail_url" placeholder="External Image URL"
                           class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-300 focus:border-brand-400 outline-none">
                </div>
            </div>

            {{-- ── Settings ─────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Category <span class="text-red-400">*</span></label>
                        <select name="category_id" required
                                class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-300 outline-none">
                            <option value="">Select a category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @php
                    $toggles = [
                        ['name' => 'is_featured',      'label' => 'Add to Featured'],
                        ['name' => 'is_breaking',      'label' => 'Add to Breaking News'],
                    ];
                    @endphp
                    @foreach($toggles as $t)
                    <div class="flex items-center justify-between py-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t['label'] }}</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="{{ $t['name'] }}" value="0">
                            <input type="checkbox" name="{{ $t['name'] }}" value="1" class="sr-only peer" {{ old($t['name']) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-checked:bg-brand-500 rounded-full transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-transform peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ── General ──────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4">General</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Title <span class="text-red-400">*</span></label>
                        <input type="text" name="title" required placeholder="Title"
                               value="{{ old('title') }}"
                               x-model="titleVal" @input="autoSlug"
                               class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-300 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Slug</label>
                        <input type="text" name="slug" placeholder="Slug" x-model="slugVal"
                               class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-300 outline-none font-mono text-xs">
                        <p class="text-xs text-gray-400 mt-1">If you leave it blank, it will be generated automatically.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Summary &amp; Description</label>
                        <textarea name="excerpt" rows="3" placeholder="Summary & Description"
                                  class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-300 outline-none resize-none">{{ old('excerpt') }}</textarea>
                    </div>

                    {{-- Tags chip input --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Tags</label>
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 min-h-[42px] flex flex-wrap gap-1.5 items-center cursor-text"
                             @click="$refs.tagInput.focus()">
                            <template x-for="tag in tags" :key="tag">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 rounded-full text-xs font-medium">
                                    <span x-text="tag"></span>
                                    <button type="button" @click.stop="removeTag(tag)" class="hover:text-red-500 leading-none">×</button>
                                </span>
                            </template>
                            <input type="text" x-ref="tagInput" placeholder="Type tag and hit enter"
                                   @keydown.enter.prevent="addTag($event.target.value); $event.target.value=''"
                                   @keydown.comma.prevent="addTag($event.target.value); $event.target.value=''"
                                   class="flex-1 min-w-[120px] text-sm outline-none bg-transparent text-gray-700 dark:text-gray-200 placeholder-gray-400">
                        </div>
                    </div>

                    {{-- Vote Permission --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Vote Permission</label>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="vote_permission" value="registered"
                                       class="w-4 h-4 accent-brand-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Only Registered Users Can Vote</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="vote_permission" value="all" checked
                                       class="w-4 h-4 accent-brand-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">All Users Can Vote</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Meta Options (collapsible) ───────────────────── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden"
                 x-data="{open: false}">
                <button type="button" @click="open=!open"
                        class="w-full flex items-center justify-between px-6 py-4 text-left">
                    <h2 class="font-bold text-gray-900 dark:text-white">Meta Options <span class="font-normal text-gray-400 text-sm">(Optional)</span></h2>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="px-6 pb-5 space-y-4 border-t border-gray-100 dark:border-gray-700 pt-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Meta Title</label>
                        <input type="text" name="seo_title" placeholder="Meta Title" value="{{ old('seo_title') }}"
                               class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-300 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                        <textarea name="seo_desc" rows="3" placeholder="Meta Description"
                                  class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-300 outline-none resize-none">{{ old('seo_desc') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Meta Keywords</label>
                        <input type="text" name="seo_keywords" placeholder="Enter keywords separated by commas" value="{{ old('seo_keywords') }}"
                               class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-300 outline-none">
                    </div>
                </div>
            </div>

            {{-- ── Questions ────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4">Questions</h2>

                <div class="space-y-4">
                    <template x-for="(q, qi) in questions" :key="q.id">
                        <div class="border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden">
                            {{-- Question header --}}
                            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="flex-1 text-sm font-semibold text-gray-700 dark:text-gray-300">Question <span x-text="qi+1"></span></span>
                                <button type="button" @click="removeQuestion(qi)"
                                        x-show="questions.length > 1"
                                        class="text-gray-400 hover:text-red-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                <button type="button" @click="q.open = !q.open" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4 transition-transform" :class="q.open ? '' : '-rotate-90'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>

                            <div x-show="q.open" class="p-4 space-y-4">
                                {{-- Question title --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Title</label>
                                    <input type="text" x-model="q.title" placeholder="Title"
                                           class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-300 outline-none">
                                </div>

                                {{-- Answers --}}
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Answers</h3>
                                        {{-- Answer format selector --}}
                                        <div class="flex items-center gap-1 border border-gray-200 dark:border-gray-600 rounded-lg p-0.5">
                                            <button type="button" @click="q.answerFormat='grid3'"
                                                    :class="q.answerFormat==='grid3' ? 'bg-brand-500 text-white' : 'text-gray-400 hover:text-gray-600'"
                                                    class="w-8 h-7 flex items-center justify-center rounded-md transition-colors" title="3-column grid">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4 4h4v4H4zm6 0h4v4h-4zm6 0h4v4h-4zM4 10h4v4H4zm6 0h4v4h-4zm6 0h4v4h-4zM4 16h4v4H4zm6 0h4v4h-4zm6 0h4v4h-4z"/></svg>
                                            </button>
                                            <button type="button" @click="q.answerFormat='grid2'"
                                                    :class="q.answerFormat==='grid2' ? 'bg-brand-500 text-white' : 'text-gray-400 hover:text-gray-600'"
                                                    class="w-8 h-7 flex items-center justify-center rounded-md transition-colors" title="2-column grid">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4 4h7v7H4zm9 0h7v7h-7zM4 13h7v7H4zm9 0h7v7h-7z"/></svg>
                                            </button>
                                            <button type="button" @click="q.answerFormat='list'"
                                                    :class="q.answerFormat==='list' ? 'bg-brand-500 text-white' : 'text-gray-400 hover:text-gray-600'"
                                                    class="w-8 h-7 flex items-center justify-center rounded-md transition-colors" title="List">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Answer cards --}}
                                    <div :class="{
                                        'grid grid-cols-3 gap-2': q.answerFormat === 'grid3',
                                        'grid grid-cols-2 gap-2': q.answerFormat === 'grid2',
                                        'space-y-2': q.answerFormat === 'list'
                                    }">
                                        <template x-for="(ans, ai) in q.answers" :key="ans.id">
                                            <div class="relative border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-700">
                                                <button type="button" @click="removeAnswer(qi, ai)"
                                                        class="absolute top-1.5 right-1.5 w-5 h-5 flex items-center justify-center rounded-full bg-white dark:bg-gray-600 text-gray-400 hover:text-red-500 shadow-sm text-sm leading-none z-10">×</button>

                                                {{-- Answer image (for grid formats) --}}
                                                <label x-show="q.answerFormat !== 'list'" class="block cursor-pointer">
                                                    <div class="h-20 bg-gray-100 dark:bg-gray-600 flex items-center justify-center overflow-hidden">
                                                        <img x-show="ans.imagePreview" :src="ans.imagePreview" class="w-full h-full object-cover">
                                                        <svg x-show="!ans.imagePreview" class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    </div>
                                                    <input type="file" accept="image/*" class="hidden"
                                                           @change="previewAnswerImage($event, qi, ai)">
                                                </label>

                                                {{-- Answer text --}}
                                                <div class="p-2">
                                                    <input type="text" x-model="ans.text" placeholder="Answer Text..."
                                                           class="w-full text-xs bg-transparent outline-none text-gray-700 dark:text-gray-200 placeholder-gray-400">
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <button type="button" @click="addAnswer(qi)"
                                            class="mt-3 w-full py-2 border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-lg text-sm text-brand-500 hover:border-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/10 transition-colors font-medium">
                                        + Add Answer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Add Question --}}
                <button type="button" @click="addQuestion()"
                        class="mt-4 w-full py-3 border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-500 hover:border-brand-400 hover:text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-900/10 transition-colors font-medium">
                    + Add Question
                </button>
            </div>

            {{-- ── Action buttons ───────────────────────────────── --}}
            <div class="flex items-center justify-end gap-3 pb-6">
                <button type="button" @click="submitAs('draft')"
                        class="px-6 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Save as Draft
                </button>
                <button type="button" @click="submitAs('published')"
                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    Add Post
                </button>
            </div>

        </div>
    </form>
</div>

<script>
function pollForm() {
    return {
        titleVal: '',
        slugVal: '',
        tags: [],
        thumbPreview: null,
        questions: [],
        nextId: 1,

        init() {
            this.addQuestion();
        },

        autoSlug() {
            this.slugVal = this.titleVal
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
        },

        previewThumb(e) {
            const file = e.target.files[0];
            if (file) this.thumbPreview = URL.createObjectURL(file);
        },

        addTag(val) {
            const t = val.trim().replace(/,$/, '');
            if (t && !this.tags.includes(t)) this.tags.push(t);
        },

        removeTag(tag) {
            this.tags = this.tags.filter(t => t !== tag);
        },

        addQuestion() {
            this.questions.push({
                id: this.nextId++,
                title: '',
                open: true,
                answerFormat: 'grid2',
                answers: [
                    { id: Date.now(), text: '', imagePreview: null },
                    { id: Date.now()+1, text: '', imagePreview: null },
                ]
            });
        },

        removeQuestion(i) {
            this.questions.splice(i, 1);
        },

        addAnswer(qi) {
            this.questions[qi].answers.push({ id: Date.now(), text: '', imagePreview: null });
        },

        removeAnswer(qi, ai) {
            this.questions[qi].answers.splice(ai, 1);
        },

        previewAnswerImage(e, qi, ai) {
            const file = e.target.files[0];
            if (file) this.questions[qi].answers[ai].imagePreview = URL.createObjectURL(file);
        },

        submitAs(status) {
            this.$refs.statusInput.value = status;
            this.$refs.tagsHidden.value = this.tags.join(', ');
            this.$refs.questionsHidden.value = JSON.stringify(
                this.questions.map(q => ({
                    title: q.title,
                    answerFormat: q.answerFormat,
                    answers: q.answers.map(a => ({ text: a.text }))
                }))
            );
            document.getElementById('poll-form').submit();
        },

        prepareSubmit(e) {
            this.$refs.tagsHidden.value = this.tags.join(', ');
            this.$refs.questionsHidden.value = JSON.stringify(
                this.questions.map(q => ({
                    title: q.title,
                    answerFormat: q.answerFormat,
                    answers: q.answers.map(a => ({ text: a.text }))
                }))
            );
        }
    }
}
</script>
@endsection
