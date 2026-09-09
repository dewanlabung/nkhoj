const CACHE = 'nkhoj-v1';
const OFFLINE_URL = '/offline.html';

// Cache static assets on install
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE).then(cache =>
            cache.addAll(['/', OFFLINE_URL]).catch(() => {})
        )
    );
    self.skipWaiting();
});

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Network-first for pages, cache-first for static assets
self.addEventListener('fetch', e => {
    const url = new URL(e.request.url);

    // Skip non-GET, cross-origin, and admin/api routes
    if (e.request.method !== 'GET') return;
    if (url.origin !== location.origin) return;
    if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api')) return;

    // Static assets: cache-first
    if (/\.(css|js|woff2?|png|jpg|webp|svg|ico)$/.test(url.pathname)) {
        e.respondWith(
            caches.match(e.request).then(cached =>
                cached || fetch(e.request).then(res => {
                    const clone = res.clone();
                    caches.open(CACHE).then(c => c.put(e.request, clone));
                    return res;
                })
            )
        );
        return;
    }

    // Pages: network-first, fallback to offline page
    e.respondWith(
        fetch(e.request).catch(() =>
            caches.match(e.request).then(cached => cached || caches.match(OFFLINE_URL))
        )
    );
});
