<?php

namespace App\Filament\Resources\DiscordSettingResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DiscordSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bot_token')
                    ->label('Bot Token')
                    ->formatStateUsing(fn ($state) => $state ? '••••••••••••••••' : 'Not Set')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                TextColumn::make('guild_id')
                    ->label('Guild (Server) ID')
                    ->placeholder('Not Set')
                    ->fontFamily('mono'),
                TextColumn::make('target_channel_id')
                    ->label('Target Channel ID')
                    ->placeholder('Not Set')
                    ->fontFamily('mono'),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
