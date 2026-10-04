<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserLastActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            // Throttled update: update last_active_at if null or if more than 1 minute has elapsed
            if (! $user->last_active_at || $user->last_active_at->diffInMinutes(now()) >= 1) {
                $user->timestamps = false;
                $user->updateQuietly(['last_active_at' => now()]);
            }
        }

        return $next($request);
    }
}
