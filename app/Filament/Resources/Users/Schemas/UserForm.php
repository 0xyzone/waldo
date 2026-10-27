<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar_url')
                    ->label('Avatar')
                    ->avatar()
                    ->image()
                    ->disk('public')
                    ->directory('avatars')
                    ->maxSize(2048)
                    ->imageEditor()
                    ->circleCropper()
                    ->alignCenter()
                    ->hiddenOn('create'),
                Toggle::make('is_active')
                    ->label('Active Account')
                    ->default(true)
                    ->disabled(fn (?User $record): bool => $record?->id === auth()->id() || ($record !== null && ! (bool) $record->is_active))
                    ->helperText(fn (?User $record): string => ($record !== null && ! (bool) $record->is_active)
                        ? 'Inactive accounts cannot be reactivated from this form. Use the Reactivate button with OTP verification on the Users list page.'
                        : 'Deactivating will revoke access to the Kamkaj panel.'
                    )
                    ->hiddenOn('create'),
                Toggle::make('read_receipts_enabled')
                    ->label('Read Receipts (Blue Ticks)')
                    ->default(true)
                    ->hiddenOn(['create', 'edit']),
                Toggle::make('online_status_enabled')
                    ->label('Show Online Status & Last Active')
                    ->default(true)
                    ->hiddenOn(['create', 'edit']),
                TextInput::make('name')
                    ->required(),
                TextInput::make('username')
                    ->label('Username')
                    ->maxLength(50)
                    ->regex('/^[a-zA-Z0-9._-]+$/')
                    ->validationMessages([
                        'regex' => 'The username may only contain letters, numbers, dashes, underscores, and periods.',
                    ])
                    ->unique(ignoreRecord: true)
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? trim($state) : null)
                    ->nullable(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label('Phone number')
                    ->tel()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? trim($state) : null)
                    ->nullable(),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->required()
                    ->searchable()
                    ->hiddenOn('create'),
            ]);
    }
}
