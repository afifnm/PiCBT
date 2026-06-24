const CACHE_NAME = 'picbt-v1';
const STATIC_ASSETS = [
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

// Install: cache static assets
self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

// Activate: clean old caches
self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Fetch: network-first for HTML (navigation), cache-first for assets
self.addEventListener('fetch', (e) => {
    const { request } = e;
    const url = new URL(request.url);

    // Only handle same-origin requests
    if (url.origin !== self.location.origin) return;

    // Navigation requests (HTML pages): network-first, no cache storage
    if (request.mode === 'navigate') {
        e.respondWith(
            fetch(request).catch(() =>
                caches.match('/') // offline fallback
            )
        );
        return;
    }

    // Built assets (Vite hashed files): cache-first
    if (url.pathname.startsWith('/build/')) {
        e.respondWith(
            caches.match(request).then(cached => {
                if (cached) return cached;
                return fetch(request).then(res => {
                    const clone = res.clone();
                    caches.open(CACHE_NAME).then(c => c.put(request, clone));
                    return res;
                });
            })
        );
        return;
    }

    // Icons & manifest: cache-first
    if (url.pathname.startsWith('/icons/') || url.pathname === '/manifest.json') {
        e.respondWith(
            caches.match(request).then(cached => cached || fetch(request))
        );
        return;
    }

    // Everything else: network-first
    e.respondWith(fetch(request));
});
