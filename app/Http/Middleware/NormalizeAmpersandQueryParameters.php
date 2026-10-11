<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeAmpersandQueryParameters
{
    /**
     * Handle an incoming request.
     *
     * Cleans up HTML-entity-escaped query parameters (such as `&amp;signature=...`)
     * that occur when URLs are copied or rendered through email clients or logs.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $queryString = (string) $request->server->get('QUERY_STRING', '');

        if (str_contains($queryString, '&amp;') || str_contains($queryString, 'amp;')) {
            $cleanQueryString = str_replace('&amp;', '&', $queryString);
            $request->server->set('QUERY_STRING', $cleanQueryString);

            if ($request->server->has('REQUEST_URI')) {
                $request->server->set(
                    'REQUEST_URI',
                    str_replace('&amp;', '&', (string) $request->server->get('REQUEST_URI'))
                );
            }

            parse_str($cleanQueryString, $newQuery);

            // Clean any remaining keys starting with amp;
            $finalQuery = [];
            foreach ($newQuery as $k => $v) {
                $finalKey = str_starts_with((string) $k, 'amp;') ? substr((string) $k, 4) : (string) $k;
                $finalQuery[$finalKey] = $v;
            }

            $request->query->replace($finalQuery);
        }

        return $next($request);
    }
}
