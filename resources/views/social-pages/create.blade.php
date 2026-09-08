@extends('layouts.app')

@section('title', 'Create your Page')

@section('content')
<div class="min-h-screen bg-gray-50"
     x-data="pageCreate()"
     x-init="init()">

    {{-- Mobile header --}}
    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
        <button @click="step > 1 ? step-- : window.history.back()" class="p-2 -ml-2 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <h1 class="font-bold text-gray-900 text-lg" x-text="stepTitles[step - 1]"></h1>
    </div>

    {{-- Progress bar --}}
    <div class="w-full h-1 bg-gray-100">
        <div class="h-1 bg-blue-600 transition-all duration-300" :style="`width: ${(step / totalSteps) * 100}%`"></div>
    </div>

    {{-- Steps container --}}
    <div class="max-w-xl mx-auto px-4 py-8">

        {{-- STEP 1: Page name --}}
        <div x-show="step === 1" x-transition>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">What do you want to name this Page?</h2>
            <p class="text-gray-500 mb-6">The Page name should be the name of your business, personal brand or organization.</p>
            <input
                type="text"
                x-model="form.name"
                @input="generateSlug"
                placeholder="Page name"
                class="w-full border border-gray-300 rounded-xl px-4 py-3 text-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                maxlength="150"
            >
            <p class="text-gray-400 text-sm mt-2">dewanlabung.com.np/pages/<span class="text-gray-700 font-medium" x-text="form.slug || 'your-page-name'"></span></p>
        </div>

        {{-- STEP 2: Categories --}}
        <div x-show="step === 2" x-transition>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">What category best describes the Page you want to create?</h2>
            <p class="text-gray-500 mb-1">A category will help people find this Page in search results. You can add up to 3.</p>

            {{-- Selected chips --}}
            <div class="flex flex-wrap gap-2 border border-gray-300 rounded-xl px-3 py-3 mb-4 min-h-[52px]">
                <template x-for="(cat, i) in form.categories" :key="i">
                    <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                        <span x-text="cat"></span>
                        <button type="button" @click="removeCategory(i)" class="text-blue-500 hover:text-blue-700 ml-1">&times;</button>
                    </span>
                </template>
                <template x-if="form.categories.length < 3">
                    <button type="button" @click="showCatInput = !showCatInput"
                            class="inline-flex items-center gap-1 border border-gray-300 text-gray-600 text-sm px-3 py-1 rounded-full hover:bg-gray-50">
                        Add category <span class="text-lg leading-none">+</span>
                    </button>
                </template>
            </div>

            {{-- Category search input --}}
            <div x-show="showCatInput" class="mb-4">
                <input type="text" x-model="catSearch" @input="filterCategories"
                       placeholder="Search categories..."
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500">
                <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="cat in filteredCategories" :key="cat">
                        <button type="button" @click="addCategory(cat)"
                                class="text-sm px-3 py-1.5 rounded-full border border-gray-300 hover:bg-gray-100 transition"
                                :class="form.categories.includes(cat) ? 'bg-blue-50 border-blue-400 text-blue-700' : 'text-gray-700'"
                                x-text="cat">
                        </button>
                    </template>
                </div>
            </div>

            <p class="text-sm font-semibold text-gray-700 mb-3">Popular categories:</p>
            <div class="flex flex-wrap gap-2">
                <template x-for="cat in popularCategories" :key="cat">
                    <button type="button" @click="addCategory(cat)"
                            class="text-sm px-4 py-2 rounded-full border border-gray-300 hover:bg-gray-100 transition font-medium"
                            :class="form.categories.includes(cat) ? 'bg-blue-50 border-blue-400 text-blue-700' : 'text-gray-700'"
                            x-text="cat">
                    </button>
                </template>
            </div>
        </div>

        {{-- STEP 3: Bio --}}
        <div x-show="step === 3" x-transition>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Add a bio</h2>
            <p class="text-gray-500 mb-6">Tell people what your Page is about. You can edit this later.</p>
            <textarea
                x-model="form.bio"
                placeholder="Describe your page..."
                rows="4"
                maxlength="500"
                class="w-full border border-gray-300 rounded-xl px-4 py-3 text-base focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 resize-none"
            ></textarea>
            <p class="text-gray-400 text-sm mt-1 text-right" x-text="`${form.bio.length} / 500`"></p>
        </div>

        {{-- STEP 4: Page type --}}
        <div x-show="step === 4" x-transition>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Page type</h2>
            <p class="text-gray-500 mb-2">Select the primary use for your Page</p>
            <p class="text-gray-400 text-sm mb-6">Get customized features as they become available and be recommended to the right audience.</p>

            <div class="space-y-3 mb-4">
                <label class="flex items-center justify-between border rounded-2xl px-5 py-4 cursor-pointer transition"
                       :class="form.page_type === 'business' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">Business</p>
                            <p class="text-gray-500 text-sm">Best for businesses that sell products or services, non-profits and other organizations.</p>
                        </div>
                    </div>
                    <input type="radio" x-model="form.page_type" value="business" class="w-5 h-5 text-blue-600 flex-shrink-0">
                </label>

                <label class="flex items-center justify-between border rounded-2xl px-5 py-4 cursor-pointer transition"
                       :class="form.page_type === 'creator' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">Creator</p>
                            <p class="text-gray-500 text-sm">Best for content creators, public figures and others who seek to build an audience.</p>
                        </div>
                    </div>
                    <input type="radio" x-model="form.page_type" value="creator" class="w-5 h-5 text-blue-600 flex-shrink-0">
                </label>
            </div>
            <p class="text-gray-400 text-xs text-center">You can change this setting later.</p>
        </div>

        {{-- Bottom progress dots --}}
        <div class="flex justify-center gap-2 mt-8 mb-6">
            <template x-for="n in totalSteps" :key="n">
                <div class="h-1 rounded-full transition-all duration-300"
                     :class="n === step ? 'w-8 bg-blue-600' : n < step ? 'w-4 bg-blue-300' : 'w-4 bg-gray-200'">
                </div>
            </template>
        </div>

        {{-- Next / Submit button --}}
        <div class="mt-2">
            <template x-if="step < totalSteps">
                <button @click="nextStep"
                        :disabled="!canProceed"
                        class="w-full py-4 rounded-xl font-bold text-lg transition"
                        :class="canProceed ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
                    Next
                </button>
            </template>
            <template x-if="step === totalSteps">
                <form method="POST" action="/pages" @submit.prevent="submitForm">
                    @csrf
                    <input type="hidden" name="name" :value="form.name">
                    <input type="hidden" name="page_type" :value="form.page_type">
                    <template x-for="(cat, i) in form.categories" :key="i">
                        <input type="hidden" :name="`categories[${i}]`" :value="cat">
                    </template>
                    <input type="hidden" name="bio" :value="form.bio">
                    <button type="submit"
                            :disabled="!form.page_type"
                            class="w-full py-4 rounded-xl font-bold text-lg transition"
                            :class="form.page_type ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
                        Create Page
                    </button>
                </form>
            </template>
        </div>

        <p x-show="step === totalSteps" class="text-center text-xs text-gray-400 mt-3">
            By creating a Page, you agree to our <a href="#" class="text-blue-500">Pages terms</a>.
        </p>
    </div>
