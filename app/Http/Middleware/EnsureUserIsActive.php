<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! (bool) ($user->is_active ?? true)) {
            $bypassRoutes = [
                'account.deactivated',
                'account.deactivated.check',
                'filament.kamkaj.auth.email-verification.verify',
                'filament.kamkaj.auth.logout',
            ];

            if (
                $request->routeIs($bypassRoutes) ||
                $request->is('account-deactivated*') ||
                $request->is('kamkaj/logout')
            ) {
                return $next($request);
            }

            return redirect()->route('account.deactivated');
        }

        return $next($request);
    }
}
