<div
    x-data="{
        canInstall: false,
        isInstalled: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
        isIOS: /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream,
        showIosModal: false,
        init() {
            if (this.isInstalled) {
                this.canInstall = false;
                return;
            }

            if (window.deferredPWAInstallPrompt) {
                this.canInstall = true;
            }

            window.addEventListener('pwa-prompt-available', () => {
                if (!this.isInstalled) {
                    this.canInstall = true;
                }
            });

            window.addEventListener('pwa-installed', () => {
                this.isInstalled = true;
                this.canInstall = false;
            });

            // On iOS Safari, show install option if not already standalone
            if (this.isIOS && !this.isInstalled) {
                this.canInstall = true;
            }
        },
        async triggerInstall() {
            if (window.deferredPWAInstallPrompt) {
                const promptEvent = window.deferredPWAInstallPrompt;
                promptEvent.prompt();
                const { outcome } = await promptEvent.userChoice;
                if (outcome === 'accepted') {
                    this.isInstalled = true;
                    this.canInstall = false;
                }
                window.deferredPWAInstallPrompt = null;
            } else if (this.isIOS) {
                this.showIosModal = true;
            }
        }
    }"
    x-show="canInstall && !isInstalled"
    x-cloak
    class="relative inline-flex items-center"
>
    <!-- Install Action Button -->
    <button
        type="button"
        @click="triggerInstall()"
        title="Install Kamkaj as App"
        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 active:bg-amber-500/25 border border-amber-500/30 rounded-lg transition-all shadow-xs focus:outline-hidden"
    >
        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
        <span class="hidden sm:inline">Install App</span>
    </button>

    <!-- iOS Instructions Modal -->
    <div
        x-show="showIosModal"
        x-transition.opacity
        @click.outside="showIosModal = false"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
    >
        <div
            @click.stop
            class="relative w-full max-w-sm p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl text-center"
        >
            <div class="w-12 h-12 mx-auto mb-4 bg-amber-500/15 border border-amber-500/30 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
            </div>

            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">
                Install Kamkaj on iOS
            </h3>

            <p class="text-xs text-slate-600 dark:text-slate-300 mb-4 leading-relaxed">
                To install this app on your iPhone or iPad, tap the <span class="font-semibold text-slate-900 dark:text-white">Share</span> button in Safari's toolbar, scroll down, and select <span class="font-semibold text-slate-900 dark:text-white">"Add to Home Screen"</span>.
            </p>

            <button
                type="button"
                @click="showIosModal = false"
                class="w-full py-2 px-4 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-500 rounded-lg transition-colors"
            >
                Got It
            </button>
        </div>
    </div>
</div>
