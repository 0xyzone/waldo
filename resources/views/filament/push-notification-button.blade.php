<div
    x-data="pushNotificationComponent()"
    x-show="supported"
    x-cloak
    class="relative inline-flex items-center"
>
    <!-- Trigger Button -->
    <button
        type="button"
        @click="showMenu = !showMenu"
        :title="isSubscribed ? 'Push Notifications: Active (Click to manage)' : 'Push Notifications: Inactive (Click to enable)'"
        class="relative inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors focus:outline-hidden"
    >
        <!-- Bell Icon -->
        <template x-if="isSubscribed">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
        </template>
        <template x-if="!isSubscribed && permission === 'denied'">
            <svg class="w-5 h-5 text-rose-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.143 17.082a24.248 24.248 0 0 0 3.844.148m-5.46-1.57A8.967 8.967 0 0 1 6 9.75V9a6 6 0 0 1 6-6c1.332 0 2.56.435 3.55 1.174M18 9.75V9a6 6 0 0 0-.683-2.793M3 3l18 18" />
            </svg>
        </template>
        <template x-if="!isSubscribed && permission !== 'denied'">
            <svg class="w-5 h-5 text-slate-400 dark:text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
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

    <!-- Dropdown Popover Menu -->
    <div
        x-show="showMenu"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="showMenu = false"
        x-cloak
        class="absolute right-0 top-full mt-2 w-72 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl z-50 text-left text-xs"
    >
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <span
                    class="w-2.5 h-2.5 rounded-full"
                    :class="isSubscribed ? 'bg-emerald-500' : (permission === 'denied' ? 'bg-rose-500' : 'bg-slate-400')"
                ></span>
                <span class="font-semibold text-slate-800 dark:text-slate-100 text-sm">Push Notifications</span>
            </div>
            <span
                class="px-1.5 py-0.5 text-[10px] font-semibold uppercase rounded tracking-wider"
                :class="isSubscribed ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : (permission === 'denied' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400' : 'bg-slate-500/10 text-slate-500')"
                x-text="isSubscribed ? 'Active' : (permission === 'denied' ? 'Blocked' : 'Off')"
            ></span>
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
                ⚠️ Notifications are blocked in your browser settings. Click the lock/tune icon in your browser URL bar to allow notifications for this site.
            </div>
        </template>

        <!-- Action Buttons -->
        <div class="space-y-2">
            <template x-if="!isSubscribed && permission !== 'denied'">
                <button
                    type="button"
                    @click="subscribe()"
                    :disabled="isLoading"
                    class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-500 active:bg-amber-700 rounded-lg shadow-sm transition-all disabled:opacity-50"
                >
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
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
                        class="w-full inline-flex items-center justify-center gap-2 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 active:bg-amber-500/25 border border-amber-500/30 rounded-lg transition-all disabled:opacity-50"
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
                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-[11px] font-medium text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors disabled:opacity-50"
                    >
                        <span>Disable on this device</span>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    (function () {
        function initPushNotificationComponent() {
            if (!window.Alpine) return;

            window.Alpine.data('pushNotificationComponent', () => ({
                supported: ('serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window),
                permission: typeof Notification !== 'undefined' ? Notification.permission : 'default',
                isSubscribed: false,
                isLoading: false,
                showMenu: false,
                statusText: '',
                vapidKey: @json(config('webpush.vapid.public_key')),
                csrfToken: @json(csrf_token()),

                init() {
                    if (!this.supported) {
                        return;
                    }

                    this.checkSubscription();

                    window.addEventListener('focus', () => {
                        if (typeof Notification !== 'undefined') {
                            this.permission = Notification.permission;
                        }
                    });
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

                getVapidKey() {
                    if (this.vapidKey && String(this.vapidKey).trim().length > 0) {
                        return String(this.vapidKey).trim();
                    }
                    const el = document.querySelector('meta[name="vapid-public-key"]');
                    return el ? (el.getAttribute('content') || '').trim() : '';
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

                        const vapidKey = this.getVapidKey();
                        if (!vapidKey) {
                            this.statusText = 'VAPID public key is missing.';
                            this.isLoading = false;
                            return;
                        }

                        let sub = await reg.pushManager.getSubscription();
                        if (!sub) {
                            sub = await reg.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: this.urlBase64ToUint8Array(vapidKey)
                            });
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
