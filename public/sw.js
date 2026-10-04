const CACHE_NAME = 'kamkaj-pwa-v1.3.0';
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

// Sound preference persistence using IndexedDB (accessible in Service Worker)
let swSoundEnabled = false;

function getSoundPreference() {
    return new Promise((resolve) => {
        try {
            const req = indexedDB.open('kamkaj_push_settings', 1);
            req.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains('settings')) {
                    db.createObjectStore('settings');
                }
            };
            req.onsuccess = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains('settings')) {
                    resolve(swSoundEnabled);
                    return;
                }
                const tx = db.transaction('settings', 'readonly');
                const store = tx.objectStore('settings');
                const getReq = store.get('sound_enabled');
                getReq.onsuccess = () => {
                    const result = !!getReq.result;
                    swSoundEnabled = result;
                    resolve(result);
                };
                getReq.onerror = () => resolve(swSoundEnabled);
            };
            req.onerror = () => resolve(swSoundEnabled);
        } catch (e) {
            resolve(swSoundEnabled);
        }
    });
}

function saveSoundPreference(enabled) {
    swSoundEnabled = !!enabled;
    try {
        const req = indexedDB.open('kamkaj_push_settings', 1);
        req.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('settings')) {
                db.createObjectStore('settings');
            }
        };
        req.onsuccess = (e) => {
            const db = e.target.result;
            const tx = db.transaction('settings', 'readwrite');
            const store = tx.objectStore('settings');
            store.put(!!enabled, 'sound_enabled');
        };
    } catch (e) {}
}

// Listen for messages from client (sound toggling)
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SET_PUSH_SOUND') {
        saveSoundPreference(event.data.enabled);
    }
});

// Push notification received in background
self.addEventListener('push', (event) => {
    let payload = {};
    if (event.data) {
        try {
            payload = event.data.json();
        } catch (e) {
            payload = {
                title: 'Kamkaj Notification',
                body: event.data.text()
            };
        }
    }

    const title = payload.title || 'Kamkaj Notification';
    const actionUrl = payload.action_url || (payload.data && payload.data.url) || '/kamkaj';

    event.waitUntil((async () => {
        let isSoundEnabled = false;
        try {
            isSoundEnabled = await Promise.race([
                getSoundPreference(),
                new Promise((r) => setTimeout(() => r(swSoundEnabled), 250))
            ]);
        } catch (e) {
            isSoundEnabled = swSoundEnabled;
        }

        // STRICT ENFORCEMENT: sound is ONLY allowed if user has explicitly turned it ON
        const isSoundOn = (isSoundEnabled === true);

        const options = {
            body: payload.body || '',
            icon: payload.icon || '/icons/icon-192x192.png',
            badge: payload.badge || '/icons/favicon-32x32.png',
            image: payload.image || undefined,
            tag: payload.tag || 'kamkaj-notification',
            requireInteraction: payload.requireInteraction || false,
            data: Object.assign({ url: actionUrl }, payload.data || {}),
            actions: Array.isArray(payload.actions) ? payload.actions : []
        };

        if (isSoundOn) {
            options.silent = false;
            options.renotify = payload.renotify !== false;
            options.vibrate = payload.vibrate || [100, 50, 100];
            if (payload.sound) {
                options.sound = payload.sound;
            }
        } else {
            // Absolute silence: strict silent flag, no vibration, no sound property, no renotify sound trigger
            options.silent = true;
            options.renotify = false;
            delete options.vibrate;
            delete options.sound;
        }

        return self.registration.showNotification(title, options);
    })());
});

// User clicked on a notification
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.url) 
        ? event.notification.data.url 
        : '/kamkaj';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // If already open, focus it and navigate
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url.includes('/kamkaj') && 'focus' in client) {
                    if ('navigate' in client && !client.url.includes(targetUrl)) {
                        client.navigate(targetUrl);
                    }
                    return client.focus();
                }
            }

            // Otherwise open a new window
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

