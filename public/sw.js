const CACHE_NAME = 'kamkaj-pwa-v1.0.0';
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/favicon.ico',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/icons/apple-touch-icon.png',
    '/icons/icon-maskable-192x192.png',
    '/icons/icon-maskable-512x512.png',
    '/icons/favicon-32x32.png',
    '/icons/favicon-16x16.png'
];

// Install: Cache critical shell assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => {
            return self.skipWaiting();
        })
    );
});

// Activate: Clean up previous cache versions
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME && name.startsWith('kamkaj-pwa-')) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => {
            return self.clients.claim();
        })
    );
});

// Fetch event
self.addEventListener('fetch', (event) => {
    const req = event.request;
    const url = new URL(req.url);

    // Only handle HTTP/HTTPS GET requests from same origin or trusted CDNs
    if (req.method !== 'GET') {
        return;
    }

    // Ignore Livewire dynamic calls, debugging tools, and WebSocket connections
    if (
        url.pathname.startsWith('/livewire/') ||
        url.pathname.startsWith('/telescope') ||
        url.pathname.startsWith('/horizon') ||
        url.pathname.includes('/reverb') ||
        url.pathname.includes('/broadcasting/auth')
    ) {
        return;
    }

    // Navigation requests (HTML documents) - Network First with Offline Fallback
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req)
                .then((networkResponse) => {
                    return networkResponse;
                })
                .catch(async () => {
                    const cache = await caches.open(CACHE_NAME);
                    const cachedOffline = await cache.match(OFFLINE_URL);
                    return cachedOffline || Response.error();
                })
        );
        return;
    }

    // Static assets (images, fonts, css, js) - Stale-While-Revalidate / Cache First
    const isStaticAsset = (
        url.pathname.match(/\.(css|js|woff2?|ttf|eot|svg|png|jpg|jpeg|gif|webp|ico)$/i) ||
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/fonts/')
    );

    if (isStaticAsset) {
        event.respondWith(
            caches.match(req).then((cachedResponse) => {
                if (cachedResponse) {
                    // Update cache in the background (stale-while-revalidate)
                    fetch(req).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(req, networkResponse);
                            });
                        }
                    }).catch(() => {
                        // Network error in background revalidation, ignore
                    });
                    return cachedResponse;
                }

                // If not cached, fetch from network and cache
                return fetch(req).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(req, responseToCache);
                        });
                    }
                    return networkResponse;
                }).catch(() => {
                    return Response.error();
                });
            })
        );
    }
});
