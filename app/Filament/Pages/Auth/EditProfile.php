<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getAvatarFormComponent(),
                $this->getNameFormComponent(),
                $this->getUsernameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPhoneFormComponent(),
                $this->getReadReceiptsFormComponent(),
                $this->getOnlineStatusFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }

    protected function getAvatarFormComponent(): Component
    {
        return FileUpload::make('avatar_url')
            ->label('Profile Avatar')
            ->avatar()
            ->image()
            ->disk('public')
            ->directory('avatars')
            ->maxSize(2048)
            ->imageEditor()
            ->circleCropper()
            ->alignCenter();
    }

    protected function getReadReceiptsFormComponent(): Component
    {
        return Toggle::make('read_receipts_enabled')
            ->label('Read Receipts (Blue Ticks)')
            ->helperText("If turned off, you won't send read receipts, and you won't be able to see other users' seen status.")
            ->default(true);
    }

    protected function getOnlineStatusFormComponent(): Component
    {
        return Toggle::make('online_status_enabled')
            ->label('Show Online Status & Last Active')
            ->helperText("If turned off, others won't be able to see when you are online or your last active time, and you won't see theirs.")
            ->default(true);
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
