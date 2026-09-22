<div class="flex items-center gap-2 mb-4">
    <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-widest">नवीनतम</h2>
    <div class="flex-1 h-px bg-gray-100 dark:bg-gray-700"></div>
    <span class="text-xs text-gray-400">Latest posts</span>
</div>

<div id="posts-feed"
    x-data="{
        page: {{ $posts->currentPage() }},
        hasMore: {{ $posts->hasMorePages() ? 'true' : 'false' }},
        tab: '/',
        loading: false,
        async loadMore() {
            if (this.loading || !this.hasMore) return;
            this.loading = true;
            this.page++;
            const sep = this.tab.includes('?') ? '&' : '?';
            const res = await fetch(this.tab + sep + 'ajax=1&page=' + this.page);
            const html = await res.text();
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            const newCards = Array.from(tmp.querySelectorAll('article'));
            const grid = this.$el.querySelector('.grid');
            if (grid && newCards.length) newCards.forEach(c => grid.appendChild(c));
            const meta = tmp.querySelector('[data-feed-meta]');
            this.hasMore = meta?.dataset.hasMore === 'true';
            this.loading = false;
        },
        afterTabSwitch(html) {
            this.$el.innerHTML = html;
            const meta = this.$el.querySelector('[data-feed-meta]');
            this.page = parseInt(meta?.dataset.currentPage || '1');
            this.hasMore = meta?.dataset.hasMore === 'true';
        }
    }"
    @tab-switched.window="tab = $event.detail.tab; afterTabSwitch($event.detail.html)">
    @include('partials.posts-feed')

    <div x-intersect.threshold.10="loadMore" class="h-4 mt-2"></div>
    <div x-show="loading" class="flex justify-center py-4">
        <div class="w-5 h-5 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
    </div>
</div>
