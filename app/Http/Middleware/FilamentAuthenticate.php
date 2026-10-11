<?php

namespace App\Http\Middleware;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as BaseAuthenticate;

class FilamentAuthenticate extends BaseAuthenticate
{
    /**
     * Authenticate the incoming request.
     *
     * If an unauthenticated user arrives via a valid cryptographically signed
     * email verification link, automatically log them in as the target user so
     * email verification completes seamlessly without forcing them to log in first.
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check() && $request->routeIs('filament.*.auth.email-verification.verify')) {
            if ($request->hasValidSignature()) {
                $user = User::find($request->route('id'));
                if ($user) {
                    $guard->login($user);
                }
            }
        }

        parent::authenticate($request, $guards);
    }
}
