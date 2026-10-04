{{-- PWA Meta Tags & Manifest --}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Kamkaj">
<link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icons/favicon-16x16.png') }}">
<meta name="theme-color" media="(prefers-color-scheme: light)" content="#d97706">
<meta name="theme-color" media="(prefers-color-scheme: dark)" content="#0f172a">
<meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') ?: env('VAPID_PUBLIC_KEY', '') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    (function () {
        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js', { scope: '/' })
                    .then(function (registration) {
                        registration.update().catch(function() {});
                        // Check for updates
                        registration.addEventListener('updatefound', function () {
                            var installingWorker = registration.installing;
                            if (installingWorker) {
                                installingWorker.addEventListener('statechange', function () {
                                    if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                        // New SW version available
                                        console.log('[PWA] New version of Kamkaj available.');
                                    }
                                });
                            }
                        });
                    })
                    .catch(function (error) {
                        console.warn('[PWA] Service Worker registration failed:', error);
                    });
            });
        }

        // Global state for PWA install prompt
        window.deferredPWAInstallPrompt = null;
        window.isPWAInstalled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

        window.addEventListener('beforeinstallprompt', function (e) {
            // Prevent Chrome mini-infobar
            e.preventDefault();
            window.deferredPWAInstallPrompt = e;
            window.dispatchEvent(new CustomEvent('pwa-prompt-available'));
        });

        window.addEventListener('appinstalled', function () {
            window.deferredPWAInstallPrompt = null;
            window.isPWAInstalled = true;
            window.dispatchEvent(new CustomEvent('pwa-installed'));
            console.log('[PWA] Kamkaj was successfully installed.');
        });
    })();
</script>
