<div x-data="{
        activeTab: '/',
        loading: false,
        async switchTab(url, el) {
            if (this.activeTab === url) return;
            this.activeTab = url;
            this.loading = true;
            try {
                const res = await fetch(url + (url.includes('?') ? '&' : '?') + 'ajax=1', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const html = await res.text();
                window.dispatchEvent(new CustomEvent('tab-switched', { detail: { tab: url, html } }));
                window.scrollTo({ top: document.getElementById('posts-feed').offsetTop - 120, behavior: 'smooth' });
            } catch(e) {
                window.location.href = url;
            }
            this.loading = false;
        }
    }"
    class="relative">

    <div class="flex items-center gap-1.5 overflow-x-auto py-2 mb-6 border-b border-gray-200 dark:border-gray-700 scrollbar-hide">
        <button @click="switchTab('/', $el)"
            :class="activeTab === '/' ? 'bg-brand-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
            class="whitespace-nowrap px-4 py-1.5 text-sm font-semibold rounded-full transition-colors">
            सम्पूर्ण
        </button>
        @foreach(\App\Models\Blog\Category::orderBy('sort_order')->get() as $cat)
        <button @click="switchTab('/category/{{ $cat->slug }}', $el)"
            :class="activeTab === '/category/{{ $cat->slug }}' ? 'bg-brand-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
            class="whitespace-nowrap px-4 py-1.5 text-sm font-medium rounded-full transition-colors font-nepali">
            {{ $cat->name_ne ?? $cat->name_en }}
        </button>
        @endforeach
    </div>

    <div x-show="loading" x-cloak class="absolute top-0 left-0 right-0 h-0.5 bg-gray-100 dark:bg-gray-700 overflow-hidden rounded">
        <div class="h-full bg-brand-500 animate-pulse w-2/3"></div>
    </div>
</div>
