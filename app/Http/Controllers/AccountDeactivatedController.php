<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountDeactivatedController extends Controller
{
    /**
     * Display the deactivated account landing page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // If the logged in user is already active, redirect directly to dashboard
        if ($user && (bool) ($user->is_active ?? true)) {
            return redirect()->route('filament.kamkaj.pages.dashboard');
        }

        // Locate administrator contact (Mr. Sumin Shrestha)
        $adminUser = User::query()
            ->where('name', 'like', '%Sumin Shrestha%')
            ->orWhere('email', 'sumnsth@gmail.com')
            ->first();

        return view('auth.deactivated', [
            'adminUser' => $adminUser,
            'currentUser' => $user,
        ]);
    }

    /**
     * Check if the account has been reactivated.
     */
    public function check(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('filament.kamkaj.auth.login');
        }

        $freshUser = $user->fresh();

        if ($freshUser && (bool) ($freshUser->is_active ?? true)) {
            return redirect()->route('filament.kamkaj.pages.dashboard');
        }

        return redirect()->route('account.deactivated')->with('still_inactive', true);
    }
}
