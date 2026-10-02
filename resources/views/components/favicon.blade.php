@php
    $isSecure = request()->isSecure();
    $httpsFavicon = asset('img/logo.ico');
    $httpFavicon = asset('img/logo-http.ico');
    $initialFavicon = $isSecure ? $httpsFavicon : $httpFavicon;
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
        var isHttps = window.location.protocol === 'https:';
        var targetFavicon = isHttps ? '{{ $httpsFavicon }}' : '{{ $httpFavicon }}';
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
