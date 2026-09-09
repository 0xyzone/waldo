@php
    $isSecure = request()->isSecure();
    $httpsFavicon = asset('img/logo.ico');
    $httpFavicon = asset('img/logo-http.ico');
    $initialFavicon = $isSecure ? $httpsFavicon : $httpFavicon;
@endphp
<link rel="icon" type="image/x-icon" href="{{ $initialFavicon }}" id="app-favicon">
<link rel="shortcut icon" type="image/x-icon" href="{{ $initialFavicon }}">
<script>
    (function() {
        var isHttps = window.location.protocol === 'https:';
        var targetFavicon = isHttps ? '{{ $httpsFavicon }}' : '{{ $httpFavicon }}';
        var links = document.querySelectorAll("link[rel*='icon']");
        if (links.length > 0) {
            links.forEach(function(l) {
                if (l.href !== targetFavicon) {
                    l.href = targetFavicon;
                }
            });
        }
    })();
</script>
