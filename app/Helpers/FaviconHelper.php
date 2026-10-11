<?php

namespace App\Helpers;

class FaviconHelper
{
    /**
     * Determine if current request or environment is local/development.
     */
    public static function isLocal(): bool
    {
        // If app environment is explicitly not production
        if (! app()->isProduction() || app()->isLocal()) {
            return true;
        }

        $host = strtolower(request()->getHost());

        $localHosts = [
            'localhost',
            '127.0.0.1',
            'waldo',
            '::1',
        ];

        if (in_array($host, $localHosts, true)) {
            return true;
        }

        if (str_ends_with($host, '.test') ||
            str_ends_with($host, '.local') ||
            str_ends_with($host, '.site') ||
            str_starts_with($host, '192.168.') ||
            str_starts_with($host, '10.') ||
            str_starts_with($host, '172.16.')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get the favicon .ico URL.
     * Blue (logo-http.ico) for local/development; Orange (logo.ico) for production.
     */
    public static function getFaviconUrl(): string
    {
        return static::isLocal()
            ? asset('img/logo-http.ico')
            : asset('img/logo.ico');
    }

    /**
     * Get the favicon .png URL.
     * Blue (logo-http.png) for local/development; Orange (logo.png) for production.
     */
    public static function getFaviconPngUrl(): string
    {
        return static::isLocal()
            ? asset('img/logo-http.png')
            : asset('img/logo.png');
    }
}
