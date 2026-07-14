<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            $bypassRoutes = [
                'filament.kamkaj.pages.change-initial-password',
                'filament.kamkaj.auth.email-verification.prompt',
                'filament.kamkaj.auth.email-verification.verify',
                'filament.kamkaj.auth.logout',
            ];

            if (
                $request->routeIs($bypassRoutes) ||
                $request->routeIs('livewire.*') ||
                $request->is('livewire/*') ||
                $request->is('filament/*')
            ) {
                return $next($request);
            }

            return redirect()->route('filament.kamkaj.pages.change-initial-password');
        }

        return $next($request);
    }
}
