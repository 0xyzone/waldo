<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest as BaseEmailVerificationRequest;

class CustomEmailVerificationRequest extends BaseEmailVerificationRequest
{
    protected ?User $targetUser = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * Validates that the target user exists and that the email verification hash
     * matches the user's current email. Because the route has already passed
     * cryptographic URL signature validation (HMAC SHA-256 with APP_KEY), this
     * permits verification even if an administrator is reviewing/testing or
     * if the user is resolving verification.
     */
    public function authorize(): bool
    {
        $id = (string) $this->route('id');
        $hash = (string) $this->route('hash');

        $targetUser = User::find($id);

        if (! $targetUser) {
            return false;
        }

        if (! hash_equals(sha1($targetUser->getEmailForVerification()), $hash)) {
            return false;
        }

        $this->targetUser = $targetUser;

        return true;
    }

    /**
     * Fulfill the email verification request.
     */
    public function fulfill(): void
    {
        $targetUser = $this->targetUser ?? User::find($this->route('id')) ?? $this->user();

        if ($targetUser && ! $targetUser->hasVerifiedEmail()) {
            $targetUser->markEmailAsVerified();

            event(new Verified($targetUser));
        }

        Notification::make()
            ->title('Email verified successfully')
            ->body("Email for {$targetUser->name} ({$targetUser->email}) has been verified.")
            ->success()
            ->send();
    }
}
