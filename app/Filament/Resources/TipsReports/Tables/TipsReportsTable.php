<?php

namespace App\Filament\Resources\TipsReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TipsReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('title')
                    ->label('Report Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('month')
                    ->label('Period')
                    ->formatStateUsing(fn ($record) => ucfirst((string) $record->month).' '.$record->year)
                    ->sortable(),

                TextColumn::make('cutoff_date')
                    ->label('Cutoff Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'generated' => 'success',
                        'locked', 'published' => 'info',
                        default => 'warning',
                    }),

                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Employees')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
