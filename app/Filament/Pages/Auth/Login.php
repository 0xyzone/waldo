<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Schemas\Components\Component;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use SensitiveParameter;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Username, Email, or Phone')
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        $login = trim((string) ($data['email'] ?? ''));

        // Normalize phone number (strip whitespace, hyphens, parentheses)
        $normalizedPhone = preg_replace('/[\s\-()]/', '', $login);

        $user = User::query()
            ->where('email', $login)
            ->orWhere('username', $login)
            ->orWhere('phone', $login)
            ->when(filled($normalizedPhone) && $normalizedPhone !== $login, function ($query) use ($normalizedPhone) {
                $query->orWhere('phone', $normalizedPhone);
            })
            ->first();

        if ($user) {
            return [
                'id' => $user->getAuthIdentifier(),
                'password' => $data['password'],
            ];
        }

        return [
            'email' => $login,
            'password' => $data['password'],
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();
        $authProvider = $authGuard->getProvider();
        $credentials = $this->getCredentialsFromFormData($data);
        $remember = $data['remember'] ?? false;

        $user = $authProvider->retrieveByCredentials($credentials);

        if ((! $user) || (! $authProvider->validateCredentials($user, $credentials))) {
            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        if (! (bool) ($user->is_active ?? true)) {
            $authGuard->login($user, $remember);
            session()->regenerate();

            $this->redirect(route('account.deactivated'));

            return null;
        }

        if (! $authGuard->attemptWhen($credentials, function (Authenticatable $user): bool {
            if (! ($user instanceof FilamentUser)) {
                return true;
            }

            return $user->canAccessPanel(Filament::getCurrentOrDefaultPanel());
        }, $remember)) {
            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
