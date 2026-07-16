<?php

namespace App\Filament\Resources\DiscordSettingResource\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DiscordSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bot Credentials')
                    ->description('Set up your Discord Bot Token and Server (Guild) ID.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Bot Name / Label')
                            ->placeholder('e.g. Main Server Bot')
                            ->helperText('A friendly name to identify this bot configuration.')
                            ->required(),
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                TextInput::make('bot_token')
                                    ->label('Bot Token')
                                    ->password()
                                    ->revealable()
                                    ->required(),
                                TextInput::make('guild_id')
                                    ->label('Server (Guild) ID')
                                    ->required()
                                    ->reactive(),
                            ]),
                    ]),
                Section::make('Discord Server Dashboard')
                    ->schema([
                        Placeholder::make('server_details')
                            ->label('')
                            ->content(fn () => view('filament.pages.discord-info')),
                    ]),
            ]);
    }
}
