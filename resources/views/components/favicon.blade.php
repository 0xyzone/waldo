@php
    $isLocal = \App\Helpers\FaviconHelper::isLocal();
    $productionFavicon = asset('img/logo.ico');
    $localFavicon = asset('img/logo-http.ico');
    $initialFavicon = $isLocal ? $localFavicon : $productionFavicon;
@endphp
<link rel="icon" type="image/x-icon" href="{{ $initialFavicon }}" id="app-favicon">
<link rel="shortcut icon" type="image/x-icon" href="{{ $initialFavicon }}">
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Kamkaj">
<link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">
<meta name="theme-color" media="(prefers-color-scheme: light)" content="#d97706">
<meta name="theme-color" media="(prefers-color-scheme: dark)" content="#0f172a">
<script>
    (function() {
        var h = window.location.hostname;
        var isLocal = {{ $isLocal ? 'true' : 'false' }} || h === 'localhost' || h === '127.0.0.1' || h === 'waldo' || h.endsWith('.test') || h.endsWith('.local') || h.startsWith('192.168.') || h.startsWith('10.');
        var targetFavicon = isLocal ? '{{ $localFavicon }}' : '{{ $productionFavicon }}';
        var links = document.querySelectorAll("link[rel*='icon']");
        if (links.length > 0) {
            links.forEach(function(l) {
                if (l.href !== targetFavicon && l.id === 'app-favicon') {
                    l.href = targetFavicon;
                }
            });
        }

        if ('serviceWorker' in navigator && !window.__kamkajSWRegistered) {
            window.__kamkajSWRegistered = true;
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function(){});
            });
        }
    })();
</script>
