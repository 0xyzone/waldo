<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeSocketIdHeader
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $socketId = $request->header('X-Socket-ID');

        // Pusher/Reverb socket IDs must follow the format: {integer}.{integer}
        // If the client sent 'undefined', null, empty string, or invalid text, remove it.
        if ($socketId !== null && ! preg_match('/^\d+\.\d+$/', (string) $socketId)) {
            $request->headers->remove('X-Socket-ID');
        }

        return $next($request);
    }
}
