<div
    x-data="pushNotificationComponent({{ Js::from(config('webpush.vapid.public_key') ?: env('VAPID_PUBLIC_KEY', '')) }})"
    x-show="supported"
    x-cloak
    class="relative inline-flex items-center"
>
    <!-- Trigger Button (Broadcast / Signal Icon to distinguish from in-app notifications bell) -->
    <button
        type="button"
        @click="showMenu = !showMenu"
        :title="isSubscribed ? 'Device Push Alerts: Active (Click to manage)' : 'Device Push Alerts: Inactive (Click to enable)'"
        class="relative inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors focus:outline-hidden"
    >
        <!-- Broadcast Signal Waves Icon (Distinct from Bell) -->
        <template x-if="isSubscribed">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z" />
            </svg>
        </template>
        <template x-if="!isSubscribed && permission === 'denied'">
            <svg class="w-5 h-5 text-rose-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0ZM3 3l18 18" />
            </svg>
        </template>
        <template x-if="!isSubscribed && permission !== 'denied'">
            <svg class="w-5 h-5 text-slate-400 dark:text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z" />
            </svg>
        </template>

        <!-- Status Dot Badge -->
        <span
            class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full"
            :class="{
                'bg-emerald-500 shadow-xs shadow-emerald-500/50': isSubscribed,
                'bg-rose-500': permission === 'denied',
                'bg-amber-500 animate-pulse': !isSubscribed && permission !== 'denied'
            }"
        ></span>
    </button>

    <!-- Teleported Modal (Centered and immune to topbar clipping/transforms) -->
    <template x-teleport="body">
        <div
            x-show="showMenu"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;"
        >
            <!-- Backdrop -->
            <div
                x-show="showMenu"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showMenu = false"
                class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"
            ></div>

            <!-- Modal Content Box -->
            <div
                x-show="showMenu"
                x-transition:enter="transition ease-out duration-200 transform"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150 transform"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                @click.outside="showMenu = false"
                class="relative w-full max-w-sm p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl z-10 text-left text-xs"
            >
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span
                            class="w-2.5 h-2.5 rounded-full shrink-0"
                            :class="isSubscribed ? 'bg-emerald-500' : (permission === 'denied' ? 'bg-rose-500' : 'bg-slate-400')"
                        ></span>
                        <span class="font-semibold text-slate-800 dark:text-slate-100 text-sm">Device Push Alerts</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="px-1.5 py-0.5 text-[10px] font-semibold uppercase rounded tracking-wider"
                            :class="isSubscribed ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : (permission === 'denied' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400' : 'bg-slate-500/10 text-slate-500')"
                            x-text="isSubscribed ? 'Active' : (permission === 'denied' ? 'Blocked' : 'Off')"
                        ></span>
                        <button
                            type="button"
                            @click="showMenu = false"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                            title="Close"
                        >
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3 leading-relaxed">
                    Get instant desktop and mobile OS alerts for chat messages and system notifications even when Kamkaj is closed.
                </p>

                <!-- Status message banner -->
                <template x-if="statusText">
                    <div class="mb-3 p-2 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-300 text-[11px]" x-text="statusText"></div>
                </template>

                <!-- Blocked notice -->
                <template x-if="permission === 'denied'">
                    <div class="mb-3 p-2.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-300 text-[11px]">
                        ⚠️ Notifications are blocked in your browser settings. Click the site settings / lock icon in your browser URL bar to allow notifications for this site.
                    </div>
                </template>

                <!-- Action Buttons -->
                <div class="space-y-2">
                    <template x-if="!isSubscribed && permission !== 'denied'">
                        <button
                            type="button"
                            @click="subscribe()"
                            :disabled="isLoading"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-500 active:bg-amber-700 rounded-lg shadow-sm transition-all disabled:opacity-50 cursor-pointer"
                        >
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z" />
                            </svg>
                            <span x-text="isLoading ? 'Enabling...' : 'Enable on this Device'"></span>
                        </button>
                    </template>

                    <template x-if="isSubscribed">
                        <div class="space-y-2">
                            <button
                                type="button"
                                @click="sendTest()"
                                :disabled="isLoading"
                                class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 active:bg-amber-500/25 border border-amber-500/30 rounded-lg transition-all disabled:opacity-50 cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                </svg>
                                <span x-text="isLoading ? 'Sending...' : 'Send Test Notification'"></span>
                            </button>

                            <button
                                type="button"
                                @click="unsubscribe()"
                                :disabled="isLoading"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-[11px] font-medium text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors disabled:opacity-50 cursor-pointer"
                            >
                                <span>Disable on this device</span>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Sound Control Settings (Muted by default to avoid annoyance) -->
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <template x-if="soundEnabled">
                                <svg class="w-4 h-4 text-amber-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.757 3.63 8.25 4.51 8.25H6.75Z" />
                                </svg>
                            </template>
                            <template x-if="!soundEnabled">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-1.5-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.757 3.63 8.25 4.51 8.25H6.75l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H6.75Z" />
                                </svg>
                            </template>
                            <div>
                                <div class="font-medium text-slate-700 dark:text-slate-200 text-xs">Notification Sound</div>
                                <div class="text-[10px] text-slate-400" x-text="soundEnabled ? 'Alert chime enabled' : 'Muted (Silent by default)'"></div>
                            </div>
                        </div>

                        <button
                            type="button"
                            role="switch"
                            :aria-checked="soundEnabled"
                            @click="toggleSound()"
                            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden"
                            :class="soundEnabled ? 'bg-amber-500' : 'bg-slate-300 dark:bg-slate-700'"
                            :title="soundEnabled ? 'Disable notification sound' : 'Enable notification sound'"
                        >
                            <span
                                class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out"
                                :class="soundEnabled ? 'translate-x-4' : 'translate-x-0'"
                            ></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    (function () {
        function initPushNotificationComponent() {
            if (!window.Alpine) return;

            window.Alpine.data('pushNotificationComponent', (initialVapidKey = '') => ({
                supported: ('serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window),
                permission: typeof Notification !== 'undefined' ? Notification.permission : 'default',
                isSubscribed: false,
                isLoading: false,
                showMenu: false,
                statusText: '',
                soundEnabled: localStorage.getItem('kamkaj_push_sound') === '1', // default false (silent)
                vapidKey: initialVapidKey || @json(config('webpush.vapid.public_key') ?: env('VAPID_PUBLIC_KEY', '')),
                csrfToken: @json(csrf_token()),

                init() {
                    if (!this.supported) {
                        return;
                    }

                    // Synchronize sound preference to Service Worker & IndexedDB
                    this.syncSoundPreference(this.soundEnabled);

                    this.checkSubscription();

                    window.addEventListener('focus', () => {
                        if (typeof Notification !== 'undefined') {
                            this.permission = Notification.permission;
                        }
                    });
                },

                toggleSound() {
                    this.soundEnabled = !this.soundEnabled;
                    localStorage.setItem('kamkaj_push_sound', this.soundEnabled ? '1' : '0');
                    this.syncSoundPreference(this.soundEnabled);
                    if (this.soundEnabled) {
                        this.playChime();
                        this.statusText = 'Notification sound turned ON.';
                    } else {
                        this.statusText = 'Notification sound muted.';
                    }
                    setTimeout(() => { if (this.statusText.includes('sound')) this.statusText = ''; }, 3000);
                },

                syncSoundPreference(enabled) {
                    const isEnabled = !!enabled;

                    // 1. Post to active service worker controller
                    if (navigator.serviceWorker && navigator.serviceWorker.controller) {
                        navigator.serviceWorker.controller.postMessage({
                            type: 'SET_PUSH_SOUND',
                            enabled: isEnabled
                        });
                    }

                    // 2. Also post to ready registration (in case controller was temporarily unattached)
                    if (navigator.serviceWorker && navigator.serviceWorker.ready) {
                        navigator.serviceWorker.ready.then((reg) => {
                            if (reg.active) {
                                reg.active.postMessage({
                                    type: 'SET_PUSH_SOUND',
                                    enabled: isEnabled
                                });
                            }
                        }).catch(() => {});
                    }

                    // 3. Persist to IndexedDB
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
                            if (!db.objectStoreNames.contains('settings')) return;
                            const tx = db.transaction('settings', 'readwrite');
                            const store = tx.objectStore('settings');
                            store.put(isEnabled, 'sound_enabled');
                        };
                    } catch (e) {}

                    // 4. Notify open window components
                    window.dispatchEvent(new CustomEvent('kamkaj-sound-changed', {
                        detail: { enabled: isEnabled }
                    }));
                },

                playChime() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                        osc.frequency.setValueAtTime(880, ctx.currentTime + 0.08);
                        gain.gain.setValueAtTime(0.12, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                        osc.start(ctx.currentTime);
                        osc.stop(ctx.currentTime + 0.3);
                    } catch (e) {}
                },

                async getRegistration() {
                    if (!('serviceWorker' in navigator)) return null;
                    return await navigator.serviceWorker.ready;
                },

                urlBase64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
                    const base64 = (base64String + padding)
                        .replace(/-/g, '+')
                        .replace(/_/g, '/');
                    const rawData = window.atob(base64);
                    const outputArray = new Uint8Array(rawData.length);
                    for (let i = 0; i < rawData.length; ++i) {
                        outputArray[i] = rawData.charCodeAt(i);
                    }
                    return outputArray;
                },

                async getVapidKey() {
                    let key = (this.vapidKey || '').trim();
                    if (key.length > 0) return key;

                    const el = document.querySelector('meta[name="vapid-public-key"]');
                    if (el) {
                        key = (el.getAttribute('content') || '').trim();
                        if (key.length > 0) {
                            this.vapidKey = key;
                            return key;
                        }
                    }

                    // Dynamic fetch fallback from backend
                    try {
                        const res = await fetch('/push-subscriptions/vapid-key');
                        if (res.ok) {
                            const data = await res.json();
                            if (data.publicKey && String(data.publicKey).trim().length > 0) {
                                this.vapidKey = String(data.publicKey).trim();
                                return this.vapidKey;
                            }
                        }
                    } catch (err) {
                        console.warn('[WebPush] Error fetching VAPID key from backend:', err);
                    }

                    return '';
                },

                getCsrfToken() {
                    if (this.csrfToken) return this.csrfToken;
                    const el = document.querySelector('meta[name="csrf-token"]');
                    return el ? el.getAttribute('content') : '';
                },

                async checkSubscription() {
                    try {
                        const reg = await this.getRegistration();
                        if (!reg) return;
                        const sub = await reg.pushManager.getSubscription();
                        this.isSubscribed = !!sub;

                        if (this.isSubscribed && this.permission === 'granted') {
                            this.saveSubscription(sub, false);
                        }
                    } catch (err) {
                        console.warn('[WebPush] Error checking subscription:', err);
                    }
                },

                async subscribe() {
                    this.isLoading = true;
                    this.statusText = '';
                    try {
                        if (!this.supported) {
                            alert('Push notifications are not supported on this browser.');
                            this.isLoading = false;
                            return;
                        }

                        const result = await Notification.requestPermission();
                        this.permission = result;

                        if (result !== 'granted') {
                            this.statusText = 'Permission was denied. Please allow notifications in your browser settings.';
                            this.isLoading = false;
                            return;
                        }

                        const reg = await this.getRegistration();
                        if (!reg) {
                            this.statusText = 'Service Worker not ready yet.';
                            this.isLoading = false;
                            return;
                        }

                        const vapidKey = await this.getVapidKey();
                        if (!vapidKey) {
                            this.statusText = 'VAPID public key is missing.';
                            this.isLoading = false;
                            return;
                        }

                        // Clear any old/mismatched subscription to prevent "push service error"
                        try {
                            const existingSub = await reg.pushManager.getSubscription();
                            if (existingSub) {
                                await existingSub.unsubscribe();
                            }
                        } catch (e) {
                            console.warn('[WebPush] Error unsubscribing previous subscription:', e);
                        }

                        const appServerKey = this.urlBase64ToUint8Array(vapidKey);

                        let sub;
                        try {
                            sub = await reg.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: appServerKey
                            });
                        } catch (subErr) {
                            console.error('[WebPush] PushManager subscribe error:', subErr);
                            const isBrave = (navigator.brave && typeof navigator.brave.isBrave === 'function') || navigator.userAgent.includes('Brave');
                            if (isBrave) {
                                throw new Error('Brave blocked push service. Go to brave://settings/privacy, enable "Use Google services for push messaging", then restart Brave.');
                            }
                            throw subErr;
                        }

                        await this.saveSubscription(sub, true);
                        this.isSubscribed = true;
                        this.statusText = 'Notifications enabled successfully!';
                        setTimeout(() => { this.statusText = ''; }, 4000);
                    } catch (err) {
                        console.error('[WebPush] Failed to subscribe:', err);
                        this.statusText = 'Failed to enable notifications: ' + (err.message || 'Unknown error');
                    } finally {
                        this.isLoading = false;
                    }
                },

                async saveSubscription(sub, notifyUser = false) {
                    const key = sub.getKey ? sub.getKey('p256dh') : null;
                    const token = sub.getKey ? sub.getKey('auth') : null;
                    const contentEncoding = (PushManager.supportedContentEncodings || ['aes128gcm'])[0];

                    const response = await fetch('/push-subscriptions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            endpoint: sub.endpoint,
                            public_key: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
                            auth_token: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
                            content_encoding: contentEncoding
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Server returned ' + response.status);
                    }
                },

                async unsubscribe() {
                    this.isLoading = true;
                    this.statusText = '';
                    try {
                        const reg = await this.getRegistration();
                        if (reg) {
                            const sub = await reg.pushManager.getSubscription();
                            if (sub) {
                                await fetch('/push-subscriptions/delete', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': this.getCsrfToken(),
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ endpoint: sub.endpoint })
                                });
                                await sub.unsubscribe();
                            }
                        }
                        this.isSubscribed = false;
                        this.statusText = 'Notifications disabled on this device.';
                        setTimeout(() => { this.statusText = ''; }, 4000);
                    } catch (err) {
                        console.error('[WebPush] Failed to unsubscribe:', err);
                        this.statusText = 'Error disabling notifications.';
                    } finally {
                        this.isLoading = false;
                    }
                },

                async sendTest() {
                    this.isLoading = true;
                    this.statusText = 'Dispatching test push notification...';
                    try {
                        if (this.soundEnabled) {
                            this.playChime();
                        }

                        const res = await fetch('/push-subscriptions/test', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken(),
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.statusText = 'Test sent! Check your device notifications.';
                        } else {
                            this.statusText = data.message || 'Failed to dispatch test notification.';
                        }
                    } catch (err) {
                        this.statusText = 'Request failed: ' + err.message;
                    } finally {
                        this.isLoading = false;
                        setTimeout(() => { this.statusText = ''; }, 6000);
                    }
                }
            }));
        }

        if (window.Alpine) {
            initPushNotificationComponent();
        } else {
            document.addEventListener('alpine:init', initPushNotificationComponent);
        }
    })();
</script>
