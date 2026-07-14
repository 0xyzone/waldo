<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangeInitialPassword extends Page
{
    protected string $view = 'filament.pages.change-initial-password';

    /** Hide this page from the navigation sidebar. */
    protected static bool $shouldRegisterNavigation = false;

    public string $password = '';

    public string $password_confirmation = '';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('password')
                    ->label('New Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->confirmed()
                    ->helperText('Minimum 8 characters'),
                TextInput::make('password_confirmation')
                    ->label('Confirm New Password')
                    ->password()
                    ->revealable()
                    ->required(),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Set New Password')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ]);

        Notification::make()
            ->title('Hooray! Password Updated! 🎉')
            ->body('The password-robot has been banished. Welcome to '.config('app.name').'!')
            ->success()
            ->send();

        $this->redirect(filament()->getUrl());
    }
}
