<?php

namespace App\Filament\Resources\TipsYearlyEomGomReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TipsYearlyEomGomReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('year')
                    ->label('Year')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->weight('bold'),

                TextColumn::make('title')
                    ->label('Report Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'info',
                        'archived' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst((string) $state))
                    ->sortable(),

                TextColumn::make('entries_count')
                    ->counts('entries')
                    ->label('Total Entries')
                    ->formatStateUsing(fn ($state) => "{$state} Entries (12 Mos)"),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->placeholder('System')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('year', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
