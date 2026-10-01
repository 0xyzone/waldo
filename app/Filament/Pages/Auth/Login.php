<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
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
}