</div>

<script>
function pageCreate() {
    return {
        step: 1,
        totalSteps: 4,
        stepTitles: ['Page name', 'Add Page categories', 'Add a bio', 'Page type'],
        showCatInput: false,
        catSearch: '',
        form: {
            name: '',
            slug: '',
            categories: [],
            bio: '',
            page_type: '',
        },
        popularCategories: [
            'Personal blog', 'News & Media', 'Political organization', 'Journalist',
            'Product/service', 'Art', 'Musician/band', 'Shopping & retail',
            'Politician', 'Community organization', 'Entertainment',
        ],
        filteredCategories: [],

        init() {
            this.filteredCategories = [...this.popularCategories];
        },

        get canProceed() {
            if (this.step === 1) return this.form.name.trim().length >= 2;
            if (this.step === 2) return this.form.categories.length > 0;
            if (this.step === 3) return true; // bio optional
            return !!this.form.page_type;
        },

        nextStep() {
            if (this.canProceed && this.step < this.totalSteps) this.step++;
        },

        generateSlug() {
            this.form.slug = this.form.name
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        },

        addCategory(cat) {
            if (!this.form.categories.includes(cat) && this.form.categories.length < 3) {
                this.form.categories.push(cat);
            }
            this.showCatInput = false;
            this.catSearch = '';
        },

        removeCategory(i) {
            this.form.categories.splice(i, 1);
        },

        filterCategories() {
            const q = this.catSearch.toLowerCase();
            this.filteredCategories = this.popularCategories.filter(c => c.toLowerCase().includes(q));
            if (this.catSearch && !this.popularCategories.find(c => c.toLowerCase() === q)) {
                this.filteredCategories.unshift(this.catSearch);
            }
        },

        submitForm() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/pages';
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
            form.appendChild(csrf);
            const fields = {
                name: this.form.name,
                page_type: this.form.page_type,
                bio: this.form.bio,
            };
            Object.entries(fields).forEach(([k, v]) => {
                const el = document.createElement('input');
                el.type = 'hidden'; el.name = k; el.value = v;
                form.appendChild(el);
            });
            this.form.categories.forEach((cat, i) => {
                const el = document.createElement('input');
                el.type = 'hidden'; el.name = `categories[${i}]`; el.value = cat;
                form.appendChild(el);
            });
            document.body.appendChild(form);
            form.submit();
        },
    };
}
</script>
@endsection
