<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getUsernameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPhoneFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label('Username')
            ->maxLength(50)
            ->regex('/^[a-zA-Z0-9._-]+$/')
            ->validationMessages([
                'regex' => 'The username may only contain letters, numbers, dashes, underscores, and periods.',
            ])
            ->unique(ignoreRecord: true)
            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? trim($state) : null)
            ->nullable();
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label('Phone Number')
            ->tel()
            ->maxLength(20)
            ->unique(ignoreRecord: true)
            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? trim($state) : null)
            ->nullable();
    }
}
