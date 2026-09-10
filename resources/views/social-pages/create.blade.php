@extends('layouts.app')

@section('title', 'Create your Page')

@section('content')
<div class="min-h-screen bg-gray-50"
     x-data="pageCreate()"
     x-init="init()">

    {{-- Header --}}
    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
        <button @click="step > 1 ? step-- : window.history.back()" class="p-2 -ml-2 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <div>
            <h1 class="font-bold text-gray-900 text-base leading-none" x-text="stepTitles[step - 1]"></h1>
            <p class="text-xs text-gray-400 mt-0.5" x-text="`Step ${step} of ${totalSteps}`"></p>
        </div>
    </div>

    {{-- Progress bar --}}
    <div class="w-full h-1 bg-gray-100">
        <div class="h-1 bg-blue-600 transition-all duration-300" :style="`width: ${(step / totalSteps) * 100}%`"></div>
    </div>

    {{-- Global error --}}
    <div x-show="errorMsg" x-cloak class="max-w-xl mx-auto px-4 pt-4">
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm" x-text="errorMsg"></div>
    </div>

    <div class="max-w-xl mx-auto px-4 py-8">

        {{-- STEP 1: Page type --}}
        <div x-show="step === 1" x-transition>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">What type of page?</h2>
            <p class="text-gray-500 text-sm mb-6">Choose the type that best fits your goal.</p>

            <div class="space-y-4">
                <label class="block cursor-pointer" :class="form.page_type === 'business' ? 'ring-2 ring-blue-500 rounded-2xl' : ''">
                    <input type="radio" x-model="form.page_type" value="business" class="sr-only">
                    <div class="border-2 rounded-2xl p-5 transition"
                         :class="form.page_type === 'business' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300 bg-white'">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0"
                                 style="background: linear-gradient(135deg,#1a73e8,#0d47a1)">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-gray-900 text-base">Business</p>
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                         :class="form.page_type === 'business' ? 'border-blue-600 bg-blue-600' : 'border-gray-300'">
                                        <div x-show="form.page_type === 'business'" class="w-2 h-2 bg-white rounded-full"></div>
                                    </div>
                                </div>
                                <p class="text-gray-500 text-sm mt-1">For companies, brands, local businesses, restaurants, shops, and organizations.</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">📍 Location & hours</span>
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">🗺️ Map listing</span>
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">📞 Contact info</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </label>

                <label class="block cursor-pointer" :class="form.page_type === 'creator' ? 'ring-2 ring-purple-500 rounded-2xl' : ''">
                    <input type="radio" x-model="form.page_type" value="creator" class="sr-only">
                    <div class="border-2 rounded-2xl p-5 transition"
                         :class="form.page_type === 'creator' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300 bg-white'">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0"
                                 style="background: linear-gradient(135deg,#e91e8c,#9c27b0)">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.362a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-gray-900 text-base">Creator</p>
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                         :class="form.page_type === 'creator' ? 'border-purple-600 bg-purple-600' : 'border-gray-300'">
                                        <div x-show="form.page_type === 'creator'" class="w-2 h-2 bg-white rounded-full"></div>
                                    </div>
                                </div>
                                <p class="text-gray-500 text-sm mt-1">For content creators, public figures, artists, influencers, and personalities.</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-medium">✨ Creator tools</span>
                                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-medium">🔗 Social links</span>
                                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-medium">📊 Audience insights</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
            <p class="text-gray-400 text-xs text-center mt-4">You can change this setting later in Page Settings.</p>
        </div>

        {{-- STEP 2: Page name + username --}}
        <div x-show="step === 2" x-transition>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Name your Page</h2>
            <p class="text-gray-500 text-sm mb-6"
               x-text="form.page_type === 'business'
                   ? 'Use your business name as it appears on your signage or website.'
                   : 'Use your real name or the name your audience knows you by.'">
            </p>

            <div class="space-y-4">
                {{-- Display name --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Display Name *</label>
                    <input
                        type="text"
                        x-model="form.name"
                        @input="onNameInput"
                        :placeholder="form.page_type === 'business' ? 'e.g. Dewan Café Kathmandu' : 'e.g. Dewan Labung'"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 text-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        maxlength="150"
                        autofocus
                    >
                </div>

                {{-- Username handle --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Page Username <span class="text-gray-400 font-normal normal-case">(your unique @handle)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-base select-none">@</span>
                        <input
                            type="text"
                            x-model="form.username"
                            @input="checkUsername"
                            placeholder="yourpage"
                            class="w-full border rounded-xl pl-8 pr-10 py-3 text-base focus:outline-none transition"
                            :class="{
                                'border-green-400 focus:border-green-500 focus:ring-2 focus:ring-green-100': usernameStatus === 'available',
                                'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100': usernameStatus === 'taken',
                                'border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100': usernameStatus === '' || usernameStatus === 'checking'
                            }"
                            maxlength="60"
                        >
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm">
                            <template x-if="usernameStatus === 'checking'">
                                <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </template>
                            <template x-if="usernameStatus === 'available'">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="usernameStatus === 'taken'">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </template>
                        </span>
                    </div>
                    <p class="text-xs mt-1"
                       :class="usernameStatus === 'available' ? 'text-green-600' : usernameStatus === 'taken' ? 'text-red-500' : 'text-gray-400'"
                       x-text="usernameMsg || 'Letters, numbers, dots, dashes, underscores only.'">
                    </p>
                    <p class="text-gray-400 text-sm mt-2">
                        dewanlabung.com.np/pages/<span class="text-gray-700 font-medium" x-text="form.slug || 'your-page-name'"></span>
                    </p>
                </div>
            </div>
        </div>

        {{-- STEP 3: Categories --}}
        <div x-show="step === 3" x-transition>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Add categories</h2>
            <p class="text-gray-500 text-sm mb-1"
               x-text="form.page_type === 'business'
                   ? 'Choose what best describes your business.'
                   : 'Choose your content niche.'">
            </p>
            <p class="text-gray-400 text-xs mb-4">You can add up to 3.</p>

            <div class="flex flex-wrap gap-2 border border-gray-300 rounded-xl px-3 py-3 mb-4 min-h-[52px]">
                <template x-for="(cat, i) in form.categories" :key="i">
                    <span class="inline-flex items-center gap-1 text-sm font-medium px-3 py-1 rounded-full"
                          :class="form.page_type === 'creator' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'">
                        <span x-text="cat"></span>
                        <button type="button" @click="removeCategory(i)" class="ml-1 hover:opacity-70">&times;</button>
                    </span>
                </template>
                <template x-if="form.categories.length < 3">
                    <button type="button" @click="showCatInput = !showCatInput"
                            class="inline-flex items-center gap-1 border border-gray-300 text-gray-600 text-sm px-3 py-1 rounded-full hover:bg-gray-50">
                        Add category <span class="text-lg leading-none">+</span>
                    </button>
                </template>
            </div>

            <div x-show="showCatInput" class="mb-4">
                <input type="text" x-model="catSearch" @input="filterCategories"
                       placeholder="Search categories..."
                       class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500">
                <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="cat in filteredCategories" :key="cat">
                        <button type="button" @click="addCategory(cat)"
                                class="text-sm px-3 py-1.5 rounded-full border transition"
                                :class="form.categories.includes(cat)
                                    ? (form.page_type === 'creator' ? 'bg-purple-50 border-purple-400 text-purple-700' : 'bg-blue-50 border-blue-400 text-blue-700')
                                    : 'border-gray-300 text-gray-700 hover:bg-gray-100'"
                                x-text="cat">
                        </button>
                    </template>
                </div>
            </div>

            <p class="text-sm font-semibold text-gray-700 mb-3">Popular:</p>
            <div class="flex flex-wrap gap-2">
                <template x-for="cat in currentPopularCategories" :key="cat">
                    <button type="button" @click="addCategory(cat)"
                            class="text-sm px-4 py-2 rounded-full border font-medium transition"
                            :class="form.categories.includes(cat)
                                ? (form.page_type === 'creator' ? 'bg-purple-50 border-purple-400 text-purple-700' : 'bg-blue-50 border-blue-400 text-blue-700')
                                : 'border-gray-300 text-gray-700 hover:bg-gray-100'"
                            x-text="cat">
                    </button>
                </template>
            </div>
        </div>

        {{-- STEP 4: Bio + social links --}}
        <div x-show="step === 4" x-transition>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2"
                x-text="form.page_type === 'business' ? 'Describe your business' : 'Add a bio'"></h2>
            <p class="text-gray-500 text-sm mb-6"
               x-text="form.page_type === 'business'
                   ? 'Tell people what you offer. This also helps with SEO.'
                   : 'Tell your audience what content you create.'">
            </p>
            <textarea
                x-model="form.bio"
                :placeholder="form.page_type === 'business' ? 'e.g. We offer premium quality products and services in Kathmandu...' : 'e.g. I create tech tutorials and lifestyle content for Nepali audiences...'"
                rows="4"
                maxlength="500"
                class="w-full border border-gray-300 rounded-xl px-4 py-3 text-base focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 resize-none"
            ></textarea>
            <p class="text-gray-400 text-sm mt-1 text-right" x-text="`${form.bio.length} / 500`"></p>

            {{-- Business extra fields --}}
            <template x-if="form.page_type === 'business'">
                <div class="mt-5 space-y-3">
                    <p class="text-sm font-semibold text-gray-700">Business details <span class="text-gray-400 font-normal">(optional)</span></p>
                    <input type="text" x-model="form.website" placeholder="🌐 Website URL (https://...)"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400">
                    <input type="text" x-model="form.location" placeholder="📍 Location (City, Country)"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400">
                    <input type="text" x-model="form.phone" placeholder="📞 Phone number"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400">
                    <input type="email" x-model="form.email" placeholder="✉️ Business email"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400">
                    <p class="text-sm font-semibold text-gray-700 pt-1">Social links <span class="text-gray-400 font-normal">(optional)</span></p>
                    <input type="text" x-model="form.social_links.facebook" placeholder="🔵 Facebook page URL"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400">
                    <input type="text" x-model="form.social_links.instagram" placeholder="📸 Instagram profile URL"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400">
                    <input type="text" x-model="form.social_links.tiktok" placeholder="🎵 TikTok profile URL"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-gray-400">
                </div>
            </template>

            {{-- Creator social links --}}
            <template x-if="form.page_type === 'creator'">
                <div class="mt-5 space-y-3">
                    <p class="text-sm font-semibold text-gray-700">Your social links <span class="text-gray-400 font-normal">(optional — helps fans find you)</span></p>
                    <input type="text" x-model="form.social_links.youtube" placeholder="▶️ YouTube channel URL"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-red-400">
                    <input type="text" x-model="form.social_links.tiktok" placeholder="🎵 TikTok profile URL"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-gray-400">
                    <input type="text" x-model="form.social_links.instagram" placeholder="📸 Instagram profile URL"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-pink-400">
                    <input type="text" x-model="form.social_links.twitter" placeholder="𝕏 Twitter/X profile URL"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400">
                    <input type="text" x-model="form.social_links.facebook" placeholder="🔵 Facebook profile URL"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400">
                    <input type="text" x-model="form.website" placeholder="🔗 Main link (website, Linktree...)"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-purple-400">
                </div>
            </template>
        </div>

        {{-- Progress dots --}}
        <div class="flex justify-center gap-2 mt-8 mb-6">
            <template x-for="n in totalSteps" :key="n">
                <div class="h-1.5 rounded-full transition-all duration-300"
                     :class="n === step ? 'w-8 bg-blue-600' : n < step ? 'w-4 bg-blue-300' : 'w-4 bg-gray-200'">
                </div>
            </template>
        </div>

        {{-- Next / Submit --}}
        <div class="mt-2">
            <template x-if="step < totalSteps">
                <button @click="nextStep"
                        :disabled="!canProceed"
                        class="w-full py-4 rounded-xl font-bold text-lg transition"
                        :class="canProceed ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm' : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
                    Next
                </button>
            </template>
            <template x-if="step === totalSteps">
                <button type="button" @click="submitForm"
                        :disabled="submitting"
                        class="w-full py-4 rounded-xl font-bold text-lg transition"
                        :class="submitting ? 'bg-blue-400 text-white cursor-wait' : 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm'">
                    <span x-text="submitting ? 'Creating...' : 'Create Page'"></span>
                </button>
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
        stepTitles: ['Choose page type', 'Name your page', 'Add categories', 'Add description & links'],
        showCatInput: false,
        catSearch: '',
        submitting: false,
        errorMsg: '',
        usernameStatus: '', // '', 'checking', 'available', 'taken'
        usernameMsg: '',
        usernameTimer: null,
        form: {
            name: '',
            slug: '',
            username: '',
            categories: [],
            bio: '',
            page_type: '',
            website: '',
            location: '',
            phone: '',
            email: '',
            social_links: { facebook: '', instagram: '', twitter: '', tiktok: '', youtube: '' },
        },

        businessCategories: [
            'Restaurant', 'Retail Shop', 'Hotel & Accommodation', 'Health & Medical',
            'Education & Training', 'Real Estate', 'Financial Services', 'Legal Services',
            'Construction', 'Technology', 'Non-profit', 'Government', 'Automotive',
            'Beauty & Spa', 'Sports & Fitness', 'Photography', 'Event Planning',
        ],
        creatorCategories: [
            'Content Creator', 'Music', 'Comedy', 'Travel', 'Food & Cooking',
            'Fashion & Style', 'Tech & Gaming', 'Fitness & Health', 'Art & Design',
            'Education', 'News & Politics', 'Sports', 'Personal Blog', 'Journalist',
        ],
        filteredCategories: [],

        get currentPopularCategories() {
            return this.form.page_type === 'creator' ? this.creatorCategories : this.businessCategories;
        },

        init() {
            this.filteredCategories = [...this.businessCategories];
        },

        get canProceed() {
            if (this.step === 1) return !!this.form.page_type;
            if (this.step === 2) {
                if (this.form.name.trim().length < 2) return false;
                if (this.usernameStatus === 'taken') return false;
                if (this.usernameStatus === 'checking') return false;
                return true;
            }
            if (this.step === 3) return this.form.categories.length > 0;
            return true;
        },

        nextStep() {
            if (this.canProceed && this.step < this.totalSteps) {
                this.step++;
                if (this.step === 3) {
                    this.filteredCategories = this.form.page_type === 'creator'
                        ? [...this.creatorCategories]
                        : [...this.businessCategories];
                }
            }
        },

        onNameInput() {
            // auto-generate slug
            this.form.slug = this.form.name
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
            // auto-suggest username from name if empty
            if (!this.form.username) {
                const suggested = this.form.name
                    .toLowerCase()
                    .replace(/[^a-z0-9._-]/g, '')
                    .slice(0, 60);
                if (suggested.length >= 3) {
                    this.form.username = suggested;
                    this.checkUsername();
                }
            }
        },

        checkUsername() {
            clearTimeout(this.usernameTimer);
            const u = this.form.username.trim().toLowerCase();
            if (!u) { this.usernameStatus = ''; this.usernameMsg = ''; return; }
            if (u.length < 3) { this.usernameStatus = 'taken'; this.usernameMsg = 'At least 3 characters.'; return; }
            if (!/^[a-z0-9._-]+$/.test(u)) { this.usernameStatus = 'taken'; this.usernameMsg = 'Only letters, numbers, dots, dashes, underscores.'; return; }
            this.usernameStatus = 'checking';
            this.usernameMsg = 'Checking...';
            this.usernameTimer = setTimeout(() => {
                fetch(`/api/pages/check-username?username=${encodeURIComponent(u)}`, {
                    headers: { 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => {
                    this.usernameStatus = data.available ? 'available' : 'taken';
                    this.usernameMsg = data.message;
                }).catch(() => {
                    this.usernameStatus = '';
                    this.usernameMsg = '';
                });
            }, 400);
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
            const all = this.form.page_type === 'creator' ? this.creatorCategories : this.businessCategories;
            const q = this.catSearch.toLowerCase();
            this.filteredCategories = all.filter(c => c.toLowerCase().includes(q));
            if (this.catSearch && !all.find(c => c.toLowerCase() === q)) {
                this.filteredCategories.unshift(this.catSearch);
            }
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errorMsg = '';

            // Filter empty social links
            const socialLinks = {};
            Object.entries(this.form.social_links).forEach(([k, v]) => {
                if (v && v.trim()) socialLinks[k] = v.trim();
            });

            const payload = {
                name:         this.form.name,
                username:     this.form.username || null,
                page_type:    this.form.page_type,
                categories:   this.form.categories,
                bio:          this.form.bio,
                website:      this.form.website,
                location:     this.form.location,
                phone:        this.form.phone,
                social_links: Object.keys(socialLinks).length ? socialLinks : null,
            };

            try {
                const res = await fetch('/pages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    window.location.href = data.redirect;
                } else if (res.status === 422 && data.errors) {
                    const first = Object.values(data.errors)[0];
                    this.errorMsg = Array.isArray(first) ? first[0] : first;
                    this.submitting = false;
                } else {
                    this.errorMsg = data.message || 'Something went wrong. Please try again.';
                    this.submitting = false;
                }
            } catch (e) {
                this.errorMsg = 'Network error. Please check your connection and try again.';
                this.submitting = false;
            }
        },
    };
}
</script>
@endsection
